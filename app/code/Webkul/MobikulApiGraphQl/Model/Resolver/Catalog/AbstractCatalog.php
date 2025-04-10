<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Catalog;

/**
 * AbstractCatalog abstract class
 */
abstract class AbstractCatalog extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController
{
    /**
     * Currency header static value
     */
    protected const CURRENT_CURRENCY = 'Current-Currency';

    /**
     * Cache tag static value
     */
    protected const CACHE_TAG = 'mobikul_product_cache';

    /**
     * Dir variable
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * Date variable
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Vote variable
     *
     * @var \Magento\Review\Model\Rating\Option\Vote
     */
    protected $vote;

    /**
     * Items variable
     *
     * @var [type]
     */
    protected $items;

    /**
     * Store variable
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * Helper variable
     *
     * @var \Webkul\MobikulCore\Helper\Data
     */
    protected $helper;

    /**
     * Rating variable
     *
     * @var \Magento\Review\Model\Rating
     */
    protected $rating;

    /**
     * Review variable
     *
     * @var \Magento\Review\Model\Review
     */
    protected $review;

    /**
     * Emulate variable
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * Escaper variable
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Toolbar variable
     *
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    protected $toolbar;

    /**
     * Compare variable
     *
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $compare;

    /**
     * BaseDir variable
     *
     * @var [type]
     */
    protected $baseDir;

    /**
     * Headers variable
     *
     * @var [type]
     */
    protected $headers;

    /**
     * CmsPage variable
     *
     * @var \Magento\Cms\Model\Page
     */
    protected $cmsPage;

    /**
     * Wishlist variable
     *
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlist;

    /**
     * Category variable
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $category;

    /**
     * TaxHelper variable
     *
     * @var  \Magento\Catalog\Helper\Data
     */
    protected $taxHelper;

    /**
     * EavConfig variable
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * SendFriend variable
     *
     * @var \Magento\SendFriend\Model\SendFriend
     */
    protected $sendFriend;

    /**
     * LocaleDate variable
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * QuoteModel variable
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quoteModel;

    /**
     * Connection variable
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $connection;

    /**
     * BannerImage variable
     *
     * @var \Webkul\MobikulCore\Model\Bannerimage
     */
    protected $bannerImage;

    /**
     * LinkFactory variable
     *
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    protected $linkFactory;

    /**
     * StockFilter variable
     *
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockFilter;

    /**
     * ImageHelper variable
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * SearchLayer variable
     *
     * @var \Magento\Catalog\Model\Layer\Search
     */
    protected $searchLayer;

    /**
     * CoreRegistry variable
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * LocaleFormat variable
     *
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * QueryFactory variable
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * EventManager variable
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * ProductPrice variable
     *
     * @var \Magento\Catalog\Block\Product\Price
     */
    protected $productPrice;

    /**
     * MobikulLayer variable
     *
     * @var \Webkul\MobikulCore\Model\Layer
     */
    protected $mobikulLayer;

    /**
     * CategoryTree variable
     *
     * @var \Webkul\MobikulCore\Model\Category\Tree
     */
    protected $categoryTree;

    /**
     * ProductLinks variable
     *
     * @var \Magento\Downloadable\Block\Catalog\Product\Links
     */
    protected $productLinks;

    /**
     * CatalogLayer variable
     *
     * @var [type]
     */
    protected $catalogLayer;

    /**
     * PriceCurrency variable
     *
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * CategoryLayer variable
     *
     * @var \Magento\Catalog\Model\Layer\Filter\Category
     */
    protected $categoryLayer;

    /**
     * ProductOption variable
     *
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $productOption;

    /**
     * HelperCatalog variable
     *
     * @var \Webkul\MobikulCore\Helper\Catalog
     */
    protected $helperCatalog;

    /**
     * PricingHelper variable
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * CatalogConfig variable
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * CmsCollection variable
     *
     * @var \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    protected $cmsCollection;

    /**
     * ProductStatus variable
     *
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $productStatus;

    /**
     * CustomerImage variable
     *
     * @var \Webkul\MobikulCore\Model\UserImage
     */
    protected $customerImage;

    /**
     * ProductSample variable
     *
     * @var \Magento\Downloadable\Block\Catalog\Product\Samples
     */
    protected $productSample;

    /**
     * StockRegistry variable
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * CategoryHelper variable
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $categoryHelper;

    /**
     * GroupedProduct variable
     *
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $groupedProduct;

    /**
     * CheckoutHelper variable
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * LayerAttribute variable
     *
     * @var \Magento\Catalog\Model\Layer\Filter\AttributeFactory
     */
    protected $layerAttribute;

    /**
     * DownloadSample variable
     *
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $downloadSample;

    /**
     * WebsiteManager variable
     *
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteManager;

    /**
     * DownloadHelper variable
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $downloadHelper;

    /**
     * StoreInterface variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeInterface;

    /**
     * ProductFactory variable
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * FilterProvider variable
     *
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * CarouselFactory variable
     *
     * @var \Webkul\MobikulCore\Model\CarouselFactory
     */
    protected $carouselFactory;

    /**
     * CustomerFactory variable
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * CustomerVisitor variable
     *
     * @var \Magento\Customer\Model\Visitor
     */
    protected $customerVisitor;

    /**
     * CustomerSession variable
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * BundlePriceModel variable
     *
     * @var \Magento\Bundle\Model\Product\Price
     */
    protected $bundlePriceModel;

    /**
     * SearchCollection variable
     *
     * @var \Magento\CatalogSearch\Model\ResourceModel\Search\Collection
     */
    protected $searchCollection;

    /**
     * CompareListBlock variable
     *
     * @var \Magento\Catalog\Block\Product\Compare\ListCompare
     */
    protected $compareListBlock;

    /**
     * ConfigurableBlock variable
     *
     * @var \Webkul\MobikulCore\Block\Configurable
     */
    protected $configurableBlock;

    /**
     * ProductCollection variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

    /**
     * AppcreatorFactory variable
     *
     * @var \Webkul\MobikulCore\Model\AppcreatorFactory
     */
    protected $appcreatorFactory;

    /**
     * CurrencyInterface variable
     *
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $currencyInterface;

    /**
     * ProductRepository variable
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ProductVisibility variable
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * MobikulLayerPrice variable
     *
     * @var \Webkul\MobikulCore\Model\ResourceModel\Layer\Filter\Price
     */
    protected $mobikulLayerPrice;

    /**
     * ProductCountSelect variable
     *
     * @var [type]
     */
    protected $productCountSelect;

    /**
     * ProductOptionBlock variable
     *
     * @var \Magento\Catalog\Block\Product\View\Options
     */
    protected $productOptionBlock;

    /**
     * FeaturedCategories variable
     *
     * @var \Webkul\MobikulCore\Model\Featuredcategories
     */
    protected $featuredCategories;

    /**
     * CompareItemFactory variable
     *
     * @var \Magento\Catalog\Model\Product\Compare\ItemFactory
     */
    protected $compareItemFactory;

    /**
     * MobikulNotification variable
     *
     * @var \Webkul\MobikulCore\Model\NotificationFactory
     */
    protected $mobikulNotification;

    /**
     * CatalogHelperOutput variable
     *
     * @var \Magento\Catalog\Helper\Output
     */
    protected $catalogHelperOutput;

    /**
     * FilterableAttributes variable
     *
     * @var \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
     */
    protected $filterableAttributes;

    /**
     * CategoryImageFactory variable
     *
     * @var \Webkul\MobikulCore\Model\CategoryimagesFactory
     */
    protected $categoryImageFactory;

    /**
     * ProductResourceModel variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResourceModel;

    /**
     * CarouselImageFactory variable
     *
     * @var \Webkul\MobikulCore\Model\CarouselimageFactory
     */
    protected $carouselImageFactory;

    /**
     * AdvancedCatalogSearch variable
     *
     * @var \Magento\CatalogSearch\Model\Advanced
     */
    protected $advancedCatalogSearch;

    /**
     * CategoryResourceModel variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $categoryResourceModel;

    /**
     * ReportCollectionFactory variable
     *
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection\Factory
     */
    protected $reportCollectionFactory;

    /**
     * FilterPriceDataprovider variable
     *
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory
     */
    protected $filterPriceDataprovider;

    /**
     * CategoryCollectionFactory variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * ProductResourceCollection variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productResourceCollection;

    /**
     * LayerFilterAttributeResource variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory
     */
    protected $layerFilterAttributeResource;

    /**
     * CompareItemCollectionFactory variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory
     */
    protected $compareItemCollectionFactory;

    /**
     * LayerResolver variable
     *
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * LocaleResolver variable
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * FilterAttribute variable
     *
     * @var \Magento\Catalog\Model\Layer\Filter\Attribute
     */
    protected $filterAttribute;

    /**
     * CurrencyFactory variable
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * BestSellerCollection variable
     *
     * @var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection
     */
    protected $bestSellerCollection;

    /**
     * FileDriver variable
     *
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * ProductList variable
     *
     * @var \Magento\CatalogWidget\Block\Product\ProductsList
     */
    protected $productList;

    /**
     * Serializer variable
     *
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * Base64Json variable
     *
     * @var \Magento\Framework\Serialize\Serializer\Base64Json
     */
    protected $base64Json;

    /**
     * ListCompareModel variable
     *
     * @var \Magento\Catalog\Model\Product\Compare\ListCompare
     */
    protected $listCompareModel;

    /**
     * WalkthroughFactory variable
     *
     * @var \Webkul\MobikulCore\Model\WalkthroughFactory
     */
    protected $walkthroughFactory;

    /**
     * DefaultStockQty variable
     *
     * @var \Magento\CatalogInventory\Block\Stockqty\DefaultStockqty
     */
    protected $defaultStockQty;

    /**
     * LayerBuilder variable
     *
     * @var \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder
     */
    protected $layerBuilder;

    /**
     * PriceCurrencyModel variable
     *
     * @var \Magento\Directory\Model\PriceCurrency
     */
    protected $priceCurrencyModel;

    /**
     * PageSizeProvider variable
     *
     * @var \Magento\Search\Model\Search\PageSizeProvider
     */
    protected $pageSizeProvider;

    /**
     * Search variable
     *
     * @var \Magento\Search\Api\SearchInterface
     */
    protected $search;

    /**
     * ProductsProvider variable
     *
     * @var \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch
     */
    protected $productsProvider;

    /**
     * FieldSelection variable
     *
     * @var \Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection
     */
    protected $fieldSelection;

    /**
     * QueryPopularity variable
     *
     * @var \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search\QueryPopularity
     */
    protected $queryPopularity;

    /**
     * Suggestions variable
     *
     * @var \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Suggestions
     */
    protected $suggestions;

    /**
     * SearchResultFactory variable
     *
     * @var \Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * ArgsSelection variable
     *
     * @var \Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface
     */
    protected $argsSelection;

    /**
     * SearchCriteriaBuilder variable
     *
     * @var \Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    
    /**
     * Cache variable
     *
     * @var mixed
     */
    protected $cache;

    /**
     * Constructor function
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Review\Model\Rating $rating
     * @param \Magento\Review\Model\Review $review
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Quote\Model\Quote $quoteModel
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Data $taxHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Webkul\MobikulCore\Model\Layer $mobikulLayer
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Review\Model\Rating\Option\Vote $vote
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Model\Layer\Search $searchLayer
     * @param \Magento\SendFriend\Model\SendFriend $sendFriend
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\CategoryFactory $category
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Catalog\Helper\Product\Compare $compare
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlist
     * @param \Webkul\MobikulCore\Helper\Catalog $helperCatalog
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Downloadable\Helper\File $downloadHelper
     * @param \Webkul\MobikulCore\Model\Bannerimage $bannerImage
     * @param \Webkul\MobikulCore\Model\UserImage $customerImage
     * @param \Magento\Downloadable\Model\Sample $downloadSample
     * @param \Magento\Catalog\Block\Product\Price $productPrice
     * @param \Magento\Store\Model\WebsiteFactory $websiteManager
     * @param \Magento\CatalogInventory\Helper\Stock $stockFilter
     * @param \Magento\Catalog\Helper\Output $catalogHelperOutput
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Product\Option $productOption
     * @param \Magento\Downloadable\Model\LinkFactory $linkFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Bundle\Model\Product\Price $bundlePriceModel
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\App\ResourceConnection $connection
     * @param \Webkul\MobikulCore\Model\Category\Tree $categoryTree
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\MobikulCore\Block\Configurable $configurableBlock
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Webkul\MobikulCore\Model\CarouselFactory $carouselFactory
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeInterface
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar
     * @param \Magento\Catalog\Model\Layer\Filter\Category $categoryLayer
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\CatalogSearch\Model\Advanced $advancedCatalogSearch
     * @param \Magento\Catalog\Model\Layer\Filter\Attribute $filterAttribute
     * @param \Magento\Framework\Locale\CurrencyInterface $currencyInterface
     * @param \Webkul\MobikulCore\Model\AppcreatorFactory $appcreatorFactory
     * @param \Magento\Downloadable\Block\Catalog\Product\Links $productLinks
     * @param \Magento\Cms\Model\ResourceModel\Page\Collection $cmsCollection
     * @param \Magento\Catalog\Block\Product\View\Options $productOptionBlock
     * @param \Webkul\MobikulCore\Model\Featuredcategories $featuredCategories
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResourceModel
     * @param \Webkul\MobikulCore\Model\NotificationFactory $mobikulNotification
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Downloadable\Block\Catalog\Product\Samples $productSample
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Webkul\MobikulCore\Model\CarouselimageFactory $carouselImageFactory
     * @param \Magento\Catalog\Block\Product\Compare\ListCompare $compareListBlock
     * @param \Magento\Catalog\Model\Layer\Filter\AttributeFactory $layerAttribute
     * @param \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Webkul\MobikulCore\Model\CategoryimagesFactory $categoryImageFactory
     * @param \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory
     * @param \Webkul\MobikulCore\Model\ResourceModel\Layer\Filter\Price $mobikulLayerPrice
     * @param \Magento\CatalogSearch\Model\ResourceModel\Search\Collection $searchCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productResourceCollection
     * @param \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $reportCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $layerFilterAttributeResource
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $compareItemCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection $bestSellerCollection
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $productList
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Magento\Framework\Serialize\Serializer\Base64Json $base64Json
     * @param \Magento\Catalog\Model\Product\Compare\ListCompare $listCompareModel
     * @param \Webkul\MobikulCore\Model\WalkthroughFactory $walkthroughFactory
     * @param \Magento\CatalogInventory\Block\Stockqty\DefaultStockqty $defaultStockQty
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder $layerBuilder
     * @param \Magento\Directory\Model\PriceCurrency $priceCurrencyModel
     * @param \Magento\Search\Model\Search\PageSizeProvider $pageSizeProvider
     * @param \Magento\Search\Api\SearchInterface $search
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch $productsProvider
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection $fieldSelection
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search\QueryPopularity $queryPopularity
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Suggestions $suggestions
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory $searchResultFactory
     * @param \Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface $argsSelection
     * @param \Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Cms\Model\Page $cmsPage,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\Escaper $escaper,
        \Magento\Review\Model\Rating $rating,
        \Magento\Review\Model\Review $review,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Quote\Model\Quote $quoteModel,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Catalog\Helper\Data $taxHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Webkul\MobikulCore\Model\Layer $mobikulLayer,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Locale\Format $localeFormat,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Review\Model\Rating\Option\Vote $vote,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\Layer\Search $searchLayer,
        \Magento\SendFriend\Model\SendFriend $sendFriend,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\CategoryFactory $category,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Helper\Product\Compare $compare,
        \Magento\Wishlist\Model\WishlistFactory $wishlist,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Downloadable\Helper\File $downloadHelper,
        \Webkul\MobikulCore\Model\Bannerimage $bannerImage,
        \Webkul\MobikulCore\Model\UserImage $customerImage,
        \Magento\Downloadable\Model\Sample $downloadSample,
        \Magento\Catalog\Block\Product\Price $productPrice,
        \Magento\Store\Model\WebsiteFactory $websiteManager,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Catalog\Helper\Output $catalogHelperOutput,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Product\Option $productOption,
        \Magento\Downloadable\Model\LinkFactory $linkFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Bundle\Model\Product\Price $bundlePriceModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\ResourceConnection $connection,
        \Webkul\MobikulCore\Model\Category\Tree $categoryTree,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\MobikulCore\Block\Configurable $configurableBlock,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Webkul\MobikulCore\Model\CarouselFactory $carouselFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeInterface,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
        \Magento\Catalog\Model\Layer\Filter\Category $categoryLayer,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\CatalogSearch\Model\Advanced $advancedCatalogSearch,
        \Magento\Catalog\Model\Layer\Filter\Attribute $filterAttribute,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface,
        \Webkul\MobikulCore\Model\AppcreatorFactory $appcreatorFactory,
        \Magento\Downloadable\Block\Catalog\Product\Links $productLinks,
        \Magento\Cms\Model\ResourceModel\Page\Collection $cmsCollection,
        \Magento\Catalog\Block\Product\View\Options $productOptionBlock,
        \Webkul\MobikulCore\Model\Featuredcategories $featuredCategories,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Webkul\MobikulCore\Model\NotificationFactory $mobikulNotification,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Downloadable\Block\Catalog\Product\Samples $productSample,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Webkul\MobikulCore\Model\CarouselimageFactory $carouselImageFactory,
        \Magento\Catalog\Block\Product\Compare\ListCompare $compareListBlock,
        \Magento\Catalog\Model\Layer\Filter\AttributeFactory $layerAttribute,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Webkul\MobikulCore\Model\CategoryimagesFactory $categoryImageFactory,
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Layer\Filter\Price $mobikulLayerPrice,
        \Magento\CatalogSearch\Model\ResourceModel\Search\Collection $searchCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productResourceCollection,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $reportCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $layerFilterAttributeResource,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $compareItemCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection $bestSellerCollection,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\CatalogWidget\Block\Product\ProductsList $productList,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64Json,
        \Magento\Catalog\Model\Product\Compare\ListCompare $listCompareModel,
        \Webkul\MobikulCore\Model\WalkthroughFactory $walkthroughFactory,
        \Magento\CatalogInventory\Block\Stockqty\DefaultStockqty $defaultStockQty,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder $layerBuilder,
        \Magento\Directory\Model\PriceCurrency $priceCurrencyModel,
        \Magento\Search\Model\Search\PageSizeProvider $pageSizeProvider,
        \Magento\Search\Api\SearchInterface $search,
        \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch $productsProvider,
        \Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection $fieldSelection,
        \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search\QueryPopularity $queryPopularity,
        \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Suggestions $suggestions,
        \Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory $searchResultFactory,
        \Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface $argsSelection,
        \Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->date = $date;
        $this->vote = $vote;
        $this->store = $store;
        $this->helper = $helper;
        $this->rating = $rating;
        $this->review = $review;
        $this->escaper = $escaper;
        $this->compare = $compare;
        $this->baseDir = $dir->getPath("media");
        $this->toolbar = $toolbar;
        $this->cmsPage = $cmsPage;
        $this->emulate = $emulate;
        $this->category = $category;
        $this->wishlist = $wishlist;
        $this->taxHelper = $taxHelper;
        $this->eavConfig = $eavConfig;
        $this->connection = $connection;
        $this->sendFriend = $sendFriend;
        $this->localeDate = $localeDate;
        $this->quoteModel = $quoteModel;
        $this->jsonHelper = $jsonHelper;
        $this->searchLayer = $searchLayer;
        $this->linkFactory = $linkFactory;
        $this->bannerImage = $bannerImage;
        $this->stockFilter = $stockFilter;
        $this->imageHelper = $imageHelper;
        $this->catalogLayer = $layerResolver->get();
        $this->categoryTree = $categoryTree;
        $this->localeFormat = $localeFormat;
        $this->eventManager = $eventManager;
        $this->queryFactory = $queryFactory;
        $this->mobikulLayer = $mobikulLayer;
        $this->coreRegistry = $coreRegistry;
        $this->productLinks = $productLinks;
        $this->productPrice = $productPrice;
        $this->priceCurrency = $priceCurrency;
        $this->productSample = $productSample;
        $this->helperCatalog = $helperCatalog;
        $this->customerImage = $customerImage;
        $this->productStatus = $productStatus;
        $this->categoryLayer = $categoryLayer;
        $this->pricingHelper = $pricingHelper;
        $this->catalogConfig = $catalogConfig;
        $this->cmsCollection = $cmsCollection;
        $this->productOption = $productOption;
        $this->layerResolver = $layerResolver;
        $this->stockRegistry = $stockRegistry;
        $this->categoryHelper = $categoryHelper;
        $this->groupedProduct = $groupedProduct;
        $this->downloadHelper = $downloadHelper;
        $this->layerAttribute = $layerAttribute;
        $this->downloadSample = $downloadSample;
        $this->storeInterface = $storeInterface;
        $this->websiteManager = $websiteManager;
        $this->checkoutHelper = $checkoutHelper;
        $this->productFactory = $productFactory;
        $this->localeResolver = $localeResolver;
        $this->filterProvider = $filterProvider;
        $this->customerSession = $customerSession;
        $this->filterAttribute = $filterAttribute;
        $this->carouselFactory = $carouselFactory;
        $this->customerVisitor = $customerVisitor;
        $this->currencyFactory = $currencyFactory;
        $this->customerFactory = $customerFactory;
        $this->bundlePriceModel = $bundlePriceModel;
        $this->compareListBlock = $compareListBlock;
        $this->searchCollection = $searchCollection;
        $this->appcreatorFactory = $appcreatorFactory;
        $this->currencyInterface = $currencyInterface;
        $this->configurableBlock = $configurableBlock;
        $this->mobikulLayerPrice = $mobikulLayerPrice;
        $this->productRepository = $productRepository;
        $this->productVisibility = $productVisibility;
        $this->productCollection = $productCollection;
        $this->featuredCategories = $featuredCategories;
        $this->compareItemFactory = $compareItemFactory;
        $this->productOptionBlock = $productOptionBlock;
        $this->catalogHelperOutput = $catalogHelperOutput;
        $this->mobikulNotification = $mobikulNotification;
        $this->categoryImageFactory = $categoryImageFactory;
        $this->carouselImageFactory = $carouselImageFactory;
        $this->filterableAttributes = $filterableAttributes;
        $this->productResourceModel = $productResourceModel;
        $this->advancedCatalogSearch = $advancedCatalogSearch;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->reportCollectionFactory = $reportCollectionFactory;
        $this->filterPriceDataprovider = $filterPriceDataprovider;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productResourceCollection = $productResourceCollection;
        $this->layerFilterAttributeResource = $layerFilterAttributeResource;
        $this->compareItemCollectionFactory = $compareItemCollectionFactory;
        $this->bestSellerCollection = $bestSellerCollection;
        $this->fileDriver = $fileDriver;
        $this->productList = $productList;
        $this->serializer = $serializer;
        $this->base64Json = $base64Json;
        $this->listCompareModel = $listCompareModel;
        $this->walkthroughFactory = $walkthroughFactory;
        $this->defaultStockQty = $defaultStockQty;
        $this->layerBuilder = $layerBuilder;
        $this->priceCurrencyModel = $priceCurrencyModel;
        $this->pageSizeProvider = $pageSizeProvider;
        $this->search = $search;
        $this->productsProvider = $productsProvider;
        $this->fieldSelection = $fieldSelection;
        $this->queryPopularity = $queryPopularity;
        $this->suggestions = $suggestions;
        $this->searchResultFactory = $searchResultFactory;
        $this->argsSelection = $argsSelection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($helper, $request, $jsonHelper);
    }

    /**
     * IsCacheEnabled function
     *
     * @return boolean
     */
    public function isCacheEnabled()
    {
        return $this->helper->getConfigData("mobikul/cachesettings/enable") ? true : false;
    }

    /**
     * GetCacheLifetime function
     *
     * @return boolean
     */
    public function getCacheLifetime()
    {
        return $this->helper->getConfigData("mobikul/cachesettings/ttl");
    }

    /**
     * GetDecodedFromCache function
     *
     * @param mixed $identifier
     * @return void
     */
    public function getDecodedFromCache($identifier)
    {
        $data = $this->getDataFromCache($identifier);
        return $data ? $this->decodeCacheData($data) : null;
    }

    /**
     * GetDataFromCache function
     *
     * @param mixed $identifier
     * @return void
     */
    public function getDataFromCache($identifier)
    {
        $cache = $this->getCache();
        return $cache->load($identifier);
    }

    /**
     * GetCache function
     *
     * @return boolean
     */
    public function getCache()
    {
        if (empty($this->cache)) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $this->cache =  $om->get(\Magento\Framework\App\CacheInterface::class);
        }
        return $this->cache;
    }

    /**
     * DecodeCacheData function
     *
     * @param mixed $data
     * @return void
     */
    public function decodeCacheData($data)
    {
        return json_decode($data, true);
    }

    /**
     * EncodeAndSaveInCache function
     *
     * @param mixed $data
     * @param mixed $identifier
     * @param mixed $tags
     * @return void
     */
    public function encodeAndSaveInCache($data, $identifier, $tags)
    {
        $this->getCache()->save($this->encodeCacheData($data), $identifier, $tags, $this->getCacheLifetime());
    }

    /**
     * EncodeCacheData function
     *
     * @param mixed $data
     * @return void
     */
    public function encodeCacheData($data)
    {
        return json_encode($data);
    }
}
