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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2024-2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Plugin\Model\Product;

class PriceAdmin
{
    /**
     * @var \Bss\CustomPricing\Helper\CustomerRule
     */
    protected $helperRule;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\CustomPricing\Helper\GetFinalProductPriceCustom
     */
    protected $getFinalProductPriceCustom;

    /**
     * @param \Bss\CustomPricing\Helper\CustomerRule $helperRule
     * @param \Bss\CustomPricing\Helper\Data $helperData
     * @param \Bss\CustomPricing\Helper\GetFinalProductPriceCustom $getFinalProductPriceCustom
     */
    public function __construct(
        \Bss\CustomPricing\Helper\CustomerRule $helperRule,
        \Bss\CustomPricing\Helper\Data $helperData,
        \Bss\CustomPricing\Helper\GetFinalProductPriceCustom $getFinalProductPriceCustom
    ) {
        $this->helperRule = $helperRule;
        $this->helperData = $helperData;
        $this->getFinalProductPriceCustom = $getFinalProductPriceCustom;
    }

    /**
     * Modify price when clone QuoteExtension/ Order Create {later}
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $subject
     * @param float $result
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetPrice($subject, $result, $product) {
        if ($this->helperData->isEnabled() && $this->helperData->getBackendSession()) {
            $productId = $product->getId();
            $customerGroup = $this->helperData->getBackendSession()->getQECustomerGroup();
            $ruleIds = $this->getRuleIDs();
            if ($ruleIds) {
                $priceRule = $this->getFinalProductPriceCustom
                    ->getInfoPrices($ruleIds, $productId, $customerGroup);
                if ($priceRule) {
                    $product->setBssCustomPrice(true);
                    return $priceRule;
                }
            }
        }
        return $result;
    }

    /**
     * Modify price when clone QuoteExtension/ Order Create {later}
     *
     * @param \Magento\Catalog\Model\Product\Type\Price $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @param float|null $qty
     * @return mixed
     */
    public function aroundGetBasePrice($subject, callable $proceed, $product, $qty) {
        try {
            if ($this->helperData->isEnabled() && $this->helperData->getBackendSession()) {
                $ruleIds = $this->getRuleIDs();
                if ($ruleIds) {
                    $productId = $product->getId();
                    $customerGroup = $this->helperData->getBackendSession()->getQECustomerGroup();
                    $priceRule = $this->getFinalProductPriceCustom
                        ->getInfoPrices($ruleIds, $productId, $customerGroup);
                    if (!$priceRule) {
                        return $proceed($product, $qty);
                    }

                    return $this->getFinalProductPriceCustom->getFinalPriceCustom(
                        $subject,
                        $product,
                        $priceRule,
                        $qty
                    );
                }
            }
            return $proceed($product, $qty);
        } catch (\Exception $e) {
            return $proceed($product, $qty);
        }
    }

    /**
     * Get rule apply by customer id
     *
     * @return false|string
     */
    public function getRuleIDs()
    {
        try {
            $customerId = $this->helperData->getBackendSession()->getQECustomerID();
            return $this->helperRule->getSpecialRuleByCustomerId($customerId);
        } catch (\Exception $e) {
            return false;
        }
    }
}
