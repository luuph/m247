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

class Sendamount extends AbstractWallet implements ResolverInterface
{
    protected $code;
    protected $amount;
    protected $baseAmount;
    protected $receiverId;

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
            $this->returnArray["transferValidation"] = (bool)$this->_walletHelper->getTransferValidationEnable();
            $walletNote     = $this->_walletHelper->validateScriptTag($this->wholeData["walletNote"]);
            $toCurrency     = $this->_walletHelper->getBaseCurrencyCode();
            $fromCurrency   = $this->_walletHelper->getCurrentCurrencyCode();
            $transferAmount = $this->_walletHelper->getwkconvertCurrency($fromCurrency, $toCurrency, $this->amount);
            if (!$this->_walletHelper->getTransferValidationEnable()) {
                $totalAmount = $this->_walletHelper->getWalletTotalAmount($this->customerId);
                if ($transferAmount <= $totalAmount && $this->customerId && $this->amount && $this->receiverId && $this->baseAmount) {
                    $wholeData["curr_code"]   = $this->_walletHelper->getCurrentCurrencyCode();
                    $wholeData["base_amount"] = $transferAmount;
                    $wholeData["curr_amount"] = $wholeData["amount"];
                    $this->SendAmountToWallet($wholeData);
                    $this->DeductAmountFromWallet($wholeData);
                    $this->returnArray["message"] = __("Amount Transfered successfully");
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Something went wrong!")
                    );
                }
            } else {
                $this->_waletTransfer->checkAndUpdateSession();
                $walletTransferData = $this->_waletTransfer->getWalletTransferDataToSession();
                if ($walletTransferData == '') {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Either code session is expired, or amount is already transferred.")
                    );
                }
                $walletCookieArray = $this->_walletHelper->convertStringAccToVersion($walletTransferData, 'decode');
                if (
                    $walletCookieArray['sender_id'] == $this->customerId &&
                    $walletCookieArray['amount'] == $this->amount &&
                    $walletCookieArray['reciever_id'] == $this->receiverId
                ) {
                    if (!$this->_encryptor->validateHash($this->code, $walletCookieArray['code'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("Incorrect code.")
                        );
                    }
                    $wholeData["base_amount"] = $transferAmount;
                    $wholeData["curr_code"]   = $this->_walletHelper->getCurrentCurrencyCode();
                    $wholeData["curr_amount"] = $wholeData["amount"];
                    $wholeData['walletnote'] = $walletNote;
                    $this->SendAmountToWallet($wholeData);
                    $this->DeductAmountFromWallet($wholeData);
                    $this->_waletTransfer->setWalletTransferDataToSession('');
                    $this->messageManager->addSuccess(__("Amount transferred successfully"));
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Something went wrong!")
                    );
                }
            }
            $this->returnArray["success"] = true;
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->returnArray;
        }
    }

    public function SendAmountToWallet($params)
    {
        $customerModel = $this->_walletHelper->getCustomerByCustomerId($params["senderId"]);
        $senderName    = $customerModel->getName();
        if ($params["walletNote"] == "") {
            $params["walletNote"] = __("Transfer by %1", $senderName);
        }
        $transferAmountData = [
            "curr_code"        => $params["curr_code"],
            "sender_id"        => $params["senderId"],
            "customerid"       => $params["receiverId"],
            "walletnote"       => __($params["walletNote"]),
            "sender_type"      => 4,
            "curr_amount"      => $params["curr_amount"],
            "walletamount"     => $params["base_amount"],
            "walletactiontype" => "credit",
            "status"           => 1,
            'order_id'         => 0,
            'increment_id'     => ''
        ];
        $msg = __(
            "%1 amount %2ed by %3.  He added a note for the transaction: %4",
            $this->_walletHelper->getformattedPrice($transferAmountData["walletamount"]),
            $transferAmountData["walletactiontype"],
            $senderName,
            __($params["walletNote"])
        );
        $adminMsg = __(
            "'s account is updated with the %1 amount %2ed by %3. He added a note for the transaction: %4",
            $this->_walletHelper->getformattedPrice($transferAmountData["walletamount"]),
            $transferAmountData["walletactiontype"],
            $senderName,
            __($params["walletNote"])
        );
        $this->_walletUpdate->creditAmount($params["receiverId"], $transferAmountData, $msg, $adminMsg);
    }

    public function DeductAmountFromWallet($params)
    {
        $customerModel = $this->_walletHelper->getCustomerByCustomerId($params["receiverId"]);
        $recieverName  = $customerModel->getName();
        if ($params["walletNote"] == "") {
            $params["walletNote"] = __("Transfer to %1", $recieverName);
        }
        $transferAmountData = [
            "curr_code"        => $params["curr_code"],
            "sender_id"        => $params["receiverId"],
            "customerid"       => $params["senderId"],
            "walletnote"       => __($params["walletNote"]),
            "curr_amount"      => $params["curr_amount"],
            "sender_type"      => 4,
            "walletamount"     => $params["base_amount"],
            "walletactiontype" => "debit",
            'order_id'         => 0,
            'status'           => 1,
            'increment_id'     => ''
        ];
        $msg = __(
            'You have transfered %1 amount to %2. you added a note on the transaction: %3',
            $this->_walletHelper->getformattedPrice($transferAmountData["walletamount"]),
            $recieverName,
            __($params["walletNote"])
        );
        $adminMsg = __(
            "'s account is updated with the %1 amount transferd to %2. He added a note for the transaction: %3",
            $this->_walletHelper->getformattedPrice($transferAmountData["walletamount"]),
            $recieverName,
            __($params["walletNote"])
        );
        $this->_walletUpdate->debitAmount($params["senderId"], $transferAmountData, $msg, $adminMsg);
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
            $this->code          = $this->wholeData["code"];
            $this->amount        = $this->wholeData["amount"];
            $this->baseAmount    = $this->wholeData["baseAmount"];
            $this->receiverId    = $this->wholeData["receiverId"];
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
