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

class Transfer extends AbstractWallet implements ResolverInterface
{
    protected $currency;

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
                $Iconheight = $IconWidth = 144 * $this->mFactor;
                $newUrl     = "";
                $basePath   = $this->_moduleReader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, "Webkul_Walletsystem");
                $basePath  .= "/frontend/web/images/wallet.png";
                if (is_file($basePath)) {
                    $newPath = $this->_baseDir . "/" . "mobikulresized" . "/" . $IconWidth . "x" . $Iconheight . "/" . "wallet.png";
                    $this->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                    $newUrl = $this->_helper->getUrl("media") . "mobikulresized" . "/" . $IconWidth . "x" . $Iconheight . "/" . "wallet.png";
                }
                $this->returnArray["logo"] = $newUrl ?? "";
                $this->returnArray["walletSummaryHeading"] = __("Wallet Details");
                $this->returnArray["currencyCode"] = $this->_walletHelper->getCurrentCurrencyCode() ?? "";
                $remainingAmount = 0;
                $walletRecordCollection = $this->_walletrecordModel->create()
                ->addFieldToFilter("customer_id", $this->customerId);
                if (count($walletRecordCollection)) {
                    foreach ($walletRecordCollection as $record) {
                        $remainingAmount = $record->getRemainingAmount();
                    }
                }
                $this->returnArray["walletAmount"] = $this->stripTags(
                    $this->_priceFormat->currency($remainingAmount)
                ) ?? "";
                $this->returnArray["walletSummarySubHeading"] = __("Your wallet Balance");

                $walletPayeeCollection = $this->walletPayee->create()
                    ->getCollection()
                    ->addFieldToFilter('customer_id', $this->customerId);
                $payeeList = [];
                if ($walletPayeeCollection->getSize()) {
                    foreach ($walletPayeeCollection as $model) {
                        $eachModel                  = [];
                        $customerModel = $this->_customerFactory->create()->load($model->getData("payee_customer_id"));
                        $eachModel["id"]              = $model->getId();
                        $eachModel["customerId"] = $customerModel->getId();
                        $eachModel["name"]            = $model->getNickName();
                        $eachModel["email"]           = $customerModel->getEmail();
                        $eachModel["status"]          = $model->getStatus() ? __('Applied') : __('Pending');
                        $payeeList[] = $eachModel;
                    }
                }
                $this->returnArray["payeeList"] = $payeeList;
                $this->returnArray["success"] = true;
            } else {
                $this->returnArray["message"] = __("Payment method is not enabled.");
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
            $this->mFactor          = $this->wholeData["mFactor"] ?? 1;
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
