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
use Magento\Sales\Model\OrderFactory;
use \Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;
use \Webkul\Walletsystem\Model\Wallettransaction;

/**
 * Webkul Walletsystem Class
 */
class SalesOrderCancelAfter implements ObserverInterface
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $mailHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $walletTransaction;
    
    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory;
     */
    protected $walletrecord;
    
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;
    
    /**
     * Constructor
     *
     * @param OrderFactory $orderFactory
     * @param \Webkul\Walletsystem\Helper\Mail $mailHelper
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param WallettransactionFactory $wallettransaction
     * @param WalletrecordFactory $walletRecord
     * @param WalletUpdateData $walletUpdateData
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        WallettransactionFactory $wallettransaction,
        WalletrecordFactory $walletRecord,
        WalletUpdateData $walletUpdateData
    ) {
        $this->orderFactory = $orderFactory;
        $this->mailHelper = $mailHelper;
        $this->helper = $helper;
        $this->date = $date;
        $this->walletTransaction = $wallettransaction;
        $this->walletrecord = $walletRecord;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * Credit memo save after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();

        if ($order->getPayment()->getMethod() != 'walletsystem') {
            return true;
        }
        $orderId = $order->getEntityId();
        $incrementId = $order->getIncrementId();
        $walletAmount = 0;
        foreach ($order->getInvoiceCollection() as $previousInvoice) {
            if ((double) $previousInvoice->getWalletAmount() && !$previousInvoice->isCanceled()) {
                $walletAmount = $walletAmount + $previousInvoice->getWalletAmount();
            }
        }
        if ($order->getWalletAmount()!=$walletAmount) {
            $walletAmount = $order->getWalletAmount() - $walletAmount;
        } else {
            $walletAmount = 0;
        }

        $totalCanceledAmount = (-1 * $walletAmount);
        $baseTotalCanceledAmount = $this->helper->baseCurrencyAmount($totalCanceledAmount);
        $currencyCode = $order->getOrderCurrencyCode();
        $rowId = 0;
        $totalAmount = 0;
        $remainingAmount = 0;
        $orderItem = $order->getAllItems();
        $productIdArray = [];

        foreach ($orderItem as $value) {
            $productIdArray[] = $value->getProductId();
        }
        $walletProductId = $this->helper->getWalletProductId();

        if (!in_array($walletProductId, $productIdArray) && $walletAmount != 0) {
            $customerId = $order->getCustomerId();
            $transferAmountData = [
                'customerid' => $customerId,
                'walletamount' => $baseTotalCanceledAmount,
                'walletactiontype' => Wallettransaction::WALLET_ACTION_TYPE_CREDIT,
                'curr_code' => $currencyCode,
                'curr_amount' => $totalCanceledAmount,
                'walletnote' => __('Order id : %1 credited amount', $incrementId),
                'sender_id' => 0,
                'sender_type' => Wallettransaction::REFUND_TYPE,
                'order_id' => $orderId,
                'status' => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
                'increment_id' => $incrementId
            ];
            $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
        }
    }
}
