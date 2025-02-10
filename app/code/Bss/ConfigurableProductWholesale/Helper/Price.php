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
 * @package    Bss_ConfigurableProductWholesale
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableProductWholesale\Helper;

use Magento\Framework\App\Helper\Context;

class Price extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DEFAULT_FINAL_PRICE_CLASS = \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox::class;
    const CUSTOM_FINAL_PRICE_CLASS = \Bss\ConfigurableProductWholesale\Block\Pricing\Render\FinalPriceBox::class;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var MagentoHelper
     */
    protected $magentoHelper;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    private $currencyLocale;

    /**
     * Price constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MagentoHelper $magentoHelper
     * @param \Magento\Framework\Locale\Currency $currencyLocale
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        MagentoHelper $magentoHelper,
        \Magento\Framework\Locale\Currency $currencyLocale,
        \Bss\ConfigurableProductWholesale\Helper\Data $helper
    ) {
        $this->registry = $registry;
        $this->magentoHelper = $magentoHelper;
        $this->currencyLocale = $currencyLocale;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     *  Get price template
     *
     * @return string
     */
    public function getPriceClass()
    {
        if ($this->helper->isModuleEnabled() && $this->helper->getConfig('/general/range_price')) {
            return self::CUSTOM_FINAL_PRICE_CLASS;
        }
        return self::DEFAULT_FINAL_PRICE_CLASS;
    }

    /**
     * @param null $price
     * @return string
     * @throws \Zend_Currency_Exception
     */
    public function getFormatPrice($price = null)
    {
        $currencyCode = $this->magentoHelper->getCurrencyCode();
        return $this->currencyLocale->getCurrency($currencyCode)->toCurrency($price);
    }

    /**
     * @param null $product
     * @param null $min
     * @param null $max
     * @return array|bool
     */
    public function getRangePrice($product = null, $min = null, $max = null)
    {
        $usedProducts = $product->getTypeInstance()->getUsedProducts($product);
        if (empty($usedProducts)) {
            return [
                'finalPrice' => $product->getPrice(),
                'exclTaxFinalPrice' => $product->getPrice()
            ];
        }
        $price = [];
        $result = [];
        foreach ($usedProducts as $productChild) {
            $priceModel = $productChild->getPriceInfo()->getPrice('final_price');
            $productSku = $productChild->getSku();
            $stockStatus = $this->magentoHelper->getStockRegistry()->getProductStockStatusBySku($productSku);
            if ($stockStatus == 1) {
                $price['finalPrice'][] = $priceModel->getAmount()->getValue();
                $price['exclTaxFinalPrice'][] = $priceModel->getAmount()->getValue(['tax']);
                $tierPriceModel = $productChild->getPriceInfo()->getPrice('tier_price');
                $tierPricesList = $tierPriceModel->getTierPriceList();
                if (isset($tierPricesList) && !empty($tierPricesList)) {
                    foreach ($tierPricesList as $tierPrices) {
                        $price['finalPrice'][] = $tierPrices['price']->getValue();
                        $price['exclTaxFinalPrice'][] = $tierPrices['price']->getValue(['tax']);
                    }
                }
            }
        }

        if (!isset($price['finalPrice'])) {
            return [
                'finalPrice' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                'exclTaxFinalPrice' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(['tax'])
            ];
        }

        $result['finalPrice'] = array_unique($price['finalPrice']);
        $result['exclTaxFinalPrice'] = array_unique($price['exclTaxFinalPrice']);
        $maxFinalPrice = max($result['finalPrice']);
        $maxExclTaxFinalPrice = max($result['exclTaxFinalPrice']);
        $minFinalPrice = min($result['finalPrice']);
        $minExclTaxFinalPrice = min($result['exclTaxFinalPrice']);
        if ($max) {
            return [
                'finalPrice' => $maxFinalPrice,
                'exclTaxFinalPrice' => $maxExclTaxFinalPrice
            ];
        } elseif ($min) {
            return [
                'finalPrice' => $minFinalPrice,
                'exclTaxFinalPrice' => $minExclTaxFinalPrice
            ];
        } else {
            return false;
        }
    }

    /**
     * Get Configuration module is Enabled
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->helper->isModuleEnabled();
    }

    /**
     * Check config exclude tax price
     *
     * @return bool
     */
    public function hasExclTaxConfig()
    {
        return $this->helper->hasExclTaxConfig();
    }

    /**
     * Get Configuration by Field
     *
     * @param bool|string|int $field
     * @return bool|string|int
     */
    public function getConfig($field)
    {
        return $this->helper->getConfig($field);
    }
}
