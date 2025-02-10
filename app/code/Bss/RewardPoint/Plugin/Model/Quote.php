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
 * @copyright  Copyright (c) 2019-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Plugin\Model;

class Quote
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Construct.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Validate minimum amount with reward point.
     *
     * @param \Magento\Quote\Model\Quote $subject
     * @param bool $result
     * @param bool $multishipping
     * @return bool
     */
    public function afterValidateMinimumAmount(\Magento\Quote\Model\Quote $subject, $result, $multishipping = false)
    {
        if (!$result || !$subject->getRwpAmount() || !$multishipping) { // Skip check reward point
            return $result;
        }

        $storeId = $subject->getStoreId();
        $includeDiscount = $this->scopeConfig->getValue(
            'sales/minimum_order/include_discount_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$includeDiscount) { // Skip check reward point
            return $result;
        }

        $minOrderActive = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$minOrderActive) {
            return true;
        }

        $minOrderMulti = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/multi_address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $minAmount = $this->scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $taxInclude = $this->scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $addresses = $subject->getAllAddresses();

        if (!$multishipping) {
            foreach ($addresses as $address) {
                if (!$address->validateMinimumAmount()) {
                    return false;
                }
            }
            return true;
        }

        if (!$minOrderMulti) {
            foreach ($addresses as $address) {
                $taxes = $taxInclude
                    ? $address->getBaseTaxAmount() + $address->getBaseDiscountTaxCompensationAmount()
                    : 0;
                foreach ($address->getQuote()->getItemsCollection() as $item) {
                    /** @var \Magento\Quote\Model\Quote\Item $item */
                    $amount = $item->getBaseRowTotal() - $item->getBaseDiscountAmount() - (int)$subject->getRwpAmount() + $taxes; // Check reward point
                    if ($amount < $minAmount) {
                        return false;
                    }
                }
            }
        } else {
            $baseTotal = 0;
            foreach ($addresses as $address) {
                $taxes = $taxInclude
                    ? $address->getBaseTaxAmount() + $address->getBaseDiscountTaxCompensationAmount()
                    : 0;
                $baseTotal += $address->getBaseSubtotalWithDiscount() - (int)$address->getRwpAmount() + $taxes; // Check reward point
            }

            if ($baseTotal < $minAmount) {
                return false;
            }
        }
        return true;
    }
}
