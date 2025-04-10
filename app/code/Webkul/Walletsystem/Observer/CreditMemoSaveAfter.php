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
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;

/**
 * Webkul Walletsystem Class
 */
class CreditMemoSaveAfter implements ObserverInterface
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
     * @var WallettransactionFactory
     */
    protected $walletTransaction;

    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory
     */
    protected $walletrecord;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var  Webkul\Walletsystem\Model\WalletcreditamountFactory
     */
    protected $walletcreditAmountFactory;

    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;

    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $sessionManager;

    /**
     * @var  \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Initialize dependencies
     *
     * @param OrderFactory $orderFactory
     * @param \Webkul\Walletsystem\Helper\Mail $mailHelper
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param WallettransactionFactory $walletTransaction
     * @param WalletrecordFactory $walletRecord
     * @param \Magento\Framework\Session\SessionManager $sessionManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory
     * @param WalletUpdateData $walletUpdateData
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        WallettransactionFactory $walletTransaction,
        WalletrecordFactory $walletRecord,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        WalletUpdateData $walletUpdateData
    ) {
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->mailHelper = $mailHelper;
        $this->helper = $helper;
        $this->date = $date;
        $this->walletTransaction = $walletTransaction;
        $this->walletrecord = $walletRecord;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->walletUpdateData = $walletUpdateData;
    }

    /**
     * Credit memo save after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->sessionManager->unsWalletUsedForReturn();
        $params = $this->request->getParams();
        $walletTransaction = $this->walletTransaction->create();
        $orderId = $observer->getEvent()->getCreditmemo()->getOrderId();
        $order = $this->loadObject($this->orderFactory->create(), $orderId);

        if ($order->getPayment()->getMethod() != 'walletsystem' && !isset($params['creditmemo']['wallet_refund'])) {
            return true;
        }

        $walletRechargeOrder = $this->checkWalletRechargeOrder($order);
        $customerId = $order->getCustomerId();
        if ($customerId) {
            if (!$walletRechargeOrder) {
                if (isset($params['creditmemo']['do_offline'])) {
                    $doOffline = $params['creditmemo']['do_offline'];
                    if ($doOffline == 0) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __(
                                "You can not do online refund for this order,
                                 do the offline refund all the refunded amount will be transferred to customer's wallet"
                            )
                        );
                    }
                }
            }

            $rowId = 0;
            $baserefundTotalAmount = 0;
            $refundTotalAmount = 0;
            $totalAmount = 0;
            $remainingAmount = 0;
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $baserefundTotalAmount = $creditmemo->getBaseGrandTotal();
            $refundTotalAmount = $creditmemo->getGrandTotal();
            $baserefundTotalAmount = $this->getDeductCashBackRefundAmount($baserefundTotalAmount, $orderId);
            $flag = 0;
            $walletProductId = $this->helper->getWalletProductId();
            $currencyCode = $order->getOrderCurrencyCode();
            $baseCurrencyCode = $this->helper->getBaseCurrencyCode();
            $refundTotalAmount = $this->helper->getwkconvertCurrency(
                $currencyCode,
                $baseCurrencyCode,
                $baserefundTotalAmount
            );

            $incrementId = $order->getIncrementId();
            $orderItem = $order->getAllItems();
            $productIdArray = [];
            foreach ($orderItem as $value) {
                $productIdArray[] = $value->getProductId();
            }
            if (!in_array($walletProductId, $productIdArray)) {
                $this->updateWalletAmount(
                    $baserefundTotalAmount,
                    $refundTotalAmount,
                    $order,
                    $walletTransaction::WALLET_ACTION_TYPE_CREDIT
                );
            } else {
                $baserefundTotalAmount = $baserefundTotalAmount - $order->getDiscountAmount();
                $this->deductWalletAmountProduct($baserefundTotalAmount, $refundTotalAmount, $order);
            }
        }
    }

    /**
     * Deduct wallet amount from product
     *
     * @param int $baseAmount
     * @param int $amount
     * @param object $order
     */
    public function deductWalletAmountProduct($baseAmount, $amount, $order)
    {
        $walletTransaction = $this->walletTransaction->create();
        $currencyCode = $order->getOrderCurrencyCode();
        $orderId = $order->getId();
        $customerId = $order->getCustomerId();
        if ($customerId) {
            $remainingAmount = $this->helper->getWalletTotalAmount($customerId);
            $this->updateWalletAmount($baseAmount, $amount, $order, $walletTransaction::WALLET_ACTION_TYPE_DEBIT);
        }
    }

    /**
     * Update wallet amount
     *
     * @param int $baserefundTotalAmount
     * @param int $refundTotalAmount
     * @param object $order
     * @param string $action
     */
    public function updateWalletAmount($baserefundTotalAmount, $refundTotalAmount, $order, $action)
    {
        $walletTransaction = $this->walletTransaction->create();
        $customerId = $order->getCustomerId();
        $currencyCode = $order->getOrderCurrencyCode();
        $orderId = $order->getId();
        $incrementId = $order->getIncrementId();

        $transferAmountData = [
            'customerid' => $customerId,
            'walletamount' => $baserefundTotalAmount,
            'walletactiontype' => $action,
            'curr_code' => $currencyCode,
            'curr_amount' => $refundTotalAmount,
            'walletnote' => __('Order id : %1, %2ed amount', $incrementId, $action),
            'sender_id' => 0,
            'sender_type' => $walletTransaction::REFUND_TYPE,
            'order_id' => $orderId,
            'status' => $walletTransaction::WALLET_TRANS_STATE_APPROVE,
            'increment_id' => $incrementId
        ];
        if ($action==$walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
            $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
        } else {
            $this->walletUpdateData->debitAmount($customerId, $transferAmountData);
        }
    }

    /**
     * Get deduct cash back refund amount
     *
     * @param int $refundOrderAmount
     * @param int $orderId
     */
    public function getDeductCashBackRefundAmount($refundOrderAmount, $orderId)
    {
        $creditAmountModelClass = $this->walletcreditAmountFactory->create();
        $creditAmountModelCollection = $this->walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', $creditAmountModelClass::WALLET_CREDIT_AMOUNT_STATUS_DISABLE);
        if ($creditAmountModelCollection->getSize()) {
            foreach ($creditAmountModelCollection as $creditamount) {
                $rowId = $creditamount->getEntityId();
                $creditAmountModel = $this->loadObject($this->walletcreditAmountFactory->create(), $rowId);
                $amount = $creditAmountModel->getAmount();
                $refundOrderAmount -= $amount;
                $creditAmountModel->setRefundAmount($amount);
                $creditAmountModel->setStatus($creditAmountModelClass::WALLET_CREDIT_AMOUNT_STATUS_ENABLE);
                $this->saveObject($creditAmountModel);
            }
        } else {
            $refundAmount = 0;
            $amount = 0;
            $creditAmountModel = $this->walletcreditAmountFactory->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('status', $creditAmountModelClass::WALLET_CREDIT_AMOUNT_STATUS_ENABLE);

            if ($creditAmountModel->getSize()) {
                foreach ($creditAmountModel as $creditamount) {
                    $refundAmount = $creditamount->getRefundAmount();
                    $amount = $creditamount->getAmount();
                }
            }
            if ($amount == $refundAmount) {
                return $refundOrderAmount;
            } else {
                $leftAmount = $amount - $refundAmount;
                if ($refundOrderAmount >= $leftAmount) {
                    $finalRefundAmount = $refundOrderAmount - $leftAmount;
                    $this->updateRefundAmount($leftAmount + $refundAmount, $orderId);
                    return $finalRefundAmount;
                } else {
                    $this->updateRefundAmount($refundOrderAmount + $refundAmount, $orderId);
                    return 0;
                }
            }
        }
        return $refundOrderAmount;
    }

    /**
     * Update refund amount
     *
     * @param int $amount
     * @param int $orderId
     */
    public function updateRefundAmount($amount, $orderId)
    {
        $creditAmount = $this->walletcreditAmountFactory->create();
        $creditAmountModel = $this->walletcreditAmountFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('status', $creditAmount::WALLET_CREDIT_AMOUNT_STATUS_ENABLE);
        if ($creditAmountModel->getSize()) {
            foreach ($creditAmountModel as $amountModel) {
                $amountModel->setRefundAmount($amount);
                $this->saveObject($amountModel);
            }
        }
    }

    /**
     * Check wallet recharge order
     *
     * @param object $order
     * @return bool
     */
    public function checkWalletRechargeOrder($order)
    {
        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($item->getSku() == \Webkul\Walletsystem\Helper\Data::WALLET_PRODUCT_SKU) {
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate and deduct wallet cashback for order
     *
     * @param int $orderId
     */
    public function calculateAndDeductWalletCashbackForOrder($orderId)
    {
        try {
            $baserefundTotalAmount = $this->getDeductCashBackRefundAmount(0, $orderId);
            $order = $this->loadObject($this->orderFactory->create(), $orderId);
            $baserefundTotalAmount = $baserefundTotalAmount - $order->getDiscountAmount();
            if ((int)$order->getDiscountAmount()) {
                $this->deductWalletAmountProduct($baserefundTotalAmount, 0, $order);
            }
            return;
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
        }
    }

    /**
     * Performs load operation on object
     *
     * @param object $object
     * @param string $data
     * @return object|null
     */
    public function loadObject($object, $data)
    {
        return $object->load($data);
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
