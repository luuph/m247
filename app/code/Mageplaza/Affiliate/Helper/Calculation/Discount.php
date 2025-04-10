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

namespace Mageplaza\Affiliate\Helper\Calculation;

use Exception;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Mageplaza\Affiliate\Helper\Calculation;

/**
 * Class Discount
 * @package Mageplaza\Affiliate\Helper\Calculation
 */
class Discount extends Calculation
{
    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this|Discount
     * @throws LocalizedException
     * @throws InputException
     * @throws FailureToSendException
     * @throws Exception
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $items = $shippingAssignment->getItems();

        if (!$this->isEnabled() || !$items
            || !$this->canCalculate($quote->getStoreId(), $items, true)
            || $quote->getIsMultiShipping()) {
            $this->resetAffiliate($quote);

            return $this;
        }

        $itemFields = [];
        $this->resetAffiliateData(
            $items,
            $quote,
            array_merge($itemFields, ['affiliate_discount_shipping_amount', 'affiliate_base_discount_shipping_amount']),
            $itemFields
        );
        $account      = $this->registry->registry('mp_affiliate_account');
        $affiliateKey = $this->getAffiliateKey(); // if no cookie then first order key
        $affSource    = $this->getAffiliateSourceFromCookie(self::AFFILIATE_COOKIE_SOURCE_NAME);

        if ($affSource === 'coupon') {
            $affCodeWithPrefix = explode('-', $affiliateKey);
            if (count($affCodeWithPrefix) !== 2) {
                return $this;
            }
        }
        $totalDiscount = 0;
        foreach ($this->getAvailableCampaign($account, $items) as $campaign) {
            $isCalculateTax      = $campaign->getApplyDiscountOnTax();
            $isCalculateShipping = $campaign->getApplyToShipping();
            $totalCart           = $this->getTotalOnCart($items, $quote, $isCalculateShipping, $isCalculateTax, false);
            if ($totalCart <= 0.001) {
                break;
            }
            $totalDiscount += $campaign->getDiscountAffiliate();

        }

        $quote->setAffiliateDiscountAmount($totalDiscount);
        $quote->setBaseAffiliateDiscountAmount($totalDiscount);
        $baseGrandTotal = $total->getBaseGrandTotal() - $totalDiscount;
        $grandTotal     = $total->getGrandTotal() - $totalDiscount;
        if ($grandTotal <= 0.0001) {
            $baseGrandTotal = $grandTotal = 0;
        }
        $quote->setAffiliateKey($this->getAffiliateKeyFromCookie());
        $total->setBaseGrandTotal($baseGrandTotal);
        $total->setGrandTotal($grandTotal);
        $quote->setBaseGrandTotal($baseGrandTotal);
        $quote->setGrandTotal($grandTotal);
        $quote->save();

        return $this;
    }

    /**
     * Calculate Item Discount
     *
     * @param $item
     * @param $total
     * @param $totalDiscount
     * @param $baseDiscount
     * @param $discount
     * @param $isCalculateTax
     * @param $lastItem
     */
    public function calculateItemDiscount(
        $item,
        $total,
        $totalDiscount,
        &$baseDiscount,
        &$discount,
        $isCalculateTax,
        &$lastItem
    ) {
        $itemBaseDiscount = ($this->getItemTotalForDiscount($item, $isCalculateTax, false) / $total) * $totalDiscount;
        $itemBaseDiscount = $this->round($itemBaseDiscount, 'base');
        $itemDiscount     = $this->convertPrice($itemBaseDiscount, false, false, $item->getStoreId());
        $itemDiscount     = $this->round($itemDiscount);
        $item->setBaseAffiliateDiscountAmount($item->getBaseAffiliateDiscountAmount() + $itemBaseDiscount)
            ->setAffiliateDiscountAmount($item->getAffiliateDiscountAmount() + $itemDiscount);
        $baseDiscount += $itemBaseDiscount;
        $discount     += $itemDiscount;
        $lastItem     = $item;
    }
}
