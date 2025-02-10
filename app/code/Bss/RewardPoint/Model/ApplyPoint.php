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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Model;

class ApplyPoint
{
    /**
     * @var \Bss\RewardPoint\Helper\InjectModel
     */
    protected $helperInject;

    /**
     * @var \Bss\RewardPoint\Model\RateFactory
     */
    protected $rateFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \Bss\RewardPoint\Helper\Data $helper
     * @param \Bss\RewardPoint\Helper\InjectModel $helperInject
     * @param \Bss\RewardPoint\Model\RateFactory $rateFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Bss\RewardPoint\Helper\Data $helper,
        \Bss\RewardPoint\Helper\InjectModel $helperInject,
        \Bss\RewardPoint\Model\RateFactory $rateFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->helper = $helper;
        $this->helperInject = $helperInject;
        $this->rateFactory = $rateFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Check and use point
     *
     * @param int|float $spendPoint
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculatorApplyPoint($spendPoint, $quote)
    {
        $websiteId = $quote->getStore()->getWebsiteId();
        $customerId = $quote->getCustomerId();
        $customerGroupId = $quote->getCustomerGroupId();

        $totalPoints = $this->helperInject->createTransactionModel()
            ->loadByCustomer($customerId, $websiteId)->getPointBalance();

        /** @var \Bss\RewardPoint\Model\Rate $rate */
        $rate = $this->rateFactory->create()->fetch(
            $customerGroupId,
            $websiteId
        );
        $maximumSpendPoint = (int)$this->helper->getMaximumPointCanSpendPerOrder();
        $response['message'] = __('Successfully!');
        $response['status_message']  = 'success';

        if ($spendPoint < 0 || !$quote->getId()) {
            $response['message'] = __('Something went wrong. Please enter a value again');
            $response['status_message']  = 'error';
            $response['status']  = true;
        } elseif ($spendPoint == 0) {
            $quote->setSpendPoints($spendPoint);
            $quote->collectTotals();
            $quote->save();
            $response['message'] = __('Successfully cancel!');
            $response['status'] = true;
            $response['cancel'] = true;
            $response['spend_point'] = 0;
            $response['amount'] = 0;
            $response['pointLeft'] = $totalPoints;
        } else {
            if ($maximumSpendPoint > 0 && $spendPoint > $maximumSpendPoint) {
                $response['message'] = __("You can't use more reward point than you have");
                $response['status_message'] = 'warning';
                $spendPoint = $maximumSpendPoint;
            }

            if ($spendPoint > $totalPoints) {
                $response['message'] = __('You don’t have enough reward points. Earn more!.');
                $response['status_message'] = 'warning';
                $spendPoint = $totalPoints;
            }

            $baseAmount = $this->priceCurrency->round($spendPoint / ($rate->getBasecurrencyToPointRate() ?? 1));

            $quote->setSpendPoints($spendPoint);
            $quote->collectTotals();
            $quote->save();
            $baseRwpAmount = $this->priceCurrency->round($quote->getBaseRwpAmount());

            if ($baseAmount > $baseRwpAmount) {
                $response['status_message'] = 'warning';
                $response['message'] = __("You can't use more reward point than the order amount.");
            }

            $spendPoint = $quote->getSpendPoints();
            $pointLeft = $totalPoints - $spendPoint;
            $response['status'] = true;
            $response['spend_point'] = $spendPoint;
            $response['amount'] = $quote->getRwpAmount();
            $response['pointLeft'] = $pointLeft;
        }
        return $response;
    }
}
