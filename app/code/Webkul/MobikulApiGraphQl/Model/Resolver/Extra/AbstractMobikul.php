<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphql
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Extra;

use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MobikulCore\Helper\Data as HelperData;

/**
 * AbstractMobikul class
 */
abstract class AbstractMobikul extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController
{
    /**
     * Currency header static value
     */
    protected const CURRENT_CURRENCY = 'Current-Currency';
    
    /**
     * Quote variable
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Helper variable
     *
     * @var Webkul\MobikulCore\Helper\Data
     */
    protected $helper;

    /**
     * Logger variable
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * CmsPage variable
     *
     * @var \Magento\Cms\Model\Page
     */
    protected $cmsPage;

    /**
     * CmsHelperPage variable
     *
     * @var \Magento\Cms\Helper\Page
     */
    protected $cmsHelperPage;

    /**
     * Toolbar variable
     *
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    protected $toolbar;

    /**
     * BaseDir variable
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $baseDir;

    /**
     * Emulate variable
     *
     * @var Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * Visitor variable
     *
     * @var \Magento\Customer\Model\Visitor
     */
    protected $visitor;

    /**
     * EavConfig variable
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * SearchQuery variable
     *
     * @var \Magento\Search\Model\Query
     */
    protected $searchQuery;

    /**
     * DeviceToken variable
     *
     * @var \Webkul\MobikulCore\Helper\Token
     */
    protected $deviceToken;

    /**
     * CompareItem variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item
     */
    protected $compareItem;

    /**
     * PriceHelper variable
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * CoreSession variable
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * ImageHelper variable
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * BlockFactory variable
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Cataloghelper variable
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $cataloghelper;

    /**
     * CatalogConfig variable
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * PricingHelper variable
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * ProductStatus variable
     *
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $productStatus;

    /**
     * FilterProvider variable
     *
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * ProductFactory variable
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * ProductCompare variable
     *
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $productCompare;

    /**
     * QueryCollection variable
     *
     * @var \Magento\Search\Model\ResourceModel\Query\Collection
     */
    protected $queryCollection;

    /**
     * CustomerSession variable
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * FilterAttribute variable
     *
     * @var \Magento\Catalog\Model\Layer\Filter\Attribute
     */
    protected $filterAttribute;

    /**
     * CategoryFactory variable
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * CustomerFactory variable
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * ProductVisibility variable
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * ProductCollection variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * CompareCollection variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory
     */
    protected $compareCollection;

    /**
     * DeviceTokenFactory variable
     *
     * @var \Webkul\MobikulCore\Model\DeviceTokenFactory
     */
    protected $deviceTokenFactory;

    /**
     * MobikulNotification variable
     *
     * @var \Webkul\MobikulCore\Model\NotificationFactory
     */
    protected $mobikulNotification;

    /**
     * LayerFilterAttribute variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute
     */
    protected $layerFilterAttribute;

    /**
     * FilterableAttributes variable
     *
     * @var \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
     */
    protected $filterableAttributes;

    /**
     * SearchSuggestionHelper variable
     *
     * @var \Webkul\MobikulCore\Helper\Searchsuggestion
     */
    protected $searchSuggestionHelper;

    /**
     * HelperCatalog variable
     *
     * @var \Webkul\MobikulCore\Helper\Catalog
     */
    protected $helperCatalog;

    /**
     * BundlePriceModel variable
     *
     * @var \Magento\Bundle\Model\Product\Price
     */
    protected $bundlePriceModel;

    /**
     * FileDriver variable
     *
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * Cerializer variable
     *
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * EventManager variable
     * 
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Store variable
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * Constructor function
     *
     * @param Context $context
     * @param HelperData $helper
     * @param Emulation $emulate
     * @param \Magento\Cms\Helper\Page $cmsHelperPage
     * @param \Magento\Cms\Model\Page $cmsPage
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Visitor $visitor
     * @param \Magento\Search\Model\Query $searchQuery
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Checkout\Helper\Data $cataloghelper
     * @param \Webkul\MobikulCore\Helper\Token $deviceToken
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MobikulCore\Helper\Catalog $helperCatalog
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Bundle\Model\Product\Price $bundlePriceModel
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Product\Compare $productCompare
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Layer\Filter\Attribute $filterAttribute
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceTokenFactory
     * @param \Webkul\MobikulCore\Model\NotificationFactory $mobikulNotification
     * @param \Webkul\MobikulCore\Helper\Searchsuggestion $searchSuggestionHelper
     * @param \Magento\Search\Model\ResourceModel\Query\Collection $queryCollection
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $compareItem
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute $layerFilterAttribute
     * @param \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $compareCollection
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\Store $store
     */
    public function __construct(
        Context $context,
        HelperData $helper,
        Emulation $emulate,
        \Magento\Store\Model\Store $store,
        \Magento\Cms\Helper\Page $cmsHelperPage,
        \Magento\Cms\Model\Page $cmsPage,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Visitor $visitor,
        \Magento\Search\Model\Query $searchQuery,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Checkout\Helper\Data $cataloghelper,
        \Webkul\MobikulCore\Helper\Token $deviceToken,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Bundle\Model\Product\Price $bundlePriceModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Product\Compare $productCompare,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Layer\Filter\Attribute $filterAttribute,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceTokenFactory,
        \Webkul\MobikulCore\Model\NotificationFactory $mobikulNotification,
        \Webkul\MobikulCore\Helper\Searchsuggestion $searchSuggestionHelper,
        \Magento\Search\Model\ResourceModel\Query\Collection $queryCollection,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $compareItem,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute $layerFilterAttribute,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $compareCollection,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->quote = $quote;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->visitor = $visitor;
        $this->cmsHelperPage = $cmsHelperPage;
        $this->cmsPage = $cmsPage;
        $this->emulate = $emulate;
        $this->toolbar = $toolbar;
        $this->eavConfig = $eavConfig;
        $this->jsonHelper = $jsonHelper;
        $this->searchQuery = $searchQuery;
        $this->imageHelper = $imageHelper;
        $this->compareItem = $compareItem;
        $this->deviceToken = $deviceToken;
        $this->coreSession = $coreSession;
        $this->priceHelper = $priceHelper;
        $this->storeManager = $storeManager;
        $this->blockFactory = $blockFactory;
        $this->cataloghelper = $cataloghelper;
        $this->catalogConfig = $catalogConfig;
        $this->productStatus = $productStatus;
        $this->helperCatalog = $helperCatalog;
        $this->pricingHelper = $pricingHelper;
        $this->productCompare = $productCompare;
        $this->filterProvider = $filterProvider;
        $this->productFactory = $productFactory;
        $this->baseDir = $dir->getPath("media");
        $this->filterAttribute = $filterAttribute;
        $this->categoryFactory = $categoryFactory;
        $this->queryCollection = $queryCollection;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->bundlePriceModel = $bundlePriceModel;
        $this->compareCollection = $compareCollection;
        $this->productVisibility = $productVisibility;
        $this->productCollection = $productCollection;
        $this->deviceTokenFactory = $deviceTokenFactory;
        $this->mobikulNotification = $mobikulNotification;
        $this->layerFilterAttribute = $layerFilterAttribute;
        $this->filterableAttributes = $filterableAttributes;
        $this->searchSuggestionHelper = $searchSuggestionHelper;
        $this->fileDriver = $fileDriver;
        $this->serializer = $serializer;
        $this->eventManager = $eventManager;
        $this->store = $store;
        parent::__construct($helper, $request, $jsonHelper);
    }
}
