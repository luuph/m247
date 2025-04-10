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

class Bankaccount extends AbstractWallet implements ResolverInterface
{
    protected $amount;
    protected $bankDetails;
    protected $walletNote;

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
            if (
                $this->amount && $this->bankDetails != ''
                && !preg_match('#<script(.*?)>(.*?)</script>#is', $this->walletNote)
                && !preg_match('#<script(.*?)>(.*?)</script>#is', $this->bankDetails)
            ) {
                $walletNote = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $this->walletNote);
                $bankDetails = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $this->bankDetails);
                $baseCurrencyCode = $this->_walletHelper->getBaseCurrencyCode();
                $currencycode = $this->_walletHelper->getCurrentCurrencyCode();
                $baseAmount = $this->_walletHelper->getwkconvertCurrency(
                    $currencycode,
                    $baseCurrencyCode,
                    $this->amount
                );

                $params['customer_id'] = $this->customerId;
                $params['amount'] = $this->amount;
                $params['bank_details'] = $bankDetails;
                $params['walletnote'] = $walletNote;
                $params['curr_code'] = $currencycode;
                $params['curr_amount'] = $this->amount;
                $params['order_id'] = 0;
                $params['status'] = Wallettransaction::WALLET_TRANS_STATE_PENDING;
                $params['increment_id'] = '';
                $params['walletamount'] = $baseAmount;
                $params['walletactiontype'] = 'debit';
                $params['sender_id'] = 0;
                $params['sender_type'] = Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE;
                $params['transfer_to_bank'] = 1;
                if ($walletNote == '') {
                    $walletNote = __('%1, Amount is transferred by customer to bank account', $this->amount);
                }

                $result = $this->_walletUpdate->debitAmount($this->customerId, $params);

                if (is_array($result) && array_key_exists('success', $result)) {
                    $this->setNotificationMessageForAdmin();
                    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    if ($this->scopeConfig->getValue('walletsystem/message_after_request/show_message', $storeScope)) {
                        $message = $this->scopeConfig->getValue('walletsystem/message_after_request/message_content', $storeScope);
                        $this->returnArray["message"] = $message;
                    } else {
                        $this->returnArray["message"] = __("Amount transfer request has been sent!");
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Respective amount is not available your wallet")
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Something went wrong, please try again")
                );
            }
            $this->returnArray["success"] = true;
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->returnArray;
        }
    }

    public function setNotificationMessageForAdmin()
    {
        $notificationModel = $this->_walletNotification->getCollection();
        if (!$notificationModel->getSize()) {
            $this->_walletNotification->setBanktransferCounter(1);
            $this->_walletNotification->save();
        } else {
            foreach ($notificationModel->getItems() as $notification) {
                $notification->setBanktransferCounter($notification->getBanktransferCounter() + 1);
            }
        }
        $notificationModel->save();
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
            $this->amount        = $this->wholeData["amount"] ?? 0;
            $this->bankDetails   = $this->wholeData["bankDetails"] ?? "";
            $this->walletNote   = $this->wholeData["walletNote"] ?? "";
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
