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
use Webkul\Walletsystem\Model\Wallettransaction;

/**
 * TransferAmountToBankAccount resolver, used for GraphQL request processing.
 */
class TransferAmountToBankAccount implements ResolverInterface
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
     * @var \Webkul\Walletsystem\Model\WalletNotification
     */
    private $walletNotification;
    
    /**
     * @var \Webkul\Walletsystem\Model\WalletUpdateData
     */
    private $walletUpdate;

    /**
     * Construct function
     *
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Webkul\Walletsystem\Model\WalletNotification $walletNotification
     * @param \Webkul\Walletsystem\Model\WalletUpdateData $walletUpdate
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Webkul\Walletsystem\Model\WalletNotification $walletNotification,
        \Webkul\Walletsystem\Model\WalletUpdateData $walletUpdate
    ) {
        $this->walletHelper = $walletHelper;
        $this->walletNotification = $walletNotification;
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
            $params = $args;
            $output = $this->validateParams($params);
            $responseMessage['message'] = $output;
           
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
     * @param arary $params
     * @return string
     */
    protected function validateParams($params)
    {
        $message = '';
        if (!empty($params) && is_array($params)) {
            if (array_key_exists('customerId', $params) && $params['customerId']!='') {
                if (array_key_exists('amount', $params) &&
                $params['amount']!='' &&
                array_key_exists('bankDetailsId', $params) &&
                $params['bankDetailsId']!=''
                && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['walletnote'])
                && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['bankDetailsId'])
                ) {
                    $params['walletnote'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $params['walletnote']);
                    $params['bank_details'] = preg_replace(
                        '#<script(.*?)>(.*?)</script>#is',
                        '',
                        $params['bankDetailsId']
                    );
                    $baseCurrencyCode = $this->walletHelper->getBaseCurrencyCode();
                    $currencycode = $this->walletHelper->getCurrentCurrencyCode();
                    $amount = $params['amount'];
                    $baseAmount = $this->walletHelper->getwkconvertCurrency(
                        $currencycode,
                        $baseCurrencyCode,
                        $amount
                    );
                    $customerId = $params['customerId'];
                    $params['curr_code'] = $currencycode;
                    $params['curr_amount'] = $params['amount'];
                    $params['order_id'] = 0;
                    $params['status'] = Wallettransaction::WALLET_TRANS_STATE_PENDING;
                    $params['increment_id'] = '';
                    $params['walletamount'] = $baseAmount;
                    $params['walletactiontype'] = 'debit';
                    $params['sender_id'] = 0;
                    $params['sender_type'] = Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE;
                    $params['transfer_to_bank'] = 1;
                    if ($params['walletnote']=='') {
                        $wallent_note = 'Amount'.' '.$params['amount']. 'is transferred by customer to bank account';
                        $params['walletnote'] = $wallent_note;
                    }
                    $customerId = $params['customerId'];
                    $result = $this->walletUpdate->debitAmount($customerId, $params);
                    if (is_array($result) && array_key_exists('success', $result)) {
                        $this->setNotificationMessageForAdmin();
                        $message = 'Amount transfer request has been sent!';
                    }
                } else {
                    $message = 'Something went wrong, please try again123';
                }
            } else {
                $message = 'Something went wrong, please try again321';
            }
        } else {
            $message = 'Something went wrong, please try again896';
        }

        return  $message;
    }

    /**
     * Send notification message to admin
     */
    public function setNotificationMessageForAdmin()
    {
        $notificationModel = $this->walletNotification->getCollection();
        if (!$notificationModel->getSize()) {
            $this->walletNotification->setBanktransferCounter(1);
            $this->walletNotification->save();
        } else {
            foreach ($notificationModel->getItems() as $notification) {
                $notification->setBanktransferCounter($notification->getBanktransferCounter()+1);
            }
        }
        $notificationModel->save();
    }
}
