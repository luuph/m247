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

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Mageplaza\Affiliate\Block\Adminhtml\Campaign\Edit\Tab\Commissions\Arraycommission;
use Mageplaza\Affiliate\Helper\Calculation;

/**
 * Class Commission
 * @package Mageplaza\Affiliate\Helper\Calculation
 */
class Commission extends Calculation
{
    /**
     * @var null
     */
    protected $fieldSuffix = null;

    /**
     * @var null
     */
    protected $tierId = null;

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this|Commission
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $items = $shippingAssignment->getItems();

        if (!$this->isEnabled() || !$items || !$this->canCalculate($quote->getStoreId(),$items) ||
            $quote->getIsMultiShipping()) {
            $this->resetAffiliate($quote);

            return $this;
        }
        $affiliateKey       = $this->getAffiliateKey();
        $affSource          = $this->getAffiliateSourceFromCookie(self::AFFILIATE_COOKIE_SOURCE_NAME);
        $campaignCouponCode = null;
        if ($affSource === 'coupon') {
            $affCodeWithPrefix = explode('-', $affiliateKey);
            if (count($affCodeWithPrefix) === 2) {
                $campaignCouponCode = $affCodeWithPrefix[1];
            }
        }
        $quote->setAffiliateKey($this->getAffiliateKey());
        $itemFields = ['affiliate_commission'];
        $this->resetAffiliateData(
            $items,
            $quote,
            array_merge($itemFields, ['affiliate_shipping_commission']),
            $itemFields
        );
        $commissionResult    = [];
        $commissionData      = [];
        $this->fieldSuffix   = $this->hasFirstOrder() ? '_second' : '';
        $account             = $this->registry->registry('mp_affiliate_account');
        $tree                = $this->getAffiliateTree($account);
        $isCalculateShipping = $this->isEarnCommissionFromShipping($quote->getStoreId());
        $isCalculateTax      = $this->isEarnCommissionFromTax($quote->getStoreId());
        $totalOnCart         = $this->getTotalOnCart($items, $quote, $isCalculateShipping, $isCalculateTax, true, true);
        if ($totalOnCart <= 0.001) {
            return $this;
        }
        foreach ($this->getAvailableCampaign($account, $campaignCouponCode) as $campaign) {
            $commissions = $campaign->getCommission();
            $campaigns[] = $campaign->getId();
            $tierData    = [];
            foreach ($commissions as $key => $tier) {
                if (!isset($tree[$key])) {
                    break;
                }
                // get customer id
                $this->tierId = $tree[$key];
                if (!isset($commissionResult[$this->tierId])) {
                    $commissionResult[$this->tierId] = 0;
                }

                $totalCommission = 0;
                $lastItem        = null;
                foreach ($campaign->getCampaignItems() as $item) {
                    $this->calculateTierItem(
                        $item,
                        $totalOnCart,
                        $tier,
                        $campaign,
                        $totalCommission,
                        $isCalculateTax,
                        $lastItem
                    );

                }

                $tierData[$this->tierId] = $totalCommission;
            }
            $commissionData[$campaign->getId()] = $tierData;
            $quote->setAffiliateCommission($this->serialize($commissionData));
        }

        return $this;
    }

    /**
     * @param $totalItem
     * @param $tier
     *
     * @return float|
     */
    public function getTotalCommission($item, $totalItem, $tier, $isCalculateTax)
    {
        if ($isCalculateTax) {
            $totalItem += $item->getBaseTaxAmount();
        }

        $commission = $tier['value' . $this->fieldSuffix];
        if ((int)$tier['type' . $this->fieldSuffix] === Arraycommission::TYPE_SALE_PERCENT) {
            $commission = $this->priceCurrency->round(($commission * $totalItem) / 100);
        }

        return $commission;
    }

    /**
     * @param $item
     * @param $totalOnCart
     * @param $tier
     * @param $campaign
     * @param $totalCommission
     * @param $isCalculateTax
     * @param $lastItem
     *
     * @return void
     */
    public function calculateTierItem(
        $item,
        $totalOnCart,
        $tier,
        $campaign,
        &$totalCommission,
        $isCalculateTax,
        &$lastItem
    ) {
        if ($totalOnCart && $tier) {
            if ($this->_address->getBaseAffiliateDiscountAmount() == 0 && $this->hasFirstOrder()) {
                $totalItem  = $item->getPrice() - $item->getDiscountAmount();
                $item->setBaseAffiliateDiscountAmount(0);
                $item->setAffiliateDiscountAmount(0);
            } else {
                $totalItem  = $this->getItemTotalForDiscount($item, $isCalculateTax);
            }
            $commissionItem = $this->getTotalCommission($item, $totalItem, $tier, $isCalculateTax);
            $commissionItem = $this->round($commissionItem, 'commission');
            $this->setTierData($item, $campaign, $commissionItem);
            $lastItem        = $item;
            $totalCommission += (float)$commissionItem;
        }
    }

    /**
     * @param $object
     * @param $campaign
     * @param $commissionItem
     * @param string $field
     */
    public function setTierData($object, $campaign, $commissionItem, $field = 'affiliate_commission')
    {
        $campaignId          = $campaign->getCampaignId();
        $tierId              = $this->tierId;
        $affiliateCommission = [];
        if ($object->getData($field)) {
            $affiliateCommission = $this->unserialize($object->getData($field));
        }
        if (!isset($affiliateCommission[$campaignId])) {
            $affiliateCommission[$campaignId] = [];
        }
        if (!isset($affiliateCommission[$campaignId][$tierId])) {
            $affiliateCommission[$campaignId][$tierId] = 0;
        }

        $affiliateCommission[$campaignId][$tierId] += (float)$commissionItem;
        $object->setData($field, $this->serialize($affiliateCommission));
    }

    /**
     * @param $account
     *
     * @return array
     */
    public function getAffiliateTree($account)
    {
        $tree       = explode('/', $account->getTree());
        $treeResult = [];
        $tier       = 1;
        while (count($tree)) {
            $treeResult['tier_' . ($tier++)] = array_pop($tree);
        }

        return $treeResult;
    }
}
