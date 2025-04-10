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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Helper;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Filter\TranslitUrl;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Shopbybrand\Model\Brand;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Model\Category;
use Mageplaza\Shopbybrand\Model\CategoryFactory;
use Mageplaza\Shopbybrand\Model\Config\Source\ShowBrandInfo;

/**
 * Class Data
 * @package Mageplaza\Shopbybrand\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'shopbybrand';
    /**
     * Image size default
     */
    const IMAGE_SIZE = '135x135';
    /**
     * General configuration path
     */
    const GENERAL_CONFIGURATION = 'shopbybrand/general';
    /**
     * Brand sidebar configuration path
     */
    const BRAND_SIDEBAR_CONFIGURATION = 'sidebar';
    /**
     * Brand page configuration path
     */
    const BRAND_CONFIGURATION = 'brandpage';
    /**
     * Search brand configuration path
     */
    const BRAND_DETAIL_CONFIGURATION = 'brandview';
    /**
     * Brand media path
     */
    const BRAND_MEDIA_PATH = 'mageplaza/brand';
    /**
     * Default route name
     */
    const DEFAULT_ROUTE = 'brand';
    /**
     * Get Brand by
     */
    const CATEGORY = 'category';
    /**
     * Get Brand by
     */
    const BRAND_FIRST_CHAR = 'char';
    /**
     * Brand Qty Index
     */
    const MAGEPLAZA_BRAND_QTY = 'mageplaza_brand_qty';
    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var FilterManager
     */
    protected $_filter;

    /**
     * @var TranslitUrl
     */
    protected $translitUrl;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @type string
     */
    protected $_char = '';

    /**
     * @type BrandFactory
     */
    protected $_brandFactory;

    /**
     * @type
     */
    protected $_brandCollection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Attribute
     */
    protected $_attribute;

    /**
     * @var
     */
    protected $brandCollectionCache;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var AdapterInterface
     */
    private $connection;
    /**
     * @var string
     */
    private $brandQtyIndexTable;
    /**
     * @var array
     */
    private $allQty;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param TranslitUrl $translitUrl
     * @param FilterManager $filter
     * @param CategoryFactory $categoryFactory
     * @param BrandFactory $brandFactory
     * @param FormatInterface $localeFormat
     * @param Registry $registry
     * @param Attribute $attribute
     * @param ProductRepository $productRepository
     * @param FilterProvider $filterProvider
     * @param ProductFactory $productFactory
     * @param CollectionFactory $productCollectionFactory
     * @param ResourceConnection $resource
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        TranslitUrl $translitUrl,
        FilterManager $filter,
        CategoryFactory $categoryFactory,
        BrandFactory $brandFactory,
        FormatInterface $localeFormat,
        Registry $registry,
        Attribute $attribute,
        ProductRepository $productRepository,
        FilterProvider $filterProvider,
        ProductFactory $productFactory,
        CollectionFactory $productCollectionFactory,
        ResourceConnection $resource,
        IndexerRegistry $indexerRegistry
    ) {
        $this->_filter                   = $filter;
        $this->translitUrl               = $translitUrl;
        $this->categoryFactory           = $categoryFactory;
        $this->_brandFactory             = $brandFactory;
        $this->localeFormat              = $localeFormat;
        $this->registry                  = $registry;
        $this->_attribute                = $attribute;
        $this->filterProvider            = $filterProvider;
        $this->productRepository         = $productRepository;
        $this->productFactory            = $productFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->resource                  = $resource;
        $this->connection                = $this->resource->getConnection();
        $this->indexerRegistry           = $indexerRegistry;
        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param int $position
     *
     * @return bool
     */
    public function canShowBrandLink($position)
    {
        if (!$this->isEnabled()) {
            return false;
        }
        $positionConfig = explode(',', $this->getConfigGeneral('show_position'));

        return in_array($position, $positionConfig, true);
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isShowBrandsWithoutProducts($store = null)
    {
        return $this->getBrandConfig('show_brands_without_products', $store);
    }

    /**
     * @param null $brand
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBrandUrl($brand = null)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $key     = ($brand === null) ? '' : '/' . $this->processKey($brand);

        return $baseUrl . $this->getRoute() . $key . $this->getUrlSuffix();
    }

    /**
     * @param Brand $brand
     *
     * @return string
     */
    public function processKey($brand)
    {
        if (!$brand) {
            return '';
        }
        $str = $brand->getUrlKey() ?: $brand->getDefaultValue();

        return $this->formatUrlKey($str);
    }

    /**
     * Format URL key from name or defined key
     *
     * @param string $str
     *
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->_filter->translitUrl($str);
    }

    /**
     * @param Brand brand
     *
     * @return string
     */
    public function getBrandImageUrl($brand)
    {
        if ($brand->getImage()) {
            $image = $brand->getImage();
        } elseif ($brand->getSwatchType() == Swatch::SWATCH_TYPE_VISUAL_IMAGE) {
            $image = Media::SWATCH_MEDIA_PATH . $brand->getSwatchValue();
        } elseif ($this->getBrandDetailConfig('default_image')) {
            $image = self::BRAND_MEDIA_PATH . '/' . $this->getBrandDetailConfig('default_image');
        } else {
            return ObjectManager::getInstance()
                ->get(Image::class)
                ->getDefaultPlaceholderUrl('small_image');
        }

        try {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $image;
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Get Brand Title
     *
     * @return string
     */
    public function getBrandTitle()
    {
        return $this->getConfigGeneral('link_title') ?: __('Brands');
    }

    /**
     * Retrieve Brand description
     *
     * @param Brand $brand
     * @param bool|false $short
     *
     * @return mixed
     */
    public function getBrandDescription($brand, $short = false)
    {
        if ($short) {
            $content = $brand->getShortDescription() ?: '';
        } else {
            $content = $brand->getDescription() ?: '';
        }

        try {
            return $this->filterProvider->getBlockFilter()->filter($content);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Retrieve Attribute code for Brand
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getAttributeCode($store = null)
    {
        return $this->getConfigGeneral('attribute', $store);
    }

    /**
     * @param null $store
     *
     * @return array|mixed
     */
    public function getShowBrandInfoInAdmin($store = null)
    {
        return $this->getConfigGeneral('show_brand_info_in_admin', $store);
    }

    /**
     * Retrieve route name for brand.
     * If empty, default 'brands' will be used
     *
     * @return string
     */
    public function getRoute()
    {
        $route = $this->getConfigGeneral('route', $this->getStoreId()) ?: self::DEFAULT_ROUTE;

        return $this->formatUrlKey($route);
    }

    /**
     * Retrieve category rewrite suffix for store
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getUrlSuffix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getBrandConfig($code = '', $store = null)
    {
        $code = $code ? self::BRAND_CONFIGURATION . '/' . $code : self::BRAND_CONFIGURATION;

        return $this->getModuleConfig($code, $store);
    }

    public function getNumberPosition()
    {
        return $this->getConfigGeneral('number_position');
    }

    /**
     * @param string $group
     * @param null $store
     *
     * @return array
     */
    public function getImageSize($group = '', $store = null)
    {
        $imageSize = $this->getBrandConfig($group . 'image_size', $store) ?: self::IMAGE_SIZE;

        return explode('x', $imageSize);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getFeatureConfig($code = '', $store = null)
    {
        $code = $code ? 'feature' . '/' . $code : 'feature';

        return $this->getBrandConfig($code, $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function enableFeature($store = null)
    {
        return $this->getFeatureConfig('enable', $store);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getSearchConfig($code = '', $store = null)
    {
        $code = $code ? 'search' . '/' . $code : 'search';

        return $this->getBrandConfig($code, $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function enableSearch($store = null)
    {
        return $this->getSearchConfig('enable', $store);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getBrandDetailConfig($code = '', $store = null)
    {
        $code = $code ? self::BRAND_DETAIL_CONFIGURATION . '/' . $code : self::BRAND_DETAIL_CONFIGURATION;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getSidebarConfig($code = '', $store = null)
    {
        $code = $code ? self::BRAND_SIDEBAR_CONFIGURATION . '/' . $code : self::BRAND_SIDEBAR_CONFIGURATION;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @return array
     */
    public function getAllBrandsAttributeCode()
    {
        $stores           = $this->storeManager->getStores();
        $attributeCodes   = [];
        $attributeCodes[] = $this->getAttributeCode();
        foreach ($stores as $store) {
            $attributeCodes[] = $this->getAttributeCode($store->getId());
        }
        $attributeCodes = array_unique($attributeCodes);

        return $attributeCodes;
    }

    /**
     * generate url_key for brand category
     *
     * @param string $name
     * @param int $count
     *
     * @return string
     */
    public function generateUrlKey($name, $count)
    {
        $name = $this->removeUnicode($name);
        $text = $this->translitUrl->filter($name);
        if ((int) $count === 0) {
            $count = '';
        }
        if (empty($text)) {
            return 'n-a' . $count;
        }

        return $text . $count;
    }

    /**
     * replace vietnamese characters to english characters
     *
     * @param string $str
     *
     * @return mixed|string
     */
    public function removeUnicode($str)
    {
        $str = mb_strtolower(trim($str));
        $str = preg_replace('/([àáạảãâầấậẩẫăằắặẳẵ])/u', 'a', $str);
        $str = preg_replace('/([èéẹẻẽêềếệểễ])/u', 'e', $str);
        $str = preg_replace('/([ìíịỉĩ])/u', 'i', $str);
        $str = preg_replace('/([òóọỏõôồốộổỗơờớợởỡ])/u', 'o', $str);
        $str = preg_replace('/([ùúụủũưừứựửữ])/u', 'u', $str);
        $str = preg_replace('/([ỳýỵỷỹ])/u', 'y', $str);
        $str = preg_replace('/(đ)/u', 'd', $str);

        return $str;
    }

    /**
     * @param null $cat
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCatUrl($cat = null)
    {
        $baseUrl    = $this->storeManager->getStore()->getBaseUrl();
        $brandRoute = $this->getRoute();
        $key        = ($cat === null) ? '' : '/' . $this->processKey($cat);

        return $baseUrl . $brandRoute . '/' . self::CATEGORY . $key . $this->getUrlSuffix();
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = 0;
        }

        return $storeId;
    }

    /**
     * @param string $routePath
     * @param int $routeSize
     *
     * @return bool
     */
    public function isBrandRoute($routePath, $routeSize)
    {
        if ($routeSize > 3) {
            return false;
        }

        $urlSuffix  = $this->getUrlSuffix();
        $brandRoute = $this->getRoute();
        if ($urlSuffix) {
            $brandSuffix = strpos($brandRoute, $urlSuffix);
            if ($brandSuffix) {
                $brandRoute = substr($brandRoute, 0, $brandSuffix);
            }
        }

        return ($routePath[0] === $brandRoute);
    }

    /**
     * @param string $urlKey
     *
     * @return mixed|null
     */
    public function getCategoryByUrlKey($urlKey)
    {
        /** @var Category $cat */
        $cat = $this->categoryFactory->create()->load($urlKey, 'url_key');
        if ($cat) {
            return $cat->getId();
        }

        return null;
    }

    /**
     * @param null $type
     * @param null $ids
     * @param null $storeId
     *
     * @return Attribute\Option\Collection
     */
    public function getBrandList($type = null, $ids = null, $storeId = null)
    {
        /** @var Brand $brands */
        $brands = $this->_brandFactory->create();
        switch ($type) {
            //Get Brand List by Category
            case self::CATEGORY:
                $list = $brands->getBrandCollection(null, ['main_table.option_id' => ['in' => $ids]]);
                break;
            //Get Brand List Filtered by Brand First Char
            case self::BRAND_FIRST_CHAR:
                if ($ids) {
                    $list = $brands->getBrandCollection($storeId, ['main_table.option_id' => ['in' => $ids]]);
                } else {
                    $list = $brands->getBrandCollection($storeId);
                }
                break;
            default:
                //Get Brand List
                if ($ids) {
                    $this->brandCollectionCache
                        = $brands->getBrandCollection($storeId, ['main_table.option_id' => ['in' => $ids]]);
                } else {
                    $this->brandCollectionCache = $brands->getBrandCollection($storeId);
                }
                $list = $this->brandCollectionCache;
        }

        return $list;
    }

    /**
     * If $key is empty, checks whether there's any data in the object
     *
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasData($key = '')
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->_data);
        }

        return array_key_exists($key, $this->_data);
    }

    /**
     * Get condition for sql
     *
     * @param string $char
     *
     * @return string
     */
    public function checkCharacter($char)
    {
        $specialChar = [
            '!',
            '"',
            '#',
            '$',
            '&',
            '(',
            ')',
            '*',
            '+',
            ',',
            '-',
            '.',
            '/',
            ':',
            ';',
            '<',
            '=',
            '>',
            '?',
            '@',
            '[',
            ']',
            '^',
            '_',
            '`',
            '{',
            '|',
            '}',
            '~'
        ];
        if (in_array($char, $specialChar, true)) {
            $sqlCond = 'IF(tsv.value_id > 0, tsv.value, tdv.value) LIKE ' . "'" . $char . "%'";
        } elseif ($char === "'") {
            $sqlCond = 'IF(tsv.value_id > 0, tsv.value, tdv.value) LIKE ' . '"' . $char . '%"';
        } elseif ($char === "%") {
            $sqlCond = 'IF(tsv.value_id > 0, tsv.value, tdv.value) REGEXP BINARY ' . "'^" . $char . "'";
        } else {
            $sqlCond = 'IF(tsv.value_id > 0, tsv.value, tdv.value) LIKE ' . '"' . $char . '%"';
        }

        return $sqlCond;
    }

    /**
     * @return AbstractCollection
     */
    public function getCategoryList()
    {
        return $this->categoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', '1')
            ->addFieldToFilter(['store_ids', 'store_ids'], [
                ['finset' => $this->getStoreId()],
                ['finset' => 0]
            ]);
    }

    /**
     * @param Brand $brand
     *
     * @return string
     */
    public function getFilterClass($brand)
    {
        //vietnamese unikey format
        if ($this->getBrandConfig('brand_filter/encode_key')) {
            $firstChar = mb_substr($brand->getValue(), 0, 1, $this->getBrandConfig('brand_filter/encode_key'));
        } else {
            $firstChar = mb_substr($brand->getValue(), 0, 1, 'UTF-8');
        }

        return is_numeric($firstChar) ? 'num' . $firstChar : ucfirst($firstChar);
    }

    /**
     * @param string $optionId
     *
     * @return string
     */
    public function getCatFilterClass($optionId)
    {
        $catName = [];
        $sql     = 'brand_cat_tbl.option_id IN (' . $optionId . ')';
        $group   = 'main_table.cat_id';

        $collection = $this->categoryFactory->create()->getCategoryCollection($sql, $group);
        foreach ($collection as $item) {
            $str       = str_replace([' ', '*', '/', '\\'], '_', $item->getName());
            $catName[] = 'cat' . $str;
        }

        return implode(' ', $catName);
    }

    /**
     * Is show quick view near Brand name
     *
     * @return mixed
     */
    public function showQuickView()
    {
        return $this->getBrandConfig('show_quick_view');
    }

    /**
     * @return string
     */
    public function getQuickViewUrl()
    {
        return $this->_getUrl('');
    }

    /**
     * @param null $brand
     *
     * @return string
     */
    public function getQuickview($brand = null)
    {
        $key = ($brand === null) ? '' : '/' . $this->processKey($brand);

        return $this->getRoute() . $key . $this->getUrlSuffix();
    }

    /**
     * @param Product $product
     *
     * @return array|string|null
     */
    public function getBrandTextFromProduct(Product $product = null)
    {
        $currentProduct = $product ?: $this->getCurrentProduct();
        if (!$currentProduct) {
            return null;
        }

        $attCode = $this->getAttributeCode();

        return $currentProduct->getAttributeText($attCode);
    }

    /**
     * @return mixed|null
     */
    public function getBrandObject()
    {
        $currentProduct = $this->getCurrentProduct();
        if (!$currentProduct) {
            return null;
        }

        $optionId = $currentProduct->getData($this->getAttributeCode()) ?? $this->getOptionBrandById($currentProduct->getId());
        if (!$optionId) {
            return null;
        }

        return $this->_brandFactory->create()->loadByOption($optionId);
    }

    /**
     * @return mixed|null
     * @throws LocalizedException
     */
    public function getBrandObjectById($productId)
    {
        $currentProduct = $this->productFactory->create()->load($productId);
        if (!$currentProduct) {
            return null;
        }
        $optionId = $currentProduct->getData($this->getAttributeCode()) ?? $this->getOptionBrandById($productId);
        if (!$optionId) {
            return null;
        }

        return $this->_brandFactory->create()->loadByOption($optionId);
    }

    /**
     * Get current product
     *
     * @return mixed
     */
    public function getCurrentProduct()
    {
        if (!$this->registry->registry('current_product')) {
            return $this->registry->registry('product');
        }
        return $this->registry->registry('current_product');
    }

    /**
     * @param $productId
     *
     * @return array|mixed|null
     */
    public function getOptionBrandById($productId)
    {
        $attributeCode = $this->getAttributeCode();
        $productCollection = $this->_productCollectionFactory->create();

        $productCollection->addFieldToFilter('entity_id', $productId);

        $productCollection->addAttributeToSelect($attributeCode);

        $product = $productCollection->getFirstItem();

        return $product->getData($attributeCode);
    }
    /**
     * Get current brand
     *
     * @return mixed
     */
    public function getBrand()
    {
        return $this->registry->registry('current_brand');
    }

    /**
     * convert strings in an array to uppercase
     *
     * @param array $array
     *
     * @return array|null
     */
    public function convertUppercase($array)
    {
        $input = array_flip($array);
        $input = array_change_key_case($input, CASE_UPPER);
        $input = array_flip($input);

        return $input;
    }

    /**
     * Get attribute id
     *
     * @param string $code
     *
     * @return int
     */
    public function getAttributeId($code)
    {
        return $this->_attribute->getIdByCode(Product::ENTITY, $code);
    }

    /**
     * @param null $format
     *
     * @return array
     */
    public function getDateRange($format = null)
    {
        try {
            if ($dateRange = $this->_request->getParam('dateRange')) {
                $startDate = $format ? $this->formatDate($format, $dateRange[0]) : $dateRange[0];
                $endDate   = $format ? $this->formatDate($format, $dateRange[1]) : $dateRange[1];
            } else {
                [$startDate, $endDate] = $this->getDateTimeRangeFormat('-1 month', 'now', null, $format);
            }
        } catch (Exception $e) {
            $this->_logger->critical($e);

            return [null, null];
        }

        return [$startDate, $endDate];
    }

    /**
     * @param null $startDate
     * @param null $endDate
     * @param null $isConvertToLocalTime
     *
     * @param null $format
     *
     * @return array
     * @throws Exception
     */
    public function getDateTimeRangeFormat($startDate, $endDate = null, $isConvertToLocalTime = null, $format = null)
    {
        $endDate   = (new DateTime($endDate ?: $startDate, new DateTimeZone($this->getTimezone())))->setTime(
            23,
            59,
            59
        );
        $startDate = (new DateTime($startDate, new DateTimeZone($this->getTimezone())))->setTime(00, 00, 00);

        if ($isConvertToLocalTime) {
            $startDate->setTimezone(new DateTimeZone('UTC'));
            $endDate->setTimezone(new DateTimeZone('UTC'));
        }

        return [$startDate->format($format ?: 'Y-m-d H:i:s'), $endDate->format($format ?: 'Y-m-d H:i:s')];
    }

    /**
     * @return array|mixed
     */
    public function getTimezone()
    {
        return $this->getConfigValue('general/locale/timezone');
    }

    /**
     * @param string $format
     * @param string $date
     *
     * @return string
     * @throws Exception
     */
    public function formatDate($format, $date)
    {
        return (new DateTime($date))->format($format);
    }

    /**
     * @return string
     */
    public function getPriceFormat()
    {
        return self::jsonEncode(
            $this->localeFormat->getPriceFormat()
        );
    }

    /**
     * @param $item
     *
     * @return false|DataObject
     */
    public function getProductBrand($item)
    {
        try {
            if ($item->getData('product_type') === 'configurable') {
                $product = $this->productRepository->getById($item->getData('product_id'), false, $item->getStoreId());
            } else {
                $product = $this->productRepository->get($item->getSku(), false, $item->getStoreId());
            }
            $brandAttrCode = $this->getAttributeCode();
            $brandOption = $product->getData($brandAttrCode)  ?? $this->getOptionBrandById($product->getId());
            if ($brandOption) {
                return $this->_brandFactory->create()->loadByOption($brandOption);
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * @return array
     */
    public function showBrandInfoCheckout()
    {
        return explode(',', $this->getConfigGeneral('show_brand_info_checkout'));
    }

    /**
     * @return bool
     */
    public function isOscEnable()
    {
        if ($this->_moduleManager->isEnabled('Mageplaza_Osc')) {
            if ($this->isEnabled() && $this->getConfigValue('osc/general/enabled')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isEnable($storeId = null)
    {
        return $this->isEnabled($storeId);
    }

    /**
     * @return bool
     */
    public function isOscDisable()
    {
        if (!$this->_moduleManager->isEnabled('Mageplaza_Osc')) {
            return true;
        } else {
            if ($this->isEnabled() && !$this->getConfigValue('osc/general/enabled')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $description
     *
     * @return mixed|string
     */
    public function shortenDesc($description)
    {
        preg_match_all('/<img[^>]+>/i', $description, $images);

        $content = strip_tags($description, '<p>');

        if (strlen($content) > 50) {
            $content = substr($content, 0, 40) . '...';
        }

        foreach ($images[0] as $value) {
            $pos = strpos($description, $value);
            if ($pos !== false) {
                $content = substr_replace($content, $value, $pos, 0);
            }
        }

        return $content;
    }

    /**
     * @param $description
     *
     * @return bool
     */
    public function isShortenDesc($description)
    {
        $content = strip_tags($description, '<p>');
        if (strlen($content) > 50) {
            return true;
        }

        return false;
    }

    /**
     * Get Brand form Item
     *
     * @param Item $item
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getItemBrandCheckoutData($item)
    {
        $brandData = [];
        if (!$this->getProductBrand($item) && !$item->getData('product_type') === 'configurable') {
            $brandData['mp_brand'] = false;
        } elseif ($brand = $this->getProductBrand($item)) {
            $brandData['mp_brand']    = true;
            $brandData['brand_url']   = $this->getBrandUrl($brand);
            $brandData['brand_value'] = $brand->getValue();

            if ((in_array(ShowBrandInfo::BRAND_LOGO, $this->showBrandInfoCheckout(), true))) {
                $brandData['brand_logo']   = true;
                $brandData['brand_url']    = $this->getBrandUrl($brand);
                $brandData['image_url']    = $this->getBrandImageUrl($brand);
                $brandData['swatch_type']  = $brand->getSwatchType();
                $brandData['swatch_value'] = $brand->getSwatchValue();
                $brandData['brand_value']  = $brand->getValue();
                $brandData['brand_width']  = $this->getConfigGeneral('logo_width_on_product_page');
                $brandData['brand_height'] = $this->getConfigGeneral('logo_height_on_product_page');
            }

            if ((in_array(ShowBrandInfo::BRAND_NAME, $this->showBrandInfoCheckout(), true))) {
                $brandData['brand_name'] = true;
            }

            if ((in_array(ShowBrandInfo::BRAND_DESC, $this->showBrandInfoCheckout(), true))) {
                $brandData['show_desc'] = true;
                $shortDesc              = $this->getBrandDescription($brand, true);
                if ($this->isShortenDesc($shortDesc)) {
                    $brandData['desc_is_shorten'] = true;
                }
                $brandData['short_desc'] = $this->shortenDesc($shortDesc);
            }

        }

        return $brandData;
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getBackgroundImageUrl($storeId = null)
    {
        $backgroundImageUrl = '';
        $backgroundImage    = $this->getSearchConfig('background_image');
        if (!empty($backgroundImage)) {
            $backgroundImageUrl = $this->_urlBuilder->getBaseUrl(['_type' => 'media']) . 'mageplaza/shopbybrand/' . $backgroundImage;
        }

        return $backgroundImageUrl;
    }

    /**
     * @param string $optionId
     * @param string|null $attrCode
     *
     * @return int
     */
    public function getProductQty(string $optionId, string $attrCode = null)
    {
        if (!$attrCode) {
            $attrCode = $this->getAttributeCode();
        }
        $qty = $this->getQtyProductInThis($attrCode, $optionId);
        if ($qty !== false) {
            return $qty;
        }

        $qty = $this->collectProductQty($optionId, $attrCode);
        $this->saveQtyOption($optionId, $attrCode, $qty);

        return $qty;
    }

    /**
     * Get Qty Product in save Data
     *
     * @param $attrCode
     * @param $optionId
     *
     * @return false|mixed
     */
    private function getQtyProductInThis($attrCode, $optionId)
    {
        $storeId        = $this->getStoreId();
        $allQty         = $this->fetchAllQtyOption($attrCode);
        $qtyOptionStore = $attrCode . '_' . $optionId . '_' . $storeId;

        foreach ($allQty as $qtyProduct) {
            if ($qtyProduct['attr_code_option_id_store_id'] === $qtyOptionStore) {
                return $qtyProduct['qty'];
            }
        }

        return false;
    }

    /**
     * Collect Extract Product Qty of Brand
     *
     * @param string $optionId
     * @param string $attrCode
     *
     * @return int
     */
    public function collectProductQty(string $optionId, string $attrCode)
    {
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection
            ->setVisibility(
                [
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH
                ]
            )
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter($attrCode, $optionId);

        $configOutStock = $this->getConfigValue('cataloginventory/options/show_out_of_stock', $this->getStoreId());
        if (empty($configOutStock)) {
            try {
                $productCollection->joinField(
                    'stock_status',
                    'cataloginventory_stock_status',
                    'stock_status',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left'
                )
                    ->addFieldToFilter('stock_status', ['eq' => 1]);
            } catch (LocalizedException $e) {
                $this->_logger->error($e);
            }
        }

        return $productCollection->getSize();
    }

    /**
     * @param string $attrCode
     * @param null $storeId
     *
     * @return array
     */
    public function fetchAllQtyOption(string $attrCode, $storeId = null)
    {
        if ($this->allQty) {
            return $this->allQty;
        }
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        $sql = $this->connection->select()
            ->from($this->getBrandQtyIndexTable(), ['qty', 'attr_code_option_id_store_id'])
            ->where("attr_code='$attrCode' AND store_id=$storeId");
        if (!$this->isShowBrandsWithoutProducts()) {
            $sql->where('qty > 0');
        }
        $allQty       = $this->connection->fetchAll($sql);
        $this->allQty = $allQty;

        return $this->allQty;
    }

    /**
     * @param string $optionId
     * @param string $attrCode
     * @param int|string $qty
     * @param string $storeId
     */
    public function saveQtyOption(string $optionId, string $attrCode, $qty, $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }
        $qtyOptionStore = $attrCode . '_' . $optionId . '_' . $storeId;
        $data           = [
            'attr_code_option_id_store_id' => $qtyOptionStore,
            'qty'                          => $qty,
            'store_id'                     => $storeId,
            'attr_code'                    => $attrCode,
        ];

        $this->connection->insertOnDuplicate(
            $this->getBrandQtyIndexTable(),
            $data,
            ['qty']
        );
    }

    /**
     * @return string
     */
    private function getBrandQtyIndexTable()
    {
        if (!$this->brandQtyIndexTable) {

            return $this->brandQtyIndexTable = $this->resource->getTableName(self::MAGEPLAZA_BRAND_QTY);
        }

        return $this->brandQtyIndexTable;
    }

    /**
     * set Attribute Value is null
     *
     * @param int $productId
     * @param int $storeId
     *
     * @throws NoSuchEntityException
     */
    public function unSetBrand($productId, $storeId)
    {
        $product = $this->productRepository->getById($productId);
        $product->addAttributeUpdate($this->getAttributeCode($storeId), null, $storeId);
    }

    /**
     * @param int $productId
     * @param int $optionId
     * @param int $storeId
     *
     * @throws NoSuchEntityException
     */
    public function setBrand($productId, $optionId, $storeId)
    {
        $product = $this->productRepository->getById($productId);
        $product->addAttributeUpdate($this->getAttributeCode($storeId), $optionId, $storeId);
    }

    /**
     * Index the catalogsearch_fulltext by list Products to show Products in Brand View page.
     *
     * @param $productIds
     */
    public function indexProducts($productIds)
    {
        $indexer = $this->indexerRegistry->get('catalogsearch_fulltext');
        $indexer?->reindexList($productIds);
    }
}
