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
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\OrderFactory;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Webkul\Walletsystem\Model\WalletUpdateData;

/**
 * Webkul Walletsystem Class
 */
class CheckoutControllerSuccessObserver implements ObserverInterface
{
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
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;
    
    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;
    
    /**
     * @var  Webkul\Walletsystem\Model\WalletcreditamountFactory
     */
    protected $walletcreditAmountFactory;
    
    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $orderModel;
    
    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $walletTransaction;
    
    /**
     * @var WalletrecordFactory
     */
    protected $walletRecordFactory;
    
    /**
     * @var Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    
    /**
     * @var Magento\Framework\DB\Transaction
     */
    protected $dbTransaction;
    
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;
    
   /**
    * Constructor
    *
    * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
    * @param \Webkul\Walletsystem\Helper\Data $helper
    * @param \Webkul\Walletsystem\Helper\Mail $mailHelper
    * @param \Magento\Checkout\Model\Session $checkoutSession
    * @param \Magento\Catalog\Model\Product $productFactory
    * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    * @param InvoiceSender $invoiceSender
    * @param \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory
    * @param OrderFactory $orderModel
    * @param WallettransactionFactory $walletTransaction
    * @param WalletrecordFactory $walletRecordModel
    * @param InvoiceService $invoiceService
    * @param Transaction $dbTransaction
    * @param WalletUpdateData $walletUpdateData
    */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product $productFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        InvoiceSender $invoiceSender,
        \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        OrderFactory $orderModel,
        WallettransactionFactory $walletTransaction,
        WalletrecordFactory $walletRecordModel,
        InvoiceService $invoiceService,
        Transaction $dbTransaction,
        WalletUpdateData $walletUpdateData
    ) {
        $this->date = $date;
        $this->helper = $helper;
        $this->mailHelper = $mailHelper;
        $this->productFactory = $productFactory;
        $this->checkoutSession = $checkoutSession;
        $this->stockRegistry = $stockRegistry;
        $this->invoiceSender = $invoiceSender;
        $this->walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->orderModel = $orderModel;
        $this->walletTransaction = $walletTransaction;
        $this->walletRecordFactory = $walletRecordModel;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * Sales order place after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletTransaction = $this->walletTransaction->create();
        $orderIds = $observer->getOrderIds();
        foreach ($orderIds as $orderId) {
            $walletProductId = $this->helper->getWalletProductId();
            $order = $this->getLoadOrderById($orderId);
            if ($this->alreadyAddedInData($order)) {
                continue;
            }
            $customerId = $order->getCustomerId();
            $currencyCode = $order->getOrderCurrencyCode();
            $incrementId = $order->getIncrementId();
            $flag = 0;
            if ($orderId) {
                foreach ($order->getAllVisibleItems() as $item) {
                    $productId = $item->getProductId();
                    if ($productId == $walletProductId) {
                        $price = number_format($item->getBasePrice(), 2, '.', '');
                        $currPrice = number_format($item->getPrice(), 2, '.', '');
                        $flag = 1;
                    }
                }
            }
            $totalAmount = 0;
            $usedAmount = 0;
            $remainingAmount = 0;
            if ($flag == 1) {
                $transferAmountData = [
                    'customerid' => $customerId,
                    'walletamount' => $price,
                    'walletactiontype' => $walletTransaction::WALLET_ACTION_TYPE_CREDIT,
                    'curr_code' => $currencyCode,
                    'curr_amount' => $currPrice,
                    'walletnote' => __('Order id : %1 credited amount', $incrementId),
                    'sender_id' => $customerId,
                    'sender_type' => $walletTransaction::ORDER_PLACE_TYPE,
                    'order_id' => $orderId,
                    'status' => $walletTransaction::WALLET_TRANS_STATE_PENDING,
                    'increment_id' => $incrementId
                ];
                $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
                $this->mailHelper->checkAndUpdateWalletAmount($order);
            } else {
                $discountAmount = $order->getBaseWalletAmount();
                $discountcurrAmount = $order->getWalletAmount();
                $walletDiscountParams = [];
                if ($this->checkoutSession->getWalletDiscount()) {
                    $walletDiscountParams = $this->checkoutSession->getWalletDiscount();
                }
                if (array_key_exists('flag', $walletDiscountParams) && $walletDiscountParams['flag'] == 1) {
                    $transferAmountData = [
                        'customerid' => $customerId,
                        'walletamount' => -1 * $discountAmount,
                        'walletactiontype' => $walletTransaction::WALLET_ACTION_TYPE_DEBIT,
                        'curr_code' => $currencyCode,
                        'curr_amount' => -1 * $discountcurrAmount,
                        'walletnote' => __('Order id : %1 debited amount', $incrementId),
                        'sender_id' => $customerId,
                        'sender_type' => $walletTransaction::ORDER_PLACE_TYPE,
                        'order_id' => $orderId,
                        'status' => $walletTransaction::WALLET_TRANS_STATE_APPROVE,
                        'increment_id' => $incrementId
                    ];
                    $this->walletUpdateData->debitAmount($customerId, $transferAmountData);
                }
                $this->addCreditAmountData($orderId);
                //generate invoice automatically if whole amount is paid by wallet
                if ($order->getPayment()->getMethod() == 'walletsystem') {
                    $order = $this->updateOrder($order);
                    $order->setWalletInvoiced(1);
                    $this->saveObject($order);
                    $this->generateInvoiceForWalletPayment($order);
                }
            }
            $this->updateWaletProductQuantity($walletProductId);
        }
        $this->checkoutSession->unsWalletDiscount();
    }

    /**
     * Load order by id
     *
     * @param int $orderId
     * @return object
     */
    protected function getLoadOrderById($orderId)
    {
        $order = $this->orderModel
            ->create()
            ->load($orderId);
        return $order;
    }

    /**
     * Update wallet product quantity
     *
     * @param int $walletProductId
     */
    public function updateWaletProductQuantity($walletProductId)
    {
        $product = $this->productFactory->load($walletProductId); //load product which you want to update stock
        $stockItem = $this->stockRegistry->getStockItem($walletProductId); // load stock of that product
        $stockItem->setData('manage_stock', 0);
        $stockItem->setData('use_config_notify_stock_qty', 0);
        $stockItem->save(); //save stock of item
        $this->stockRegistry->updateStockItemBySku(\Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU, $stockItem);
        $product->setStockData(
            [
                'use_config_manage_stock' => 0,
                'manage_stock' => 0
            ]
        )->save(); //  also save product
    }

    /**
     * Add credit amount data
     *
     * @param int $orderId
     */
    public function addCreditAmountData($orderId)
    {
        $creditamount = $this->helper->calculateCreditAmountforCart();
        if ($creditamount > 0) {
            $status = $this->checkAlreadyAdded($orderId);
            if ($status) {
                $creditAmountModel = $this->walletcreditAmountFactory->create();
                $creditAmountModel->setAmount($creditamount)
                    ->setOrderId($orderId)
                    ->setStatus($creditAmountModel::WALLET_CREDIT_AMOUNT_STATUS_DISABLE)
                    ->save();
            }
        }
    }

    /**
     * Check already added
     *
     * @param int $orderId
     * @return bool
     */
    public function checkAlreadyAdded($orderId)
    {
        $creditAmountCollection = $this->walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId);
        if ($creditAmountCollection->getSize()) {
            return false;
        }
        return true;
    }

    /**
     * Generate invoice for wallet payment
     *
     * @param order $order
     */
    public function generateInvoiceForWalletPayment($order)
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService
                ->prepareInvoice($order);
            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);
            $invoice->save();
            $transactionSave = $this->dbTransaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
            //send notification code
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
            ->setIsCustomerNotified(true)
            ->save();
        }
    }

    /**
     * Already added in data
     *
     * @param object $order
     * @return bool
     */
    public function alreadyAddedInData($order)
    {
        $transactionCollection = $this->walletTransaction
            ->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId());

        if ($transactionCollection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Update order
     *
     * @param object $order
     * @return object
     */
    public function updateOrder($order)
    {
        $order->setGrandTotal($order->getWalletAmount());
        $order->save();
        return $order;
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
