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
namespace Bss\RewardPoint\Plugin\Model\Quote;

class Address
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
     * Validate minimum amount address with reward point.
     *
     * @param \Magento\Quote\Model\Quote\Address $subject
     * @param bool $result
     * @return bool
     */
    public function afterValidateMinimumAmount(\Magento\Quote\Model\Quote\Address $subject, $result)
    {
        if (!$result || !$subject->getRwpAmount()) { // Skip check reward point
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

        $validateEnabled = $this->scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$validateEnabled) {
            return true;
        }

        if (!$subject->getQuote()->getIsVirtual() xor $subject->getAddressType() == $subject::TYPE_SHIPPING) {
            return true;
        }

        $amount = $this->scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $taxInclude = $this->scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $taxes = $taxInclude
            ? $subject->getBaseTaxAmount() + $subject->getBaseDiscountTaxCompensationAmount()
            : 0;

        return ($subject->getBaseSubtotalWithDiscount() + $taxes) - (int)$subject->getRwpAmount()  > $amount - 0.0001; // Check reward point
    }
}
