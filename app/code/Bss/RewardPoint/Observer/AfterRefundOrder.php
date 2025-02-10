<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\RewardPoint\Observer;

use Bss\RewardPoint\Model\Config\Source\TransactionActions;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class AfterRefundOrder implements ObserverInterface
{
    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\RewardPoint\Model\RateFactory
     */
    protected $rateFactory;

    /**
     * @var \Bss\RewardPoint\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AfterRefundOrder constructor.
     * @param \Bss\RewardPoint\Helper\Data $helper
     * @param \Bss\RewardPoint\Model\RateFactory $rateFactory
     * @param \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Bss\RewardPoint\Helper\Data              $helper,
        \Bss\RewardPoint\Model\RateFactory        $rateFactory,
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory,
        LoggerInterface                           $logger
    ) {
        $this->helper = $helper;
        $this->rateFactory = $rateFactory;
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order_Creditmemo $creditMemo */
        if (!$creditMemo = $observer->getEvent()->getCreditmemo()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $creditMemo->getOrder();

        $customerId = $order->getCustomerId();
        $customerGroupId = $order->getCustomerGroupId();
        $websiteId = $creditMemo->getStore()->getWebsiteId();
        $baseGrandTotal = $creditMemo->getBaseGrandTotal();
        $baseRwpAmount = $creditMemo->getBaseRwpAmount();

        if (!$this->helper->isActive($websiteId)) {
            return;
        }

        $rate = $this->rateFactory->create()->fetch(
            $customerGroupId,
            $websiteId
        );

        $point = $this->getPoint($websiteId, $rate, $baseGrandTotal, $baseRwpAmount);

        if ($point > 0) {
            $data = [
                'website_id' => $websiteId,
                'customer_id' => $customerId,
                'point' => $point,
                'action_id' => $creditMemo->getId(),
                'action' => TransactionActions::ORDER_REFUND,
                'created_at' => $this->helper->getCreateAt(),
                'created_by' => $order->getCustomerEmail(),
                'is_expired' => (bool)$this->helper->getExpireDay($websiteId),
                'expires_at' => $this->helper->getExpireDay($websiteId)
            ];

            $transaction = $this->transactionFactory->create();
            try {
                $transaction->setData($data);
                $transaction->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }

            if ($this->helper->isRestoreSpent($websiteId) && !$this->helper->isAutoRefundOrderToPoints($websiteId)) {
                $point = $transaction->getPoint();
                $point_html = $point > 1 ? $point . ' points' : $point . ' point';
                $baseRwpAmount = $order->formatPrice($baseRwpAmount);
                $message = __('We refunded %1 (%2) to your reward point', $point_html, $baseRwpAmount);

                $order->addStatusHistoryComment(__($message))->save();
            }
        }
    }

    /**
     * Get point
     *
     * @param int $websiteId
     * @param \Bss\RewardPoint\Model\Rate $rate
     * @param float $baseGrandTotal
     * @param float $baseRwpAmount
     * @return float|int
     */
    protected function getPoint($websiteId, $rate, $baseGrandTotal, $baseRwpAmount)
    {
        $point = 0;
        $baseCurrency = $rate->getBasecurrencyToPointRate() ?? 1;
        if ($this->helper->isAutoRefundOrderToPoints($websiteId)) {
            $baseGrandTotal = $baseGrandTotal != null ? $baseGrandTotal : 0;
            $point += round($baseCurrency * $baseGrandTotal);
        }

        if ($this->helper->isRestoreSpent($websiteId)) {
            $baseRwpAmount = $baseRwpAmount != null ? $baseRwpAmount : 0;
            $point += round($baseCurrency * $baseRwpAmount);
        }
        return $point;
    }
}
