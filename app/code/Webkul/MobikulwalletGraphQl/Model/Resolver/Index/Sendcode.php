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

class Sendcode extends AbstractWallet implements ResolverInterface
{
    protected $code;
    protected $amount;
    protected $base_amount;
    protected $walletNote;
    protected $receiverId;

    protected const SYMBOLS_COLLECTION = "0123456789";
    protected const DEFAULT_LENGTH = 6;

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
            $this->returnArray["base_amount"] = 0;
            $this->returnArray["transferValidation"] = (bool)$this->_walletHelper->getTransferValidationEnable();
            $walletNote     = $this->_walletHelper->validateScriptTag($this->walletNote);
            $this->wholeData['senderId'] = $this->customerId;
            if (!is_numeric($this->amount)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Invalid Amount.")
                );
            }
            $toCurrency     = $this->_walletHelper->getBaseCurrencyCode();
            $fromCurrency   = $this->_walletHelper->getCurrentCurrencyCode();
            $transferAmount = $this->_walletHelper->getwkconvertCurrency($fromCurrency, $toCurrency, $this->amount);
            if (!$this->_walletHelper->getTransferValidationEnable()) {
                $this->wholeData["curr_code"] = $fromCurrency;
                $totalAmount            = $this->_walletHelper->getWalletTotalAmount($this->customerId);
                if ($transferAmount <= $totalAmount) {
                    $this->wholeData["base_amount"] = $transferAmount;
                    $this->wholeData["curr_amount"] = $this->amount;
                    $this->SendAmountToWallet($this->wholeData);
                    $this->DeductAmountFromWallet($this->wholeData);
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("You don't have enough amount in your wallet.")
                    );
                }
            }
            $duration = $this->_walletHelper->getCodeValidationDuration();
            if ($this->amount != 0 && $this->receiverId != 0 && $this->receiverId != "") {
                $totalAmount = $this->_walletHelper->getWalletTotalAmount($this->customerId);
                if ($transferAmount <= $totalAmount) {
                    $this->wholeData["base_amount"] = $transferAmount;
                    $data        = $this->sendEmailForCode($this->wholeData);
                    $sessionData = [
                        "code"        => $this->createCodeHash($data["code"]),
                        "amount"      => $this->wholeData["amount"],
                        "sender_id"   => $data["customer_id"],
                        "walletNote"  => $this->walletNote,
                        "created_at"  => strtotime($this->_date->gmtDate()),
                        "reciever_id" => $this->wholeData["receiverId"],
                        "base_amount" => $transferAmount
                    ];
                    $serializedData = $this->_walletHelper->convertStringAccToVersion($sessionData, 'encode');
                    $this->_waletTransfer->setWalletTransferDataToSession($serializedData);
                    unset($sessionData["code"]);
                    $getParamData = urlencode(base64_encode(json_encode($sessionData)));
                    $paramsJson   = base64_decode(urldecode($getParamData));
                    if ($paramsJson)
                        $params   = json_decode($paramsJson, true);
                    if (is_array($params) && count($params) && array_key_exists("sender_id", $params)) {
                        if (!$this->_walletHelper->getTransferValidationEnable()) {
                            $this->returnArray["message"] = __("Amount has been transfered successfully.");
                        } else {
                            $this->returnArray["message"] = __("Code has been sent to your email id.");
                        }
                    } else {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("Please try again later.")
                        );
                    }
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("You don't have enough amount in your wallet.")
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Try again with valid data.")
                );
            }
            $this->returnArray["success"]     = true;
            $this->returnArray["base_amount"] = $this->wholeData["base_amount"];
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->returnArray;
        }
    }

    public function sendEmailForCode($params)
    {
        $data = [
            "code"        => $this->generateCode(),
            "amount"      => $params["amount"],
            "duration"    => $this->_walletHelper->getCodeValidationDuration(),
            "customer_id" => $params["senderId"],
            "base_amount" => $params["base_amount"]
        ];
        $this->_walletMail->sendTransferCode($data);
        return $data;
    }

    public function generateCode()
    {
        $alphabet = self::SYMBOLS_COLLECTION;
        $length   = self::DEFAULT_LENGTH;
        $code     = "";
        for ($i = 0, $indexMax = strlen($alphabet) - 1; $i < $length; ++$i)
            $code .= substr($alphabet, mt_rand(0, $indexMax), 1);
        return $code;
    }

    protected function createCodeHash($code)
    {
        return $this->_encryptor->getHash($code, true);
    }

    public function updateSession()
    {
        $this->_waletTransfer->checkAndUpdateSession();
        $walletTransferData = $this->_waletTransfer->getWalletTransferDataToSession();
        if ($walletTransferData == "")
            return false;
        return true;
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
            "walletactiontype" => Wallettransaction::WALLET_ACTION_TYPE_CREDIT,
            'order_id'         => 0,
            'status'           => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
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
            "sender_type"      => Wallettransaction::CUSTOMER_TRANSFER_TYPE,
            "walletamount"     => $params["base_amount"],
            "walletactiontype" => Wallettransaction::WALLET_ACTION_TYPE_DEBIT,
            'order_id' => 0,
            'status' => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
            'increment_id' => ''
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
            $this->amount        = $this->wholeData["amount"];
            $this->receiverId    = $this->wholeData["receiverId"];
            $this->base_amount   = $this->wholeData["base_amount"];
            $this->walletNote    = $this->wholeData["walletNote"];
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
