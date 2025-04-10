<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Block\Account\Reports;

use Magento\Cms\Block\Block;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\Order;
use Mageplaza\AffiliateUltimate\Helper\Data as AffiliateHelper;
use Mageplaza\Affiliate\Helper\Payment;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\CampaignFactory;
use Mageplaza\Affiliate\Model\ResourceModel\Transaction\Collection as TransactionCollection;
use Mageplaza\Affiliate\Model\TransactionFactory;
use Mageplaza\Affiliate\Model\WithdrawFactory;
use Mageplaza\Affiliate\Model\WithdrawhistoryFactory;
use \Mageplaza\AffiliateUltimate\Block\Account\Reports;
use Mageplaza\Affiliate\Model\Transaction;
use Mageplaza\Affiliate\Model\Transaction\Status;

/**
 * Class Traffic
 * @package Mageplaza\AffiliateUltimate\Block\Account
 */
class Traffic extends Reports
{
    /**
     * @var Collection
     */
    protected $orderCollection;

    /**
     * @var TransactionCollection
     */
    protected $transCollection;

    /**
     * @var Order
     */
    protected $order;

    /**
     * The flag to prevent duplicate the order
     */
    private $lastOrderCount;

    /**
     * The flag to prevent duplicate the order
     */
    private $lastOrderedItemQty;

    /**
     * The flag to prevent duplicate the order
     */
    private $lastInvoiced;

    /**
     * The flag to prevent duplicate the order
     */
    private $lastTotalCommission = [];

    /**
     * @var Block
     */
    protected $cmsBlock;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param View $helperView
     * @param AffiliateHelper $affiliateHelper
     * @param Payment $paymentHelper
     * @param JsonHelper $jsonHelper
     * @param Registry $registry
     * @param PriceHelper $pricingHelper
     * @param ObjectManagerInterface $objectManager
     * @param CampaignFactory $campaignFactory
     * @param AccountFactory $accountFactory
     * @param WithdrawFactory $withdrawFactory
     * @param TransactionFactory $transactionFactory
     * @param TransactionCollection $transCollection
     * @param Collection $orderCollection
     * @param Order $order
     * @param Block $cmsBlock
     * @param array $data
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        View $helperView,
        AffiliateHelper $affiliateHelper,
        Payment $paymentHelper,
        JsonHelper $jsonHelper,
        Registry $registry,
        PriceHelper $pricingHelper,
        ObjectManagerInterface $objectManager,
        CampaignFactory $campaignFactory,
        AccountFactory $accountFactory,
        WithdrawFactory $withdrawFactory,
        TransactionFactory $transactionFactory,
        TransactionCollection $transCollection,
        Collection $orderCollection,
        Order $order,
        Block $cmsBlock,
        CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->orderCollection = $orderCollection;
        $this->transCollection = $transCollection;
        $this->order           = $order;
        $this->cmsBlock        = $cmsBlock;
        $this->customerFactory    = $customerFactory;
        parent::__construct(
            $context,
            $customerSession,
            $helperView,
            $affiliateHelper,
            $paymentHelper,
            $jsonHelper,
            $registry,
            $pricingHelper,
            $objectManager,
            $campaignFactory,
            $accountFactory,
            $withdrawFactory,
            $transactionFactory,
            $customerFactory,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Traffic Source Statistics'));

        return parent::_prepareLayout();
    }

    /**
     * @return int[][]
     */
    public function getTrafficData()
    {
        $transactions = $this->getTransactionCollection();

        $data = $this->createArrayData();

        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $affiliateKey = $transaction->getOrder()->getAffiliateKey();

            $couponWithPreFix = explode('-', $affiliateKey);
            if (count($couponWithPreFix) === 2) {
                $couponCodeData = &$data['coupon_code'];
                $this->getDataFromTransactions($couponCodeData, $transaction);
            } else {
                $referUrlData = &$data['refer_url'];
                $this->getDataFromTransactions($referUrlData, $transaction);
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @param $transaction
     */
    public function getDataFromTransactions(&$data, $transaction)
    {
        if ($transaction->getAction() === 'order/invoice') {
            if ((int) $transaction->getStatus() === Status::STATUS_CANCELED) {
                $this->getCommissionCanceled($data, $transaction);
            }

            $this->getTotalInvoiced($data, $transaction);
            $this->getOrderCount($data, $transaction);
            $this->getOrderedItemQty($data, $transaction);
        }

        if ((int) $transaction->getStatus() !== Status::STATUS_CANCELED) {
            $this->getGrandCommission($data, $transaction);
            if ($transaction->getAction() === 'order/refund') {
                $this->getCommissionRefunded($data, $transaction);
            }
        }
    }

    /**
     * @return TransactionCollection
     */
    public function getTransactionCollection()
    {
        $transCollection = $this->transCollection->addFieldToFilter('order_id', ['neq' => 'NULL'])
            ->addFieldToFilter('account_id', ['eq' => $this->getCurrentAccount()->getId()]);

        return $transCollection;
    }

    /**
     * @param array $data
     * @param $transaction
     */
    public function getCommissionRefunded(&$data, $transaction)
    {
        $data['refunded'] += (-$transaction->getAmount());
    }

    /**
     * @param array $data
     * @param $transaction
     */
    public function getCommissionCanceled(&$data, $transaction)
    {
        $data['canceled'] += $transaction->getAmount();
    }

    /**
     * @param array $data
     * @param $transaction
     */
    public function getGrandCommission(&$data, $transaction)
    {
        if (!$this->_affiliateHelper->getStatisticsTotalCommissionOnHold() && (int) $transaction->getStatus() === Status::STATUS_HOLD) {
            $data['grand_total'] += 0;
            $this->lastTotalCommission[] = $transaction->getOrderId();
        } else {
            if (count($this->lastTotalCommission)) {
                if (in_array($transaction->getOrderId(), $this->lastTotalCommission)) {
                    if ($transaction->getAction() === 'order/refund') {
                        $data['grand_total'] += 0;
                        return;
                    }
                }
            }

            $data['grand_total'] += $transaction->getAmount();
        }
    }

    /**
     * @param array $data
     * @param $transaction
     */
    public function getOrderCount(&$data, $transaction)
    {
        $order = clone $transaction->getOrder();
        if ($order->getState() !== Order::STATE_CLOSED) {
            if (!$this->lastOrderCount || $this->lastOrderCount->getOrderId() !== $transaction->getOrderId()) {
                $data['order_count'] += 1;
            }
            $this->lastOrderCount = $transaction;
        }
    }

    /**
     * @param array $data
     * @param $transaction
     */
    public function getOrderedItemQty(&$data, $transaction)
    {
        $order = clone $transaction->getOrder();
        if ($order->getState() !== Order::STATE_CLOSED) {
            if (!$this->lastOrderedItemQty || $this->lastOrderedItemQty->getOrderId() !== $transaction->getOrderId()) {
                foreach ($order->getItems() as $item) {
                    if (!$item->getParentItemId()) {
                        $qtyRefunded = $item->getQtyRefunded();
                        $qtyInvoiced = $item->getQtyInvoiced();

                        $data['order_item_qty'] += ($qtyInvoiced - $qtyRefunded);
                    }
                }
            }
            $this->lastOrderedItemQty = $transaction;
        }
    }

    /**
     * @param array $data
     * @param $transaction
     */
    public function getTotalInvoiced(&$data, $transaction)
    {
        $order = clone $transaction->getOrder();

        if ($order->getState() !== Order::STATE_CLOSED && $count = count($order->getInvoiceCollection())) {
            if (!$this->lastInvoiced || $this->lastInvoiced->getOrderId() !== $transaction->getOrderId()) {
                $data['total_invoiced'] += $count;
            }
            $this->lastInvoiced = $transaction;
        }
    }

    /**
     * @return int[][]
     */
    public function createArrayData()
    {
        $subData = [
            'order_count'    => 0,
            'order_item_qty' => 0,
            'total_invoiced' => 0,
            'refunded'       => 0,
            'canceled'       => 0,
            'grand_total'    => 0,
        ];

        return [
            'refer_url'   => $subData,
            'coupon_code' => $subData
        ];
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getBlock($storeId = null)
    {
        return $this->getAffiliateHelper()->getConfigGeneral('traffic_page', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCustomBlockContent($storeId = null)
    {
        return $this->getAffiliateHelper()->getConfigGeneral('traffic_page_content', $storeId);
    }

    /**
     * @param int $blockId
     *
     * @return mixed
     */
    public function getCmsBlockContent($blockId)
    {
        return $this->cmsBlock->setBlockId($blockId)->toHtml();
    }
}
