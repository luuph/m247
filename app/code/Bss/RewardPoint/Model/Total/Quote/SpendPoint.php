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
namespace Bss\RewardPoint\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

class SpendPoint extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $helper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Bss\RewardPoint\Model\RateFactory
     */
    protected $rateFactory;
    /**
     * SpendPoint constructor.
     * @param \Bss\RewardPoint\Helper\Data $helper
     * @param \Bss\RewardPoint\Model\RateFactory $rateFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Bss\RewardPoint\Helper\Data $helper,
        \Bss\RewardPoint\Model\RateFactory $rateFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->helper = $helper;
        $this->rateFactory = $rateFactory;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->setCode('spend_point');
    }

    /**
     * Collect
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $store = $this->storeManager->getStore($quote->getStoreId());
        $websiteId = $store->getWebsiteId();
        $customerGroupId = $quote->getCustomerGroupId();

        if (!$quote->getId() || $quote->getIsMultiShipping() || !$this->helper->isActive($websiteId)) {
            return $this;
        }

        $total->setRwpAmount(0)->setBaseRwpAmount(0);

        if ($total->getBaseGrandTotal() > 0 && $quote->getCustomer()->getId() && $quote->getSpendPoints()) {
            $rate = $this->rateFactory->create()->fetch(
                $customerGroupId,
                $websiteId
            );
            $baseCurrency = $rate->getBasecurrencyToPointRate() ?? 1;
            $baseRwpAmount = $quote->getSpendPoints()/$baseCurrency;
            $rwpAmount = $this->priceCurrency->convert($baseRwpAmount, $quote->getStore());

            $taxAmountUsed = $baseTaxAmountUsed = $shippingAmountUsed = $baseShippingAmountUsed = 0;
            $baseGrandTotal = (float) $total->getBaseGrandTotal();
            $grandTotal = $total->getGrandTotal();

            if (!$this->helper->isSpendPointforTax($websiteId)) {
                $taxAmountUsed = $total->getTaxAmount();
                $baseTaxAmountUsed = $total->getBaseTaxAmount();
                $baseGrandTotal -= $baseTaxAmountUsed;
                $grandTotal -= $taxAmountUsed;
            }
            if (!$this->helper->isSpendPointforShip($websiteId)) {
                $shippingAmountUsed = $total->getShippingAmount();
                $baseShippingAmountUsed = $total->getBaseShippingAmount();
                $baseGrandTotal -= $baseShippingAmountUsed;
                $grandTotal -= $shippingAmountUsed;
            }

            if ($baseRwpAmount >= $baseGrandTotal) {
                $baseRwpAmount = $baseGrandTotal;
                $rwpAmount = $grandTotal;
                $total->setGrandTotal($taxAmountUsed + $shippingAmountUsed);
                $total->setBaseGrandTotal($baseTaxAmountUsed + $baseShippingAmountUsed);
            } else {
                $total->setGrandTotal($total->getGrandTotal() - $rwpAmount);
                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseRwpAmount);
            }

            $baseCurrency = $rate->getBasecurrencyToPointRate() ?? 1;
            $spend_point = ceil($baseRwpAmount * $baseCurrency);
            $quote->setSpendPoints($spend_point);
            $quote->setRwpAmount($rwpAmount);
            $quote->setBaseRwpAmount($baseRwpAmount);

            $total->setRwpAmount($rwpAmount);
            $total->setBaseRwpAmount($baseRwpAmount);
        }
        return $this;
    }

    /**
     * Fetch
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {

        if ($total->getRwpAmount()) {
            return [
                'code' => $this->getCode(),
                'title' => __('Spend Point'),
                'value' => $total->getRwpAmount() ? -$total->getRwpAmount() : 0,
            ];
        }
        return null;
    }
}
