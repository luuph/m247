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

namespace Bss\RewardPoint\Plugin\Sales\Order;

use Magento\Sales\Model\Order\Payment as OrderPayment;

class Payment
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
     * Payment constructor.
     * @param \Bss\RewardPoint\Helper\Data $helper
     * @param \Bss\RewardPoint\Model\RateFactory $rateFactory
     * @param \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Bss\RewardPoint\Helper\Data              $helper,
        \Bss\RewardPoint\Model\RateFactory        $rateFactory,
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
    ) {
        $this->helper = $helper;
        $this->rateFactory = $rateFactory;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * Before refund
     *
     * @param OrderPayment $subject
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeRefund(OrderPayment $subject, $creditmemo)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $creditmemo->getOrder();

        $customerId = $order->getCustomerId();
        $customerGroupId = $order->getCustomerGroupId();
        $websiteId = $creditmemo->getStore()->getWebsiteId();

        $has_message = $subject->hasMessage();
        $baseAmountToRefund = $subject->formatAmount($creditmemo->getBaseGrandTotal());

        if ($this->helper->isAutoRefundOrderToPoints($websiteId) && !$has_message) {
            $rate = $this->rateFactory->create()->fetch(
                $customerGroupId,
                $websiteId
            );

            $maximum_threshold = $this->helper->getPointsMaximum($websiteId);

            $point_balance = $this->transactionFactory->create()
                ->loadByCustomer($customerId, $websiteId)->getPointBalance();

            $baseCurrency = $rate->getBasecurrencyToPointRate() ?? 1;
            $baseAmountToRefund = ($baseAmountToRefund != null) ? $baseAmountToRefund : 0;
            $point = round($baseCurrency * $baseAmountToRefund);

            $total_amount = $baseAmountToRefund;

            if ($this->helper->isRestoreSpent($websiteId)) {
                if ($creditmemo->getBaseRwpAmount()) {
                    $point += round($baseCurrency * $creditmemo->getBaseRwpAmount());
                }
                $total_amount += ($creditmemo->getBaseRwpAmount() ?? 0);
            }

            $total_points = $point + $point_balance;

            if ($maximum_threshold > 0 && $total_points > $maximum_threshold) {
                $point = $maximum_threshold - $point_balance;
            }

            $total_amount = $subject->formatPrice($total_amount);

            $point_html = $point > 1 ? $point . ' points' : $point . ' point';

            $message = __('We refunded %1 (%2) to your reward point', $point_html, $total_amount);

            $subject->setMessage($message);
        }
    }
}
