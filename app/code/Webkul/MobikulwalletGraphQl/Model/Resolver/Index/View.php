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
use Webkul\Walletsystem\Model\Wallettransaction;

class View extends AbstractWallet implements ResolverInterface
{
    protected $transactionId;
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
            $this->returnArray["transactionData"] = [];
            $walletTransactionData = $this->_walletTransaction->create()->load($this->transactionId);
            $amount       = $walletTransactionData->getCurrAmount();
            $currencyCode = $walletTransactionData->getCurrencyCode();
            $precision    = 2;
            if ($this->customerId == $walletTransactionData->getCustomerId()) {
                $transactionData["amount"] = [
                    "label" => __("Amount"),
                    // "value" => $this->stripTags($this->_priceFormat->currency($amount, $includeContainer = true, $precision, $scope = null, $currencyCode))
                    "value" => $this->stripTags($this->_priceCurrency->format(number_format($this->_walletHelper->getwkconvertCurrency($currencyCode,$this->store->getCurrentCurrencyCode(),$amount), 2)))
                ];
                $transactionData["action"] = [
                    "label" => __("Action"),
                    "value" => $walletTransactionData->getAction()
                ];
                $transactionData["type"] = [
                    "label" => __("Type"),
                    "value" => $this->_walletHelper->getTransactionPrefix($walletTransactionData->getSenderType(), $walletTransactionData->getAction())
                ];
                $transactionData["date"] = [
                    "label" => __("Transaction At"),
                    "value" => $this->_localeDate->date(new \DateTime($walletTransactionData->getTransactionAt()))->format("g:ia \o\\n l jS F Y")
                ];
                $transactionData["note"] = [
                    "label" => __("Transaction note"),
                    "value" => $walletTransactionData->getTransactionNote()
                ];
                $transactionLabel = __('Approved');
                if ($walletTransactionData->getStatus() == Wallettransaction::WALLET_TRANS_STATE_PENDING) {
                    $transactionLabel =  __('Pending');
                } else if ($walletTransactionData->getStatus() == Wallettransaction::WALLET_TRANS_STATE_CANCEL) {
                    $transactionLabel =  __('Cancelled');
                }
                $transactionData["status"] = [
                    "label" => __("Transaction Status"),
                    "value" => $transactionLabel
                ];
                $transactionData["bankDetails"] = new \stdClass();

                $transactionData["order"]  = new \stdClass();
                $transactionData["sender"] = new \stdClass();
                $incrementid = "";

                $orderDetailsActions = [
                    Wallettransaction::ORDER_PLACE_TYPE,
                    Wallettransaction::REFUND_TYPE
                ];
                if ($walletTransactionData->getOrderId()) {
                    $order = $this->_order->load($walletTransactionData->getOrderId());
                    $incrementid = $order->getIncrementId();
                }
                if ($walletTransactionData->getSenderType() == 0) {
                    $transactionData["order"] = [
                        "label" => __("Reference"),
                        "value" => $incrementid
                    ];
                } elseif ($walletTransactionData->getSenderType() == 1) {
                    if ($walletTransactionData->getAction() == "credit") {
                        $transactionData["order"] = [
                            "label" => __("Reference"),
                            "value" => $incrementid
                        ];
                    }
                } elseif ($walletTransactionData->getSenderType() == 2) {
                    $transactionData["order"] = [
                        "label" => __("Reference"),
                        "value" => $incrementid
                    ];
                } elseif ($walletTransactionData->getSenderType() == 3) {
                } elseif ($walletTransactionData->getSenderType() == 4) {
                    if ($walletTransactionData->getAction() == "credit") {
                        $senderData = $this->_customerFactory->create()->load($walletTransactionData->getSenderId());
                        $transactionData["sender"] = [
                            "label" => __("Sender"),
                            "value" => $senderData->getName()
                        ];
                    } else {
                        $recieverData = $this->_customerFactory->create()->load($walletTransactionData->getSenderId());
                        $transactionData["sender"] = [
                            "label" => __("Receiver"),
                            "value" => $recieverData->getName()
                        ];
                    }
                } elseif (!in_array($walletTransactionData->getSenderType(), $orderDetailsActions) &&
                    $walletTransactionData->getSenderType() != Wallettransaction::CASH_BACK_TYPE &&
                    $walletTransactionData->getSenderType() != Wallettransaction::CUSTOMER_TRANSFER_TYPE &&
                    $walletTransactionData->getSenderType() == Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE)
                {
                    $accountData = $this->accountDetails->load($walletTransactionData->getBankDetails());
                    if ($accountData->getId()) {
                        $this->_walletHelper->getbankDetails(nl2br($walletTransactionData->getBankDetails()));
                        $transactionData["bankDetails"] = [
                            'label' => __("Bank Details"),
                            'value' => __('A/c Holder Name') . "\n" . $accountData->getHoldername() . "\n\n" .
                                __('Bank Name') . "\n" . $accountData->getBankname() . "\n\n" .
                                __('Bank Code') . "\n" . $accountData->getBankcode() . "\n\n" .
                                __('Additional Information') . "\n" . $accountData->getAdditional()
                        ];
                    }
                }
                $this->returnArray["success"]         = true;
                $this->returnArray["transactionData"] = $transactionData;
            } else {
                $this->returnArray["otherError"] = "unauthorized access.";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("You are not authorized to access this transaction!")
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
            $this->transactionId = $this->wholeData["transactionId"] ?? 0;
            $this->currency      = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
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
        } else {
            throw new \BadMethodCallException("Invalid Request");
        }
    }
}
