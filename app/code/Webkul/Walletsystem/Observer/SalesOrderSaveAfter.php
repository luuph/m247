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

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Magento\Sales\Model\OrderFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;

/**
 * Webkul Walletsystem Class
 */
class SalesOrderSaveAfter implements ObserverInterface
{
    public const WALLET_SYSTEM_CODE = "walletsystem";
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $mailHelper;

    /**
     * @var  Webkul\Walletsystem\Model\WalletcreditamountFactory
     */
    protected $walletcreditAmountFactory;

    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $walletTransactionFactory;

    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $salesOrderFactory;

    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime          $date
     * @param \Webkul\Walletsystem\Helper\Data                     $helper
     * @param \Webkul\Walletsystem\Helper\Mail                     $mailHelper
     * @param \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory
     * @param WallettransactionFactory                             $walletTransaction
     * @param OrderFactory                                         $orderFactory
     * @param WalletUpdateData                                     $walletUpdateData
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        WallettransactionFactory $walletTransaction,
        OrderFactory $orderFactory,
        WalletUpdateData $walletUpdateData
    ) {
        $this->date = $date;
        $this->helper = $helper;
        $this->mailHelper = $mailHelper;
        $this->walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->walletTransactionFactory = $walletTransaction;
        $this->salesOrderFactory = $orderFactory;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * Sales order save after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletTransaction = $this->walletTransactionFactory->create();
        $walletProductId = $this->helper->getWalletProductId();
        $orderId = $observer->getOrder()->getId();
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodCode = $method->getCode();
        $orderArray = $order->toArray();

        if (array_key_exists("wallet_amount", $orderArray) && array_key_exists("base_wallet_amount", $orderArray)) {
            $order->setWalletAmount(-$orderArray['wallet_amount']);
            $order->setBaseWalletAmount(-$orderArray['base_wallet_amount']);
        }
        
        if ($order->getStatus() == 'complete' && $methodCode==self::WALLET_SYSTEM_CODE) {
            $incrementId = $this->salesOrderFactory
                ->create()
                ->load($orderId)
                ->getIncrementId();
            $customerId = $order->getCustomerId();
            $currencyCode = $order->getOrderCurrencyCode();
            $currencyCreditAmount = $this->getCreditAmountData($orderId);
            if ($currencyCreditAmount > 0) {
                $baseCurrencyCode = $this->helper->getBaseCurrencyCode();
                $creditAmount = $this->helper->getwkconvertCurrency(
                    $currencyCode,
                    $baseCurrencyCode,
                    $currencyCreditAmount
                );
                $transferAmountData = [
                    'customerid' => $customerId,
                    'walletamount' => $creditAmount,
                    'walletactiontype' => $walletTransaction::WALLET_ACTION_TYPE_CREDIT,
                    'curr_code' => $currencyCode,
                    'curr_amount' => $currencyCreditAmount,
                    'walletnote' => __('Order id : %1 Cash Back Amount', $incrementId),
                    'sender_id' => 0,
                    'sender_type' => $walletTransaction::CASH_BACK_TYPE,
                    'order_id' => $orderId,
                    'status' => $walletTransaction::WALLET_TRANS_STATE_APPROVE,
                    'increment_id' => $incrementId
                ];
                $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
                $creditedAmountModel = $this->walletcreditAmountFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('order_id', $orderId);
                foreach ($creditedAmountModel as $model) {
                    $model->setStatus(1);
                    $this->saveObject($model);
                }
            }
        }
    }

    /**
     * Get credit amount data
     *
     * @param int $orderId
     * @return float
     */
    public function getCreditAmountData($orderId)
    {
        $creditAmountModelClass = $this->walletcreditAmountFactory->create();
        $amount = 0;
        $creditAmountModel = $this->walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', $creditAmountModelClass::WALLET_CREDIT_AMOUNT_STATUS_DISABLE);

        if ($creditAmountModel->getSize()) {
            foreach ($creditAmountModel as $creditamount) {
                $amount = $creditamount->getAmount();
            }
        }
        return $amount;
    }

    /**
     * Performs save operation on object
     *
     * @param object $object
     */
    public function saveObject($object)
    {
        $object->save();
    }
}
