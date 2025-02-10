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
 * @category  BSS
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableProductWholesale\Helper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIGURABLE_PRODUCT_TYPE = 'configurable';

    /**
     * @var array
     */
    protected $arrProductTierPrice = [];

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Filter\LocalizedToNormalized
     */
    private $localFilter;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var MagentoHelper
     */
    protected $magentoHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Filter\LocalizedToNormalized $localFilter
     * @param \Magento\Framework\Locale\Currency $currencyLocale
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param MagentoHelper $magentoHelper
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Filter\LocalizedToNormalized $localFilter,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        MagentoHelper $magentoHelper,
        \Magento\Customer\Model\SessionFactory $session
    ) {
        parent::__construct($context);
        $this->localeFormat = $localeFormat;
        $this->productMetadata = $productMetadata;
        $this->magentoHelper = $magentoHelper;
        $this->localFilter = $localFilter;
        $this->localeResolver = $localeResolver;
        $this->session = $session;
    }

    /**
     * @param $string
     * @return bool|false|string
     */
    public function serialize($string)
    {
        return $this->magentoHelper->serialize($string);
    }

    /**
     * @param $string
     * @return bool|false|string
     */
    public function unserialize($string)
    {
        return $this->magentoHelper->unserialize($string);
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return MagentoHelper
     */
    public function getMagentoHelper()
    {
        return $this->magentoHelper;
    }

    /**
     * @return \Magento\Framework\Filter\LocalizedToNormalized
     */
    public function getLocalFilter()
    {
        return $this->localFilter;
    }

    /**
     * @return \Magento\Framework\Locale\ResolverInterface
     */
    public function getLocaleResolver()
    {
        return $this->localeResolver;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * Get Configuration by Field
     *
     * @param $field
     * @return bool|string|int
     */
    public function getConfig($field)
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $result = $this->scopeConfig->getValue(
            'configurableproductwholesale' . $field,
            $scope
        );

        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * Get Show Out Of Stock Config Default
     *
     * @return string|int|bool
     */
    public function getDisplayOutOfStock()
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            'cataloginventory/options/show_out_of_stock',
            $scope
        );
    }

    /**
     * @return bool|false|string
     */
    public function getFomatPrice()
    {
        $config = $this->localeFormat->getPriceFormat();
        return $this->serialize($config);
    }

    /**
     * @param string|null $field
     * @return bool
     */
    public function checkCustomer($field = null)
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $customerConfig = $this->scopeConfig->getValue(
            'configurableproductwholesale/general/' . $field,
            $scope
        );
        if ($customerConfig != '') {
            $customerConfigArr = explode(',', $customerConfig);
            if ($this->magentoHelper->getCustomerSession()->create()->isLoggedIn()) {
                $customerGroupId = $this->magentoHelper->getCustomerSession()->create()->getCustomer()->getGroupId();
                if (in_array($customerGroupId, $customerConfigArr)) {
                    return true;
                }
            } else {
                if (in_array(0, $customerConfigArr)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product|null $product
     * @return bool
     * @deprecated Use: ver 1.4.0
     */
    public function checkTierPrice($product = null)
    {
        $storeId = $this->magentoHelper->getStoreId();
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($storeId, $product);
        $usedProducts = $productTypeInstance->getUsedProducts($product);
        $check = [];
        $count = 0;
        $apply = true;
        $countList = 0;
        $childPrice = [];
        foreach ($usedProducts as $child) {
            $childPrice[] = $child->getPrice();
            $tierPriceModel = $child->getPriceInfo()->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList() ? $tierPriceModel->getTierPriceList() : [];
            if (!empty($tierPricesList)) {
                $tierPrice = [];
                foreach ($tierPricesList as $key => $price) {
                    if (isset($price['website_id'])) {
                        $tierPrice[$key]['website_id'] = $price['website_id'];
                    }
                    $tierPrice[$key]['all_groups'] = $price['all_groups'];
                    $tierPrice[$key]['cust_group'] = $price['cust_group'];
                    $tierPrice[$key]['price_qty'] = $price['price_qty'];
                    $tierPrice[$key]['website_price'] = $price['website_price'];
                    $tierPrice[$key]['percentage_value'] = $price['percentage_value'];
                }
                if ($count == 0) {
                    $check = $tierPrice;
                    $countList = count($check);
                }
                if (!(is_array($check)
                    && is_array($tierPrice)
                    && $countList == count($tierPrice)
                    && $this->comparePrice($tierPrice, $check, $countList))) {
                    $apply = false;
                    break;
                }
            } else {
                $apply = false;
                break;
            }
            $count++;
        }
        if (count(array_count_values($childPrice)) > 1) {
            $apply = false;
        }
        return $apply;
    }

    /**
     * @param $tierPrices
     * @param $check
     * @param $countList
     * @return bool
     * @deprecated Use: ver 1.4.0
     */
    private function comparePrice($tierPrices, $check, $countList)
    {
        $sameTierPrice = false;
        $count = 0;
        foreach ($tierPrices as $key => $tierPrice) {
            for ($key = 0; $key < $countList; $key ++) {
                if ($tierPrice == $check[$key]) {
                    $sameTierPrice = true;
                    $count++;
                    break;
                }
                if ($key+1 == $countList) {
                    $sameTierPrice = false;
                }
            }
            if (!$sameTierPrice) {
                break;
            }
        }
        return $sameTierPrice;
    }

    /**
     * @param array $tierPricesList
     * @return int
     */
    private function countTierPrice($tierPricesList)
    {
        return count($tierPricesList);
    }

    /**
     * Set price for product
     *
     * @param $item
     */
    public function setPriceForItem($item)
    {
        if (!isset($item)) {
            return;
        }
        $product = $item->getProduct();

        foreach ($item->getQuote()->getAllVisibleItems() as $quoteItem) {
            $productId = $quoteItem->getProduct()->getId();
            $productType = $quoteItem->getProduct()->getTypeId();

            if ($productType != ConfigurableType::TYPE_CODE || $product->getId() != $productId) {
                continue;
            }

            $qty = $this->getTotalQty($item, $quoteItem->getChildren()[0]->getProductId(), $productId);
            $finalPrice = $quoteItem->getProduct()->getFinalPrice($qty);

            $quoteItem->setCustomPrice($finalPrice);
            $quoteItem->setOriginalCustomPrice($finalPrice);
            $quoteItem->getProduct()->setIsSuperMode(true);
        }
    }

    /**
     * Get array product child id the same tier price.
     *
     * @param $data
     * @return array
     */
    public function getArrayTierPrice($data, $proParentId)
    {
        if (isset($this->arrProductTierPrice[$proParentId])) {
            return $this->arrProductTierPrice[$proParentId];
        }

        $arr = [];
        foreach ($data as $child) {
            $tierPriceModel = $child->getPriceInfo()->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList() ? $tierPriceModel->getTierPriceList() : [];
            if (!empty($tierPricesList)) {
                $tierPrice = [];
                foreach ($tierPricesList as $key => $price) {
                    if (isset($price['website_id'])) {
                        $tierPrice[$key]['website_id'] = $price['website_id'];
                    }
                    $tierPrice[$key]['all_groups'] = $price['all_groups'];
                    $tierPrice[$key]['cust_group'] = $price['cust_group'];
                    $tierPrice[$key]['price_qty'] = $price['price_qty'];
                    $tierPrice[$key]['website_price'] = $price['website_price'];
                    $tierPrice[$key]['percentage_value'] = $price['percentage_value'];
                }
                $arr[$child->getId()] = $tierPrice;
            } else {
                $arr[$child->getId()] = [];
            }
        }
        $this->arrProductTierPrice[$proParentId] = $arr;

        return $this->arrProductTierPrice[$proParentId];
    }

    /**
     * Get total quantity product same tier price
     *
     * @param $item
     * @return bool|int
     */
    public function getTotalQty($item, $proId, $proParentId)
    {
        $totalsQty = 0;
        if (!$proId || !isset($item) || !$proParentId) {
            return false;
        }

        $product = $item->getProduct();

        $storeId = $this->magentoHelper->getStoreId();
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($storeId, $product);
        $data = $productTypeInstance->getUsedProducts($product);
        $arr = $this->getArrayTierPrice($data, $proParentId);

        $check = [];
        foreach ($arr as $productId1 => $tierPrice1) {
            foreach ($arr as $productId2 => $tierPrice2) {
                if ($productId1 && $productId2 && $tierPrice1 == $tierPrice2) {
                    $check[$productId1]['checkAdvanceTierPrice'][$productId1] = $productId1;
                    $check[$productId1]['checkAdvanceTierPrice'][$productId2] = $productId2;
                }
            }
        }

        if (isset($check[$proId])) {
            foreach ($item->getQuote()->getAllVisibleItems() as $quoteItem) {
                $productId = $quoteItem->getProduct()->getId();
                $productType = $quoteItem->getProduct()->getTypeId();
                if ($productType != 'configurable' || $product->getId() != $productId) {
                    continue;
                }
                if (isset($check[$proId]['checkAdvanceTierPrice'][$quoteItem->getChildren()[0]->getProductId()])) {
                    $totalsQty += $quoteItem->getQty();
                }

            }
        }

        if ($totalsQty > 0) {
            return $totalsQty;
        } else {
            return false;
        }
    }

    /**
     * @param null $product
     * @return string
     */
    public function getJsonSystemConfig($product = null)
    {
        if (!$product) {
            return false;
        }
        $showSubTotal = !$this->checkCustomer('hide_price');
        $showExclTaxSubTotal = $this->hasExclTaxConfig() && !$this->checkCustomer('hide_price');
        $tierPriceAdvanced = $this->getConfig('/general/tier_price_advanced');
        $stockNumber = $this->getConfig('/general/stock_number');
        $ajaxConfig = $this->isAjax($product);
        $hidePrice = $this->checkCustomer('hide_price');
        $enableSDCP = $this->_moduleManager->isEnabled('Bss_Simpledetailconfigurable');
        $config = [
            'stockNumber' => $stockNumber,
            'tierPriceAdvanced' => $tierPriceAdvanced,
            'showSubTotal' => $showSubTotal,
            'showExclTaxSubTotal' => $showExclTaxSubTotal,
            'textColor' => $this->getConfig('/design/header_text_color'),
            'backGround' => $this->getConfig('/design/header_background_color'),
            'ajaxLoad' => $ajaxConfig,
            'hidePrice' => $hidePrice,
            'sorting' => $this->getConfig('/general/sorting'),
            'isEnableMobile' => $this->getConfig('/display/mobile_active'),
            'isEnableTablet' => $this->getConfig('/display/tab_active'),
            'isEnableCustomUrl' => $this->customUrl(),
            'enableSDCP' => $enableSDCP
        ];

        if ($product->getPreselectData()) {
            $config['preselect'] = $product->getPreselectData();
        }

        if ($this->getConfig('/display/mobile_active')) {
            $config['mobile'] = $this->getDisplayAttributeAdvanced('mobile_attr', '/display/mobile_active');
        }
        if ($this->getConfig('/display/tab_active')) {
            $config['tablet'] = $this->getDisplayAttributeAdvanced('tab_attr', '/display/tab_active');
        }
        $config['desktop'] = $this->getDisplayAttributeAdvanced('show_attr', '/general/active');
        $config['ajaxLoadUrl'] = $this->_urlBuilder->getUrl('configurablewholesale/index/rendertable');
        return $this->serialize($config);
    }

    /**
     * @return bool|mixed
     */
    public function customUrl()
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            'Bss_Commerce/SDCP_advanced/url',
            $scope
        );
    }

    /**
     * @param $product
     * @return mixed
     */
    public function isEnableSdcp($product)
    {
        return $product->getIsEnabledSdcp();
    }

    /**
     * @param string $field
     * @param string|null $active
     * @return array|bool
     */
    public function getDisplayAttributeAdvanced($field, $active = null)
    {
        if (!$this->getConfig($active)) {
            return false;
        }
        $respon = [];
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($active == '/general/active') {
            $result = $this->scopeConfig->getValue(
                'configurableproductwholesale/general/' . $field,
                $scope
            );
        } else {
            $result = $this->scopeConfig->getValue(
                'configurableproductwholesale/display/' . $field,
                $scope
            );
        }

        if ($result) {
            $resultArr = explode(',', $result);
            foreach ($resultArr as $value) {
                $respon[$value] = $value;
            }
            return $respon;
        }
        return false;
    }

    /**
     *  Compare magento version
     *
     * @param string $version
     * @return bool
     */
    public function validateMagentoVersion($version)
    {
        $dataVersion = $this->productMetadata->getVersion();
        if (version_compare($dataVersion, $version) >= 0) {
            return true;
        }
        return false;
    }

    /**
     * Check config exclude tax price
     *
     * @return bool
     */
    public function hasExclTaxConfig()
    {
        if ($this->isModuleEnabled() && $this->magentoHelper->getTaxHelper()->displayBothPrices()
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool|int|string
     */
    public function isAjax($product)
    {
        $ajaxConfig = $product->getBssCpwAjax();
        if ($ajaxConfig == 2 || $ajaxConfig === null) {
            $ajaxConfig = $this->getConfig('/general/ajax_load');
        }
        return $ajaxConfig;
    }

    /**
     * Whether a module is enabled in the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    public function hasModuleEnabled($moduleName)
    {
        return $this->magentoHelper->hasModuleEnabled($moduleName);
    }

    /**
     * Get Configuration module is Enabled
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $result = $this->scopeConfig->getValue(
            'configurableproductwholesale/general/active',
            $scope
        );

        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * Convert to text from value config Pre Order of product
     * @param int|null $textPreOrder
     * @return \Magento\Framework\Phrase
     */
    public function convertTextPreOrder($textPreOrder)
    {
        if ($textPreOrder) {
            if ($textPreOrder == 2) {
                return __('When Become Out Of Stock');
            }
            return __('Yes');
        }
        return __('No');
    }
}
