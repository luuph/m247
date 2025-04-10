<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulwalletGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MobikulwalletGraphQl\Model\Resolver\Index;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Index extends AbstractWallet implements ResolverInterface
{
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        try {
            $this->verifyRequest();
            if ($this->_walletHelper->getWalletenabled()) {
                $maximumAmount = $this->_walletHelper->getMaximumAmount() ?? 0;
                $minimumAmount = $this->_walletHelper->getMinimumAmount() ?? 0;
                if ($minimumAmount > $maximumAmount) {
                    $temp          = $maximumAmount;
                    $maximumAmount = $minimumAmount;
                    $minimumAmount = $temp;
                }
                $this->returnArray["maximumAmount"] = $maximumAmount;
                $this->returnArray["minimumAmount"] = $minimumAmount;
                $Iconheight = $IconWidth = 144 * $this->mFactor;
                $newUrl = "";
                $basePath = $this->_moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, "Webkul_Walletsystem");
                $basePath .= "/frontend/web/images/wallet.png";
                if (is_file($basePath)) {
                    $newPath = $this->_baseDir . "/" . "mobikulresized" . "/" . $IconWidth . "x" . $Iconheight . "/" . "wallet.png";
                    $this->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                    $newUrl = $this->_helper->getUrl("media") . "mobikulresized" . "/" . $IconWidth . "x" . $Iconheight . "/" . "wallet.png";
                }
                $this->returnArray["logo"] = $newUrl ?? "";
                $this->returnArray["walletSummaryHeading"] = __("Wallet Details");
                $this->returnArray["currencyCode"] = $this->_walletHelper->getCurrentCurrencyCode() ?? "";
                $remainingAmount = 0;
                $walletRecordCollection = $this->_walletrecordModel->create()->addFieldToFilter("customer_id", $this->customerId);
                if (count($walletRecordCollection)) {
                    foreach ($walletRecordCollection as $record) {
                        $remainingAmount = $record->getRemainingAmount();
                    }
                }
                $this->returnArray["walletAmount"] = $this->stripTags($this->_priceFormat->currency($remainingAmount)) ?? "";
                $this->returnArray["walletSummarySubHeading"] = __("Your wallet Balance");
                $this->returnArray["rechargeFieldLabel"] = __("Enter Amount to be Added in wallet");
                $this->returnArray["walletProductId"] = $this->_walletHelper->getWalletProductId() ?? 0;
                $this->returnArray["buttonLabel"] = __("Add Money To Wallet");
                $this->returnArray["mainHeading"]  = __("Last Transactions");
                $this->returnArray["subHeading"][] = __("Description");
                $this->returnArray["subHeading"][] = __("Debit");
                $this->returnArray["subHeading"][] = __("Credit");
                $this->returnArray["subHeading"][] = __("Status");
                $walletCollection = $this->_wallettransactionModel->create()
                    ->addFieldToFilter("customer_id", $this->customerId)
                    ->setOrder("transaction_at", "DESC");
                $transactionList = [];
                if (count($walletCollection)) {
                    foreach ($walletCollection as $record) {
                        $eachTransaction = [];
                        $prefix = $this->_walletHelper->getTransactionPrefix($record->getSenderType(), $record->getAction());
                        $eachTransaction["viewId"]      = 0;
                        $eachTransaction["incrementId"] = "";
                        $eachTransaction["viewId"]      = $record->getEntityId();
                        $eachTransaction["description"] = $prefix . " #" . $record->getEntityId();
                        $eachTransaction["debit"]       = "";
                        $eachTransaction["credit"]      = "";
                        if ($record->getAction() == "debit")
                            $eachTransaction["debit"] = $this->stripTags($this->_priceCurrency->format(number_format($this->_walletHelper->getwkconvertCurrency($record->getCurrencyCode(),$this->store->getCurrentCurrencyCode(),$record->getCurrAmount()), 2)));
                        else
                            $eachTransaction["credit"] = $this->stripTags($this->_priceCurrency->format(number_format($this->_walletHelper->getwkconvertCurrency($record->getCurrencyCode(),$this->store->getCurrentCurrencyCode(),$record->getCurrAmount()), 2)));
                        $eachTransaction["status"] = __("Pending");
                        if ($record->getStatus())
                            $eachTransaction["status"] = __("Applied");
                        $transactionList[] = $eachTransaction;
                    }
                } else {
                    $this->returnArray["message"] = __("No records found!");
                }
                $this->returnArray["transactionList"] = $transactionList;
                $this->returnArray["accountDetails"] = [];
                $accountDetails = $this->accountDetails->getCollection()
                    ->addFieldToFilter("customer_id", ["eq" => $this->customerId])
                    ->addFieldToFilter('status', ['neq' => 0]);
                if ($accountDetails->getSize()) {
                    foreach ($accountDetails as $model) {
                        $eachDetails = [];
                        if ($model->getBankname() != "") {
                            $eachDetails["id"]                  = $model->getEntityId();
                            $eachDetails["accountNumber"]       = $model->getAccountno();
                            $eachDetails["bankName"]            = $model->getBankname();
                            $eachDetails['accountHolderName'] = $model->getHoldername();
                            $this->returnArray["accountDetails"][]    = $eachDetails;
                        }
                    }
                } else {
                    $this->returnArray["messageForAccountDetails"] = __("No Record Found!");
                }

                $this->returnArray["success"] = true;
                $this->returnArray["idd"] = $this->customerId;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Payment method is not enabled.")
                );
            }
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->returnArray;
        }
    }

    /**
     * Function verify Request to authenticate the request
     *
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->wholeData) {
            $this->mFactor            = $this->wholeData["mFactor"] ?? 0;
            $this->currency        = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $this->currency      = $this->getRequest()->getHeader(self::CURRENT_CURRENCY) ?? $this->currency;
            $this->customerToken = $this->wholeData["customerToken"] ?? '';
            $this->customerId    = $this->_helper->getCustomerByToken($this->customerToken) ?? 0;
            if ($this->customerId == 0) {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist, so you need to logout.")
                );
            } elseif ($this->customerId != 0) {
                $this->customer = $this->_customerFactory->create()->load($this->customerId);
                $this->_customerSession->setCustomerId($this->customerId);
            }
            $this->store->setCurrentCurrencyCode($this->currency);
        } else {
            throw new \BadMethodCallException("Invalid Request");
        }
    }
}
