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
 * @package    Bss_ConfigurableMatrixView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableMatrixView\Helper;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customer;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionCustomerFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Customer\Model\SessionFactory $sessionCustomerFactory
     * @param \Magento\Framework\Registry $registry
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Customer\Model\SessionFactory $sessionCustomerFactory,
        \Magento\Framework\Registry $registry,
        TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
        $this->scopeConfig = $scopeConfig;
        $this->taxConfig = $taxConfig;
        $this->taxCalculation = $taxCalculation;
        $this->sessionCustomerFactory = $sessionCustomerFactory;
        $this->registry = $registry;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Check Config Yes/No
     *
     * @param string $path
     * @return bool
     */
    public function isConfigFlag($path)
    {
        return $this->scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Config Value
     *
     * @param string $path
     * @return null|string|bool|int
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check is allowed Customer and module Enable
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isEnabled()
    {
        $active = $this->isConfigFlag('configurablematrixview/general/active');
        $customer_group = $this->getConfigValue('configurablematrixview/general/customer_group');
        $customer_groups = explode(',', $customer_group);
        if ($active && $customer_group != ''
            && (in_array(32000, $customer_groups)
                || in_array($this->sessionCustomerFactory->create()->getCustomerGroupId(), $customer_groups))) {
            return true;
        }
        return false;
    }

    /**
     * Check sort attribute by default or A-Z
     *
     * @return bool
     */
    public function isSortOption()
    {
        return $this->isConfigFlag('configurablematrixview/general/sort_option');
    }

    /**
     * Check show unit price of product child in table matrix view
     *
     * @return bool
     */
    public function canShowUnitPrice()
    {
        return $this->isConfigFlag('configurablematrixview/general/unit_price');
    }

    /**
     * Check show tier price of product child in table matrix view
     *
     * @return bool
     */
    public function canShowTierPrice()
    {
        return $this->isConfigFlag('configurablematrixview/general/tier_price');
    }

    /**
     * Check show price range
     *
     * @return bool
     */
    public function canShowPriceRange()
    {
        return $this->isConfigFlag('configurablematrixview/general/price_range');
    }

    /**
     * Check show total price
     *
     * @return bool
     */
    public function canShowTotal()
    {
        return $this->isConfigFlag('configurablematrixview/general/show_total');
    }

    /**
     * Check show stock of product child in table matrix view
     *
     * @return bool
     */
    public function canShowStock()
    {
        return $this->isConfigFlag('configurablematrixview/general/show_stock');
    }

    /**
     * Check show button increase of box qty
     *
     * @return bool
     */
    public function canShowButtonQty()
    {
        return $this->isConfigFlag('configurablematrixview/general/qty_increase');
    }

    /**
     * Check show Advanced Tier Price
     *
     * @return bool
     */
    public function calculateTierPrice()
    {
        return $this->isConfigFlag('configurablematrixview/general/tier_price_calculate');
    }

    /**
     * Check Same Tier Price Child Products
     *
     * @param int $number_childproduct
     * @param int $number_tiers
     * @param array $tierPrices
     * @return int
     */
    public function checkSameTierPrice($number_childproducts, $tierPrices)
    {
        if ((int)$number_childproducts == 0) {
            $this->sortArrayTierPrice($tierPrices[0], 'qty');
            foreach ($tierPrices as $tierPrice) {
                $this->sortArrayTierPrice($tierPrice, 'qty');
                if ($tierPrice != $tierPrices[0]) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sort Tier Price
     *
     * @param array $tierPrice
     * @param string $col
     * @param int $dir
     */
    public function sortArrayTierPrice(&$tierPrice, $col, $dir = SORT_ASC)
    {
        $function = [];
        foreach ($tierPrice as $key => $row) {
            $function[$key] = $row[$col];
        }
        array_multisort($function, $dir, $tierPrice);
    }

    /**
     * @return int
     */
    public function getRateTax($product)
    {
        $customer = $this->sessionCustomerFactory->create()->getCustomer();
        $store = $product->getStore();
        $taxClassId = $product->getTaxClassId();
        $rate_request = $this->taxCalculation->getRateRequest(null, null, null, $store, $customer->getId());
        $percent = $this->taxCalculation->getRate($rate_request->setProductClassId($taxClassId));
        return $percent;
    }

    /**
     * @return int
     */
    public function getPriceDisplayType()
    {
        return $this->taxConfig->getPriceDisplayType();
    }

    /**
     * Check version magento >= 2.1.6
     *
     * @return bool
     */
    public function isMagentoVersion()
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.1.6') >= 0) {
            return true;
        }
        return false;
    }

    /**
     * Check version magento >= 2.2.0
     *
     * @return bool
     */
    public function isMagentoVersion22()
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.2.0') >= 0) {
            return true;
        }
        return false;
    }

    /**
     * Check version magento >= 2.3.0
     *
     * @return bool
     */
    public function isMagentoVersion23()
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.3.0') >= 0) {
            return true;
        }
        return false;
    }

    /**
     * Get Add to Cart template
     *
     * @param string $name
     * @return string
     */
    public function getAddtocartButtonTemplate($name)
    {
        $product = $this->registry->registry('product');
        if ($product && $product->getConfigurableMatrixView() && $this->isEnabled()) {
            return $name;
        }
        return 'Magento_Catalog::product/view/addtocart.phtml';
    }

    /**
     * Get Product View Form template
     *
     * @param string $name
     * @return string
     */
    public function getFormTemplate($name)
    {
        $product = $this->registry->registry('product');
        if ($product && $product->getConfigurableMatrixView() && $this->isEnabled()) {
            return $name;
        }
        return 'Magento_Catalog::product/view/form.phtml';
    }

    /**
     * Compare version
     *
     * @return bool
     */
    public function isNewAddtocartTemplate()
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.2.5') >= 0 || $version == '2.1.15') {
            return true;
        }
        return false;
    }

    /**
     * Check flush configurable product page cache
     *
     * @return bool
     */
    public function canFlushConfigurableProduct()
    {
        return $this->isConfigFlag('configurablematrixview/general/flush_product_page_cache');
    }
}
