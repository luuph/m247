<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\Walletsystem\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Webkul\Walletsystem\Helper\Mail as WalletMail;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Webkul\Walletsystem\Model\Wallettransaction;
use Webkul\Walletsystem\Model\WalletUpdateData;

/**
 * SendTransferAmount resolver, used for GraphQL request processing.
 */
class SendTransferAmount implements ResolverInterface
{
    /**
     * @var transactioncollection
     */
    private $transactioncollection;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $walletHelper;

    /**
     * @var \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory
     */
    private $walletrecordModel;

    /**
     * @var WalletMail
     */
    private $walletMail;

    /**
     * @var \Webkul\Walletsystem\Model\WalletTransferData
     */
    private $walletTransfer;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var WalletUpdateData
     */
    private $walletUpdate;

    /**
     * Construct function
     *
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param WalletMail $walletMail
     * @param \Webkul\Walletsystem\Model\WalletTransferData $walletTransfer
     * @param Encryptor $encryptor
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param WalletUpdateData $walletUpdate
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        WalletMail $walletMail,
        \Webkul\Walletsystem\Model\WalletTransferData $walletTransfer,
        Encryptor $encryptor,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        WalletUpdateData $walletUpdate
    ) {
        $this->walletHelper = $walletHelper;
        $this->walletMail = $walletMail;
        $this->walletTransfer = $walletTransfer;
        $this->encryptor = $encryptor;
        $this->date = $date;
        $this->walletUpdate = $walletUpdate;
    }

    /**
     * Resolver method for GraphQL
     *
     * @param Field $field
     * @param object $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $responseMessage=[];
            $result ='';
            $message = '';
            $params = $args;
            
            $error = $this->validateParams($params);
            $walletHelper = $this->walletHelper;
            if ($error) {
                $message = "Incorrect code.";
            }

            if (!$walletHelper->getTransferValidationEnable()) {
                $params['curr_code'] = $walletHelper->getCurrentCurrencyCode();
                $fromCurrency = $walletHelper->getCurrentCurrencyCode();
                $toCurrency = $walletHelper->getBaseCurrencyCode();
                $amount = $params['amount'];
                $transferAmount = $walletHelper->getwkconvertCurrency($fromCurrency, $toCurrency, $amount);
                $totalAmount = $walletHelper->getWalletTotalAmount(0);
                if ($transferAmount <= $totalAmount) {
                    $params['baseAmount'] = $transferAmount;
                    $params['curr_amount'] = $params['amount'];
                    $this->sendAmountToWallet($params);
                    $this->deductAmountFromWallet($params);
                    $message = "Amount transferred successfully";
                } else {
                    $message = "You don't have enough amount in your wallet.";
                }
                
            } else {
                $this->walletTransfer->checkAndUpdateSession();
                $walletTransferData = $this->walletTransfer->getWalletTransferDataToSession();
                if ($walletTransferData=='') {
                    $message = "Either code session is expired, or amount is already transferred";
                }
                $walletCookieArray = $walletHelper->convertStringAccToVersion($walletTransferData, 'decode');
                if (!$this->encryptor->validateHash($params['code'], $walletCookieArray['code'])) {
                    $message = "Incorrect code";
                }
                    $params['curr_code'] = $this->walletHelper->getCurrentCurrencyCode();
                    $params['curr_amount'] = $params['amount'];
                    $params['walletnote'] = $walletCookieArray['walletnote'];
                    $this->sendAmountToWallet($params);
                    $this->deductAmountFromWallet($params);
                    $this->walletTransfer->setWalletTransferDataToSession('');
                    $message ="Amount transferred successfully";
            }

            $responseMessage['message'] =$message;

        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
        return $responseMessage;
    }

    /**
     * Validate params
     *
     * @param array $params
     * @return bool
     */
    public function validateParams($params)
    {
        $error = 0;
        foreach ($params as $paramkey => $paramvalue) {
            switch ($paramkey) {
                case 'senderId':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
                case 'receiverId':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
                case 'code':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
                case 'amount':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
            }
        }
        return $error;
    }

     /**
      * Send amount to customer's wallet
      *
      * @param array $params
      */
    public function sendAmountToWallet($params)
    {
        $customerModel = $this->walletHelper->getCustomerByCustomerId($params['senderId']);
        $senderName = $customerModel->getFirstname()." ".$customerModel->getLastname();
       
        if (!isset($params['walletnote'])) {
           
            $params['walletnote'] = __("Transfer by %1", $senderName);
        }
        $transferAmountData = [
            'customerid' => $params['receiverId'],
            'walletamount' => $params['baseAmount'],
            'walletactiontype' => Wallettransaction::WALLET_ACTION_TYPE_CREDIT,
            'curr_code' => $params['curr_code'],
            'curr_amount' => $params['curr_amount'],
            'walletnote' => __($params['walletnote']),
            'sender_id' => $params['senderId'],
            'sender_type' => Wallettransaction::CUSTOMER_TRANSFER_TYPE,
            'order_id' => 0,
            'status' => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
            'increment_id' => ''
        ];
        $this->walletUpdate->creditAmount($params['receiverId'], $transferAmountData);
    }
   
     /**
      * Deduct amount from sender's wallet
      *
      * @param array $params
      */
    public function deductAmountFromWallet($params)
    {
        $customerModel = $this->walletHelper->getCustomerByCustomerId($params['receiverId']);
        $recieverName = $customerModel->getFirstname()." ".$customerModel->getLastname();
        if (!isset($params['walletnote'])) {
            $params['walletnote'] = __("Transfer to %1", $recieverName);
        }
        $transferAmountData = [
            'customerid' => $params['senderId'],
            'walletamount' => $params['baseAmount'],
            'walletactiontype' => Wallettransaction::WALLET_ACTION_TYPE_DEBIT,
            'curr_code' => $params['curr_code'],
            'curr_amount' => $params['curr_amount'],
            'walletnote' => __($params['walletnote']),
            'sender_id' => $params['receiverId'],
            'sender_type' => Wallettransaction::CUSTOMER_TRANSFER_TYPE,
            'order_id' => 0,
            'status' => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
            'increment_id' => ''
        ];
        $this->walletUpdate->debitAmount($params['senderId'], $transferAmountData);
    }
}
