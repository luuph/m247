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

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Customer;

use Magento\Framework\Filesystem;
use \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory;

/**
 * AbstractCustomer abstract class
 */
abstract class AbstractCustomer extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController
{
    /**
     * Cart variable
     *
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * CheckoutSession variable
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Vote variable
     *
     * @var \Magento\Review\Model\Rating\Option\Vote
     */
    protected $vote;

    /**
     * Order variable
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * Quote variable
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Review variable
     *
     * @var \Magento\Review\Model\Review
     */
    protected $review;

    /**
     * Visitor variable
     *
     * @var \Magento\Customer\Model\Visitor
     */
    protected $visitor;

    /**
     * Country variable
     *
     * @var \Magento\Directory\Model\Country
     */
    protected $country;

    /**
     * BaseDir variable
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $baseDir;

    /**
     * Wishlist variable
     *
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlist;

    /**
     * Request variable
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * UserImage variable
     *
     * @var \Webkul\MobikulCore\Model\UserImage
     */
    protected $userImage;

    /**
     * Encryptor variable
     *
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * LocaleDate variable
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * OrderTotals variable
     *
     * @var \Webkul\MobikulApiGraphQl\Block\Sales\Order\Totals
     */
    protected $orderTotals;

    /**
     * salesHelper variable
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $salesHelper;

    /**
     * CartFactory variable
     *
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $cartFactory;

    /**
     * TokenHelper variable
     *
     * @var \Webkul\MobikulCore\Helper\Token
     */
    protected $tokenHelper;

    /**
     * CompareItem variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item
     */
    protected $compareItem;

    /**
     * ListProduct variable
     *
     * @var \Magento\Downloadable\Block\Customer\Products\ListProducts
     */
    protected $listProduct;

    /**
     * OrderConfig variable
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * QuoteFactory variable
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * WishlistItem variable
     *
     * @var \Magento\Wishlist\Model\Item
     */
    protected $wishlistItem;

    /**
     * ShippingView variable
     *
     * @var \Magento\Shipping\Block\Adminhtml\Order\Tracking\View
     */
    protected $shippingView;

    /**
     * ReviewHelper variable
     *
     * @var \Magento\Review\Helper\Data
     */
    protected $reviewHelper;

    /**
     * CustomerList variable
     *
     * @var \Magento\Review\Block\Customer\ListCustomer
     */
    protected $customerList;

    /**
     * CustomerForm variable
     *
     * @var \Magento\Customer\Model\Form
     */
    protected $customerForm;

    /**
     * ReviewFactory variable
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * StockRegistry variable
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * CatalogConfig variable
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * AddressHelper variable
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $addressHelper;

    /**
     * Purchasedlink variable
     *
     * @var \Magento\Downloadable\Model\Link\Purchased
     */
    protected $purchasedlink;

    /**
     * InvoiceTotals variable
     *
     * @var \Webkul\MobikulApiGraphQl\Block\Sales\Order\Invoice\Totals
     */
    protected $invoiceTotals;

    /**
     * RatingFactory variable
     *
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $ratingFactory;

    /**
     * PriceRenderer variable
     *
     * @var \Magento\Weee\Block\Item\Price\Renderer
     */
    protected $priceRenderer;

    /**
     * ProductHelper variable
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * PricingHelper variable
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * SecurityConfig variable
     *
     * @var \Magento\Security\Model\ConfigInterface
     */
    protected $securityConfig;

    /**
     * Authentication variable
     *
     * @var mixed
     */
    protected $authentication;

    /**
     * ProductFactory variable
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * CustomerMapper variable
     *
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $customerMapper;

    /**
     * WishlistHelper variable
     *
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * OrderInfoBlock variable
     *
     * @var \Magento\Sales\Block\Order\Info
     */
    protected $orderInfoBlock;

    /**
     * VoteCollection variable
     *
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection
     */
    protected $voteCollection;

    /**
     * StockRepository variable
     *
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $stockRepository;

    /**
     * CustomerSession variable
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * CustomerAddress variable
     *
     * @var \Magento\Customer\Model\Address
     */
    protected $customerAddress;

    /**
     * OrderCollection variable
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orderCollection;

    /**
     * CustomerFactory variable
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * WishlistProvider variable
     *
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $wishlistProvider;

    /**
     * TransportBuilder variable
     *
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * CreditmemoTotals variable
     *
     * @var \Webkul\MobikulApiGraphQl\Block\Sales\Order\Creditmemo\Totals
     */
    protected $creditmemoTotals;

    /**
     * ReviewCollection variable
     *
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollection;

    /**
     * DownloadableLink variable
     *
     * @var \Magento\Downloadable\Model\Link
     */
    protected $downloadableLink;

    /**
     * DataObjectHelper variable
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * InlineTranslation variable
     *
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * PurchasedLinkItem variable
     *
     * @var \Magento\Downloadable\Model\Link\Purchased\Item
     */
    protected $purchasedLinkItem;

    /**
     * ProductVisibility variable
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * ProductRepository variable
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * AccountManagement variable
     *
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * EmailNotification variable
     *
     * @var mixed
     */
    protected $emailNotification;

    /**
     * CustomerExtractor variable
     *
     * @var \Magento\Customer\Model\CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * AddressRepository variable
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * CountryCollection variable
     *
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * OrderHistoryBlock variable
     *
     * @var \Magento\Sales\Block\Order\History
     */
    protected $orderHistoryBlock;

    /**
     * OrderItemRenderer variable
     *
     * @var \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
     */
    protected $orderItemRenderer;

    /**
     * CollectionFactory variable
     *
     * @var \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * WishlistItemOption variable
     *
     * @var \Magento\Wishlist\Model\Item\Option
     */
    protected $wishlistItemOption;

    /**
     * DownloadableHelper variable
     *
     * @var \Magento\Downloadable\Helper\Data
     */
    protected $downloadableHelper;

    /**
     * InvoiceItemRenderer variable
     *
     * @var \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer
     */
    protected $invoiceItemRenderer;

    /**
     * DownloadableFileHelper variable
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $downloadableFileHelper;

    /**
     * PurchasedLinkCollection variable
     *
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection
     */
    protected $purchasedLinkCollection;

    /**
     * ProductCollectionFactory variable
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * CatalogProductCompareList variable
     *
     * @var \Magento\Catalog\Model\Product\Compare\ListCompare
     */
    protected $catalogProductCompareList;

    /**
     * ProductConfigurationHelper variable
     *
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfigurationHelper;

    /**
     * ProductConfigurationHelper variable
     *
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepositoryInterface;

    /**
     * CustomerRepositoryInterface variable
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * PurchasedLinkItemCollection variable
     *
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection
     */
    protected $purchasedLinkItemCollection;

    /**
     * InvoiceItemCollectionFactory variable
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory
     */
    protected $invoiceItemCollectionFactory;

    /**
     * CreditmemoRepository variable
     *
     * @var Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * ViewTemplate variable
     *
     * @var \Magento\Framework\View\Element\Template
     */
    protected $viewTemplate;

    /**
     * ScopeConfig variable
     *
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * DashboardHelper variable
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $dashboardHelper;

    /**
     * Store variable
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * Emulate variable
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * CoreRegistry variable
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * HelperCatalog variable
     *
     * @var \Webkul\MobikulCore\Helper\Catalog
     */
    protected $helperCatalog;

    /**
     * ProductCompare variable
     *
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $productCompare;

    /**
     * InvoiceRepository variable
     *
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * CreditRepository variable
     *
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditRepository;

    /**
     * AdminPriceRenderer variable
     *
     * @var \Magento\Weee\Block\Adminhtml\Items\Price\Renderer
     */
    protected $adminPriceRenderer;

    /**
     * CustomerReviewBlock variable
     *
     * @var \Magento\Review\Block\Customer\View
     */
    protected $customerReviewBlock;

    /**
     * CreditmemoItemCollectionFactory variable
     *
     * @var CollectionFactory
     */
    protected $creditmemoItemCollectionFactory;

    /**
     * OrderRepository variable
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * FileDriver variable
     *
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * InvoicePdf variable
     *
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    protected $invoicePdf;

    /**
     * FileFactory variable
     *
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * Date variable
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * DeviceTokenFactory variable
     *
     * @var \Webkul\MobikulCore\Model\DeviceTokenFactory
     */
    protected $deviceTokenFactory;

    /**
     * CoreSession variable
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * Filesystem variable
     *
     * @var Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * IoFile variable
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    /**
     * UserImageFactory variable
     *
     * @var \Webkul\MobikulCore\Model\UserImageFactory
     */
    protected $userImageFactory;

    /**
     * UploadHelper variable
     *
     * @var \Webkul\MobikulCore\Helper\Upload
     */
    protected $uploadHelper;

    /**
     * @var  \Magento\Integration\Model\Oauth\TokenFactory
     */
    protected $tokenModelFactory;

    /**
     * @var  \Magento\Sales\Model\Reorder\Reorder
     */
    protected $reorder;

    /**
     * Construct function
     *
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Review\Model\Review $review
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Magento\Customer\Model\Visitor $visitor
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Review\Helper\Data $reviewHelper
     * @param \Magento\Directory\Model\Country $country
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @param \Magento\Customer\Model\Form $customerForm
     * @param \Magento\Sales\Helper\Reorder $salesHelper
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Webkul\MobikulCore\Helper\Token $tokenHelper
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Webkul\MobikulCore\Model\UserImage $userImage
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Review\Model\Rating\Option\Vote $vote
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Sales\Block\Order\Info $orderInfoBlock
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Customer\Model\Address $customerAddress
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\MobikulCore\Helper\Catalog $helperCatalog
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlist
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Wishlist\Model\Wishlist $wishlistProvider
     * @param \Magento\Downloadable\Model\Link $downloadableLink
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Sales\Block\Order\History $orderHistoryBlock
     * @param \Magento\Downloadable\Helper\Data $downloadableHelper
     * @param \Magento\Weee\Block\Item\Price\Renderer $priceRenderer
     * @param \Magento\Security\Model\ConfigInterface $securityConfig
     * @param \Magento\Catalog\Helper\Product\Compare $productCompare
     * @param \Magento\Wishlist\Model\Item\Option $wishlistItemOption
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Review\Block\Customer\View $customerReviewBlock
     * @param \Webkul\MobikulApiGraphQl\Block\Sales\Order\Totals $orderTotals
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Block\Customer\ListCustomer $customerList
     * @param \Magento\Downloadable\Helper\File $downloadableFileHelper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Downloadable\Model\Link\Purchased $purchasedlink
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Customer\Model\CustomerExtractor $customerExtractor
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Downloadable\Model\Link\Purchased\Item $purchasedLinkItem
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Webkul\MobikulApiGraphQl\Block\Sales\Order\Invoice\Totals $invoiceTotals
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepoInterface
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Shipping\Block\Adminhtml\Order\Tracking\View $shippingView
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $compareItem
     * @param \Magento\Weee\Block\Adminhtml\Items\Price\Renderer $adminPriceRenderer
     * @param \Magento\Downloadable\Block\Customer\Products\ListProducts $listProduct
     * @param \Webkul\MobikulApiGraphQl\Block\Sales\Order\Creditmemo\Totals $creditmemoTotals
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfigurationHelper
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockRepository
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface
     * @param \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $orderItemRenderer
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @param \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollection
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection $voteCollection
     * @param \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer $invoiceItemRenderer
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection $purchasedLinkCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory $invoiceItemCollectionFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection $purchasedLinkItemCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $productCollectionFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Checkout\Model\Session|null $checkoutSession
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Sales\Model\Order\Pdf\Invoice $invoicePdf
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceTokenFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param Filesystem $fileSystem
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Webkul\MobikulCore\Model\UserImageFactory $userImageFactory
     * @param \Webkul\MobikulCore\Helper\Upload $uploadHelper
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditRepoInterface
     * @param CollectionFactory $creditmemoItemCollectionFactory
     * @param \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory
     * @param \Magento\Sales\Model\Reorder\Reorder $reorder
     */
    public function __construct(
        \Magento\Store\Model\Store $store,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Sales\Model\Order $order,
        \Magento\Checkout\Model\Cart $cart, // need to be removed.
        \Magento\Review\Model\Review $review,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Customer\Model\Visitor $visitor,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Review\Helper\Data $reviewHelper,
        \Magento\Directory\Model\Country $country,
        \Magento\Wishlist\Model\Item $wishlistItem,
        \Magento\Customer\Model\Form $customerForm,
        \Magento\Sales\Helper\Reorder $salesHelper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Webkul\MobikulCore\Helper\Token $tokenHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Webkul\MobikulCore\Model\UserImage $userImage,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Review\Model\Rating\Option\Vote $vote,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Sales\Block\Order\Info $orderInfoBlock,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Wishlist\Model\WishlistFactory $wishlist,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Wishlist\Model\Wishlist $wishlistProvider,
        \Magento\Downloadable\Model\Link $downloadableLink,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Sales\Block\Order\History $orderHistoryBlock,
        \Magento\Downloadable\Helper\Data $downloadableHelper,
        \Magento\Weee\Block\Item\Price\Renderer $priceRenderer,
        \Magento\Security\Model\ConfigInterface $securityConfig,
        \Magento\Catalog\Helper\Product\Compare $productCompare,
        \Magento\Wishlist\Model\Item\Option $wishlistItemOption,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Review\Block\Customer\View $customerReviewBlock,
        \Webkul\MobikulApiGraphQl\Block\Sales\Order\Totals $orderTotals,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Block\Customer\ListCustomer $customerList,
        \Magento\Downloadable\Helper\File $downloadableFileHelper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Downloadable\Model\Link\Purchased $purchasedlink,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Customer\Model\CustomerExtractor $customerExtractor,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Downloadable\Model\Link\Purchased\Item $purchasedLinkItem,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Webkul\MobikulApiGraphQl\Block\Sales\Order\Invoice\Totals $invoiceTotals,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepoInterface,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Shipping\Block\Adminhtml\Order\Tracking\View $shippingView,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $compareItem,
        \Magento\Weee\Block\Adminhtml\Items\Price\Renderer $adminPriceRenderer,
        \Magento\Downloadable\Block\Customer\Products\ListProducts $listProduct,
        \Webkul\MobikulApiGraphQl\Block\Sales\Order\Creditmemo\Totals $creditmemoTotals,
        \Magento\Catalog\Helper\Product\Configuration $productConfigurationHelper,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface,
        \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $orderItemRenderer,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollection,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection $voteCollection,
        \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer $invoiceItemRenderer,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection $purchasedLinkCollection,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory $invoiceItemCollectionFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection $purchasedLinkItemCollection,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $productCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Checkout\Model\Session $checkoutSession = null,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Sales\Model\Order\Pdf\Invoice $invoicePdf,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceTokenFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Webkul\MobikulCore\Model\UserImageFactory $userImageFactory,
        \Webkul\MobikulCore\Helper\Upload $uploadHelper,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditRepoInterface,
        CollectionFactory $creditmemoItemCollectionFactory,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,
        \Magento\Sales\Model\Reorder\Reorder $reorder
    ) {
        $this->checkoutSession = $checkoutSession == null ?
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Checkout\Model\Session::class
            ) : $checkoutSession;
        $this->cart = $cart;
        $this->vote = $vote;
        $this->store = $store;
        $this->quote = $quote;
        $this->order = $order;
        $this->helper = $helper;
        $this->review = $review;
        $this->emulate = $emulate;
        $this->visitor = $visitor;
        $this->request = $request;
        $this->country = $country;
        $this->baseDir = $dir->getPath("media");
        $this->wishlist = $wishlist;
        $this->userImage = $userImage;
        $this->encryptor = $encryptor;
        $this->localeDate = $localeDate;
        $this->jsonHelper = $jsonHelper;
        $this->tokenHelper = $tokenHelper;
        $this->salesHelper = $salesHelper;
        $this->orderTotals = $orderTotals;
        $this->cartFactory = $cartFactory;
        $this->compareItem = $compareItem;
        $this->orderConfig = $orderConfig;
        $this->listProduct = $listProduct;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->customerForm = $customerForm;
        $this->reviewHelper = $reviewHelper;
        $this->shippingView = $shippingView;
        $this->customerList = $customerList;
        $this->quoteFactory = $quoteFactory;
        $this->wishlistItem = $wishlistItem;
        $this->ratingFactory = $ratingFactory;
        $this->pricingHelper = $pricingHelper;
        $this->reviewFactory = $reviewFactory;
        $this->catalogConfig = $catalogConfig;
        $this->addressHelper = $addressHelper;
        $this->helperCatalog = $helperCatalog;
        $this->stockRegistry = $stockRegistry;
        $this->purchasedlink = $purchasedlink;
        $this->priceRenderer = $priceRenderer;
        $this->productHelper = $productHelper;
        $this->invoiceTotals = $invoiceTotals;
        $this->securityConfig = $securityConfig;
        $this->productFactory = $productFactory;
        $this->orderInfoBlock = $orderInfoBlock;
        $this->wishlistHelper = $wishlistHelper;
        $this->voteCollection = $voteCollection;
        $this->productCompare = $productCompare;
        $this->customerAddress = $customerAddress;
        $this->stockRepository = $stockRepository;
        $this->orderCollection = $orderCollection;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->downloadableLink = $downloadableLink;
        $this->wishlistProvider = $wishlistProvider;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->reviewCollection = $reviewCollection;
        $this->creditmemoTotals = $creditmemoTotals;
        $this->transportBuilder = $transportBuilder;
        $this->purchasedLinkItem = $purchasedLinkItem;
        $this->collectionFactory = $collectionFactory;
        $this->productVisibility = $productVisibility;
        $this->productRepository = $productRepository;
        $this->accountManagement = $accountManagement;
        $this->inlineTranslation = $inlineTranslation;
        $this->addressRepository = $addressRepository;
        $this->customerExtractor = $customerExtractor;
        $this->countryCollection = $countryCollection;
        $this->invoiceRepository = $invoiceRepoInterface;
        $this->orderHistoryBlock = $orderHistoryBlock;
        $this->orderItemRenderer = $orderItemRenderer;
        $this->downloadableHelper = $downloadableHelper;
        $this->wishlistItemOption = $wishlistItemOption;
        $this->adminPriceRenderer = $adminPriceRenderer;
        $this->invoiceItemRenderer = $invoiceItemRenderer;
        $this->customerReviewBlock = $customerReviewBlock;
        $this->downloadableFileHelper = $downloadableFileHelper;
        $this->purchasedLinkCollection = $purchasedLinkCollection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductCompareList = $catalogProductCompareList;
        $this->productConfigurationHelper = $productConfigurationHelper;
        $this->shipmentRepositoryInterface = $shipmentRepositoryInterface;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->purchasedLinkItemCollection = $purchasedLinkItemCollection;
        $this->invoiceItemCollectionFactory = $invoiceItemCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->customerMapper = $customerMapper;
        $this->fileDriver = $fileDriver;
        $this->invoicePdf = $invoicePdf;
        $this->fileFactory = $fileFactory;
        $this->date = $date;
        $this->deviceTokenFactory = $deviceTokenFactory;
        $this->coreSession = $coreSession;
        $this->fileSystem = $fileSystem;
        $this->ioFile = $ioFile;
        $this->userImageFactory = $userImageFactory;
        $this->uploadHelper = $uploadHelper;
        $this->creditRepository = $creditRepoInterface;
        $this->creditmemoItemCollectionFactory = $creditmemoItemCollectionFactory;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->reorder = $reorder;
        parent::__construct($helper, $request, $jsonHelper);
    }
}
