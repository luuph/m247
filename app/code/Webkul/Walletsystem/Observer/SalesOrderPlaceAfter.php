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
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Session\SessionManager;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Webkul Walletsystem Class
 */
class SalesOrderPlaceAfter implements ObserverInterface
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
     * @var Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Magento\Framework\DB\Transaction
     */
    protected $dbTransaction;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var SessionManager
     */
    protected $coreSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var PaymentTransaction\BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * Initialize dependencies
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
     * @param InvoiceService $invoiceService
     * @param PaymentTransaction\BuilderInterface $transactionBuilder
     * @param Transaction $dbTransaction
     * @param \Psr\Log\LoggerInterface $log
     * @param WalletUpdateData $walletUpdateData
     * @param QuoteRepository $quoteRepository
     * @param SessionManager $coreSession
     * @param OrderRepositoryInterface $orderRepository
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
        InvoiceService $invoiceService,
        PaymentTransaction\BuilderInterface $transactionBuilder,
        Transaction $dbTransaction,
        \Psr\Log\LoggerInterface $log,
        WalletUpdateData $walletUpdateData,
        QuoteRepository $quoteRepository,
        SessionManager $coreSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->date = $date;
        $this->logger = $log;
        $this->helper = $helper;
        $this->mailHelper = $mailHelper;
        $this->productFactory = $productFactory;
        $this->checkoutSession = $checkoutSession;
        $this->stockRegistry = $stockRegistry;
        $this->transactionBuilder = $transactionBuilder;
        $this->invoiceSender = $invoiceSender;
        $this->walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->orderModel = $orderModel;
        $this->walletTransaction = $walletTransaction;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
        $this->walletUpdateData = $walletUpdateData;
        $this->quoteRepository = $quoteRepository;
        $this->coreSession = $coreSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Sales order place after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isMultiShipping = $this->checkoutSession->getQuote()->getIsMultiShipping();
        if (!$isMultiShipping) {
            $walletProductId = $this->helper->getWalletProductId();
            $orders = $observer->getOrders(); // Fetch the orders array
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    if ($order instanceof \Magento\Sales\Model\Order) {
                        $orderId = $order->getId();
                        // $orderId = $observer->getOrder()->getId();
                        $order = $this->orderRepository->get($orderId);
                        if ($this->alreadyAddedInData($order)) {
                            return;
                        }
                        $this->setDataInWalletTable($orderId, $order);
                    }
                }
            }       
        } else {
            $quoteId = $this->checkoutSession->getLastQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            if ($quote->getIsMultiShipping() == 1 || $isMultiShipping == 1) {
                $orderIds = $this->coreSession->getOrderIds();
                foreach ($orderIds as $orderId => $orderIncId) {
                    $lastOrderId = $orderId;
                    $order = $this->orderRepository->get($lastOrderId);
                    if ($this->alreadyAddedInData($order)) {
                        continue;
                    }
                    $this->setDataInWalletTable($lastOrderId, $order);
                }
            }
        }
        $this->checkoutSession->unsWalletDiscount();
        $this->checkoutSession->unsPaypalAmountForWallet();
    }

    /**
     * Set data in wallet table
     *
     * @param int $orderId
     * @param object $order
     */
    public function setDataInWalletTable($orderId, $order)
    {
        $walletTransaction = $this->walletTransaction->create();
        $walletProductId = $this->helper->getWalletProductId();
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
                $order->save();
                $this->generateInvoiceForWalletPayment($order);
                $this->createTransaction($order);
            }
        }
        $this->updateWaletProductQuantity($walletProductId);
    }

    /**
     * Add Credit Amount Data function
     *
     * @param int $orderId
     */
    public function addCreditAmountData($orderId)
    {
        $creditamount = $this->helper->calculateCreditAmountforCart($orderId);
        if ($creditamount > 0) {
            $creditAmountModel = $this->walletcreditAmountFactory->create();
            $creditAmountModel->setAmount($creditamount)
                ->setOrderId($orderId)
                ->setStatus($creditAmountModel::WALLET_CREDIT_AMOUNT_STATUS_DISABLE)
                ->save();
        }
    }

    /**
     * Will be called only in case of full payments using wallets
     * Get products from marketplacesaleslist table sorted as per the  actual_seller_amount
     * Distribute wallet amount used
     *
     * @param object $order
     **/
    public function generateInvoiceForWalletPayment($order)
    {
        if ($order->canInvoice()) {
            try {
                $invoice = $this->invoiceService
                                ->prepareInvoice($order);
                $invoice->register();
                $invoice->getOrder()->setIsInProcess(true);
                $this->coreSession->setCustomInvoiceId(true);
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
            } catch (\Exception $e) {
                $this->coreSession->unsCustomInvoiceId();
                $this->logger->info("error while creating invoice ".$e->getMessage());
            }
        }
    }

    /**
     * Check if transaction Already Added
     *
     * @param object $order
     * @return boolean
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
     * Update Walet Product Quantity function
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
     * Update order
     *
     * @param object $order
     * @return object
     */
    public function updateOrder($order)
    {
        $order->setBaseTotal(-$order->getWalletAmount());
        $baseGrandTotal = $this->helper->getwkconvertCurrency(
            $order->getOrderCurrencyCode(),
            $this->helper->getBaseCurrencyCode(),
            -$order->getWalletAmount()
        );
        $totalGrandTotal = $order->getGrandTotal() + $order->getTaxAmount() + $order->getWalletAmount();
        $totalBaseGrandTotal = $baseGrandTotal + $order->getBaseTaxAmount();
        $order->setBaseGrandTotal($totalBaseGrandTotal);
        $order->setGrandTotal($totalGrandTotal);
        $order->save();
        return $order;
    }

    /**
     * Creates transactions for payments
     *
     * @param  \Mageto\Sales\Model\Order $order
     **/
    public function createTransaction($order)
    {
        $transId = 'wk_wallet_system'.$order->getId().'-'.rand();
        $transArray = [
            'id' => $transId,
            'amount' => - (int) $order->getWalletAmount()
        ];

        $payment = $order->getPayment();
        $payment->setLastTransId($transArray['id']);
        $payment->setTransactionId($transArray['id']);
        $payment->setAdditionalInformation(
            [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $transArray]
        );
        $formatedPrice = $order->getBaseCurrency()->formatTxt(
            -$order->getWalletAmount()
        );

        $message = __('The authorized amount is %1.', $formatedPrice);

        $trans = $this->transactionBuilder;
        $transaction = $trans->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($transArray['id'])
            ->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $transArray]
            )
            ->setFailSafe(true)
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

        $payment->addTransactionCommentsToOrder(
            $transaction,
            $message
        );
        $payment->setParentTransactionId(null);
        $payment->save();
        $order->save();
    }
}
