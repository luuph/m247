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

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Checkout;

use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\MobikulCore\Helper\Data as HelperData;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Quote\Model\Quote\Address\ToOrder as ToOrderConverter;
use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ToOrderAddressConverter;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use \Magento\Checkout\Model\Cart\RequestInfoFilterInterface;

/**
 * Abstract Class Abstract Checkout
 */
abstract class AbstractCheckout extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController
{
    /**
     * Store variable
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * Cctype variable
     *
     * @var \Magento\Payment\Model\Source\Cctype
     */
    protected $ccType;

    /**
     * Country variable
     *
     * @var \Magento\Directory\Model\Country
     */
    protected $country;

    /**
     * Emulation variable
     *
     * @var Emulation
     */
    protected $emulate;

    /**
     * Escaper variable
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Wishlist variable
     *
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $wishlist;

    /**
     * JsonHelper variable
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * StockState variable
     *
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * OrderSender variable
     *
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * PriceHelper variable
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * DeviceToken variable
     *
     * @var \Webkul\MobikulCore\Model\DeviceToken
     */
    protected $deviceToken;

    /**
     * CartFactory variable
     *
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $cartFactory;

    /**
     * CustomerUrl variable
     *
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * CatalogLink variable
     *
     * @var \Magento\Catalog\Model\Product\LinkFactory
     */
    protected $catalogLink;

    /**
     * SalesHelper variable
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $salesHelper;

    /**
     * OrderFactory variable
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * QuoteFactory variable
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * CoreRegistry variable
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * EventManager variable
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * StoreManager variable
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CustomerForm variable
     *
     * @var \Magento\Customer\Model\Form
     */
    protected $customerForm;

    /**
     * PaypalConfig variable
     *
     * @var \Magento\Paypal\Model\Config
     */
    protected $paypalConfig;

    /**
     * MobikulOrder variable
     *
     * @var \Webkul\MobikulCore\Model\SalesOrderFactory
     */
    protected $mobikulOrder;

    /**
     * HelperCatalog variable
     *
     * @var \Webkul\MobikulCore\Helper\Catalog
     */
    protected $helperCatalog;

    /**
     * AddressHelper variable
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $addressHelper;

    /**
     * InvoiceSender variable
     *
     * @var Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * ProductOption variable
     *
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $productOption;

    /**
     * CouponFactory variable
     *
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * DbTransaction variable
     *
     * @var \Magento\Framework\DB\Transaction
     */
    protected $dbTransaction;

    /**
     * PaymentHelper variable
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * PaymentDetails variable
     *
     * @var \Magento\Checkout\Model\PaymentDetails
     */
    protected $paymentDetails;

    /**
     * CheckoutHelper variable
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * LocaleResolver variable
     *
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $localeResolver;

    /**
     * ProductFactory variable
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * QuoteValidator variable
     *
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $quoteValidator;

    /**
     * InvoiceService variable
     *
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * RelatedProducts variable
     *
     * @var \Magento\Quote\Model\Quote\Item\RelatedProducts
     */
    protected $relatedProducts;

    /**
     * CustomerFactory variable
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * CustomerAddress variable
     *
     * @var \Magento\Customer\Model\Address
     */
    protected $customerAddress;

    /**
     * QuoteRepository variable
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * CheckoutSession variable
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * QuoteManagement variable
     *
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;

    /**
     * AddressInterface variable
     *
     * @var \Magento\Quote\Api\Data\AddressInterface
     */
    protected $addressInterface;

    /**
     * RegionCollection variable
     *
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $regionCollection;

    /**
     * DataObjectHelper variable
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * OrderEmailSender variable
     *
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderEmailSender;

    /**
     * RequestInfoFilter variable
     *
     * @var mixed
     */
    protected $requestInfoFilter;

    /**
     * AccountManagement variable
     *
     * @var Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * CountryCollection variable
     *
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $countryCollection;

    /**
     * ObjectCopyService variable
     *
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $objectCopyService;

    /**
     * ProductVisibility variable
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * CustomerRepository variable
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * TransactionBuilder variable
     *
     * @var Transaction\BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * OrderCustomermanager variable
     *
     * @var \Magento\Sales\Api\OrderCustomerManagementInterface
     */
    protected $orderCustomermanager;

    /**
     * PaymentMethodInterface variable
     *
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodInterface;

    /**
     * ShippingMethodManagement variable
     *
     * @var \Magento\Quote\Model\ShippingMethodManagement
     */
    protected $shippingMethodManagement;

    /**
     * DownloadableConfiguration variable
     *
     * @var \Magento\Downloadable\Helper\Catalog\Product\Configuration
     */
    protected $downloadableConfiguration;

    /**
     * CatalogHelper variable
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * ProductRepository variable
     *
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * RemoteAddress variable
     *
     * @var Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * Curl variable
     *
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * Resource variable
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    
    /**
     * StockRegistry variable
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * CustomerSession variable
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * QuoteIdMaskFactory variable
     *
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * CustomerManagement variable
     *
     * @var \Magento\Quote\Model\CustomerManagement
     */
    protected $customerManagement;

    /**
     * QuoteAddressToOrder variable
     *
     * @var Magento\Quote\Model\Quote\Address\ToOrder
     */
    protected $quoteAddressToOrder;

    /**
     * QuoteItemToOrderItem variable
     *
     * @var Magento\Quote\Model\Quote\Item\ToOrderItem
     */
    protected $quoteItemToOrderItem;

    /**
     * ItemCollectionFactory variable
     *
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * PriceCurrencyInterface variable
     *
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrencyInterface;

    /**
     * QuotePaymentToOrderPayment variable
     *
     * @var Magento\Quote\Model\Quote\Payment\ToOrderPayment
     */
    protected $quotePaymentToOrderPayment;

    /**
     * QuoteAddressToOrderAddress variable
     *
     * @var Magento\Quote\Model\Quote\Address\ToOrderAddress
     */
    protected $quoteAddressToOrderAddress;

    /**
     * FileDriver variable
     *
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * Base64Json variable
     *
     * @var \Magento\Framework\Serialize\Serializer\Base64Json
     */
    protected $base64Json;

    /**
     * RequestInfoFilterInterface variable
     *
     * @var RequestInfoFilterInterface
     */
    protected $requestInfoFilterInterface;

    /**
     * ValidateEmail variable
     *
     * @var \Laminas\Validator\EmailAddress
     */
    protected $validateEmail;

    /**
     * Constructor function
     *
     * @param Context $context
     * @param Emulation $emulate
     * @param HelperData $helper
     * @param OrderSender $orderSender
     * @param InvoiceSender $invoiceSender
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Framework\Escaper $escaper
     * @param ToOrderConverter $quoteAddressToOrder
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Directory\Model\Country $country
     * @param \Magento\Framework\Registry $coreRegistry
     * @param ToOrderItemConverter $quoteItemToOrderItem
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @param \Magento\Sales\Helper\Reorder $salesHelper
     * @param \Magento\Customer\Model\Form $customerForm
     * @param \Magento\Paypal\Model\Config $paypalConfig
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Source\Cctype $ccType
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\DB\Transaction $dbTransaction
     * @param \Magento\Customer\Model\Address $customerAddress
     * @param \Webkul\MobikulCore\Helper\Catalog $helperCatalog
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     * @param \Webkul\MobikulCore\Model\DeviceToken $deviceToken
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     * @param ToOrderAddressConverter $quoteAddressToOrderAddress
     * @param ToOrderPaymentConverter $quotePaymentToOrderPayment
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Catalog\Model\Product\Option $productOption
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Checkout\Model\PaymentDetails $paymentDetails
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Product\LinkFactory $catalogLink
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Webkul\MobikulCore\Model\SalesOrderFactory $mobikulOrder
     * @param \Magento\Quote\Api\Data\AddressInterface $addressInterface
     * @param \Magento\Quote\Model\CustomerManagement $customerManagement
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Quote\Model\Quote\Item\RelatedProducts $relatedProducts
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderEmailSender
     * @param \Magento\Quote\Model\ShippingMethodManagement $shippingMethodManagement
     * @param \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomermanager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyInterface
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodInterface
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollection
     * @param \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableConfiguration
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Catalog\Helper\Data|null $catalogHelper
     * @param ProductRepositoryInterface $productRepository
     * @param RemoteAddress $remoteAddress
     * @param RequestInfoFilterInterface $requestInfoFilterInterface
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\Serialize\Serializer\Base64Json $base64Json
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Laminas\Validator\EmailAddress $validateEmail
     */
    public function __construct(
        Context $context,
        Emulation $emulate,
        HelperData $helper,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\Escaper $escaper,
        ToOrderConverter $quoteAddressToOrder,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Directory\Model\Country $country,
        \Magento\Framework\Registry $coreRegistry,
        ToOrderItemConverter $quoteItemToOrderItem,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Sales\Helper\Reorder $salesHelper,
        \Magento\Customer\Model\Form $customerForm,
        \Magento\Paypal\Model\Config $paypalConfig,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Source\Cctype $ccType,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        AccountManagementInterface $accountManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Helper\Address $addressHelper,
        Transaction\BuilderInterface $transactionBuilder,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Magento\Customer\Model\Address $customerAddress,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Webkul\MobikulCore\Model\DeviceToken $deviceToken,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        ToOrderAddressConverter $quoteAddressToOrderAddress,
        ToOrderPaymentConverter $quotePaymentToOrderPayment,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Catalog\Model\Product\Option $productOption,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Checkout\Model\PaymentDetails $paymentDetails,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Product\LinkFactory $catalogLink,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Webkul\MobikulCore\Model\SalesOrderFactory $mobikulOrder,
        \Magento\Quote\Api\Data\AddressInterface $addressInterface,
        \Magento\Quote\Model\CustomerManagement $customerManagement,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Model\Quote\Item\RelatedProducts $relatedProducts,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderEmailSender,
        \Magento\Quote\Model\ShippingMethodManagement $shippingMethodManagement,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomermanager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyInterface,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodInterface,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollection,
        \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableConfiguration,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Helper\Data $catalogHelper = null,
        ProductRepositoryInterface $productRepository,
        RemoteAddress $remoteAddress,
        RequestInfoFilterInterface $requestInfoFilterInterface,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64Json,
        \Magento\Framework\App\RequestInterface $request,
        \Laminas\Validator\EmailAddress $validateEmail
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->catalogHelper = ($catalogHelper) ? $catalogHelper : $objectManager->get(
            \Magento\Catalog\Helper\Data::class
        );
        $this->curl = $curl;
        $this->store = $store;
        $this->ccType = $ccType;
        $this->country = $country;
        $this->emulate = $emulate;
        $this->escaper = $escaper;
        $this->wishlist = $wishlist;
        $this->resource = $resource;
        $this->stockState = $stockState;
        $this->jsonHelper = $jsonHelper;
        $this->salesHelper = $salesHelper;
        $this->catalogLink = $catalogLink;
        $this->priceHelper = $priceHelper;
        $this->cartFactory = $cartFactory;
        $this->customerUrl = $customerUrl;
        $this->deviceToken = $deviceToken;
        $this->orderSender = $orderSender;
        $this->mobikulOrder = $mobikulOrder;
        $this->paypalConfig = $paypalConfig;
        $this->eventManager = $eventManager;
        $this->customerForm = $customerForm;
        $this->coreRegistry = $coreRegistry;
        $this->orderFactory = $orderFactory;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->paymentHelper = $paymentHelper;
        $this->productOption = $productOption;
        $this->addressHelper = $addressHelper;
        $this->helperCatalog = $helperCatalog;
        $this->couponFactory = $couponFactory;
        $this->invoiceSender = $invoiceSender;
        $this->stockRegistry = $stockRegistry;
        $this->dbTransaction = $dbTransaction;
        $this->checkoutHelper = $checkoutHelper;
        $this->quoteValidator = $quoteValidator;
        $this->localeResolver = $localeResolver;
        $this->productFactory = $productFactory;
        $this->invoiceService = $invoiceService;
        $this->paymentDetails = $paymentDetails;
        $this->customerAddress = $customerAddress;
        $this->quoteManagement = $quoteManagement;
        $this->relatedProducts = $relatedProducts;
        $this->quoteRepository = $quoteRepository;
        $this->customerFactory = $customerFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->addressInterface = $addressInterface;
        $this->orderEmailSender = $orderEmailSender;
        $this->regionCollection = $regionCollection;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountManagement = $accountManagement;
        $this->productVisibility = $productVisibility;
        $this->objectCopyService = $objectCopyService;
        $this->countryCollection = $countryCollection;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->customerRepository = $customerRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->customerManagement = $customerManagement;
        $this->quoteAddressToOrder = $quoteAddressToOrder;
        $this->orderCustomermanager = $orderCustomermanager;
        $this->quoteItemToOrderItem = $quoteItemToOrderItem;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->paymentMethodInterface = $paymentMethodInterface;
        $this->priceCurrencyInterface = $priceCurrencyInterface;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->downloadableConfiguration = $downloadableConfiguration;
        $this->quotePaymentToOrderPayment = $quotePaymentToOrderPayment;
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
        $this->productRepository = $productRepository;
        $this->remoteAddress = $remoteAddress;
        $this->requestInfoFilterInterface = $requestInfoFilterInterface;
        $this->fileDriver = $fileDriver;
        $this->base64Json = $base64Json;
        $this->validateEmail = $validateEmail;
        parent::__construct($helper, $request, $jsonHelper);
    }

    /**
     * Function to check if customerEmail Exist
     *
     * @param string $email     email
     * @param int    $websiteId websiteId
     *
     * @return bool
     */
    protected function _customerEmailExists($email, $websiteId = null)
    {
        $customer = $this->customerFactory->create();
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    /**
     * Function to validate customer Data
     *
     * @param array $data data
     *
     * @return bool|array
     */
    protected function _validateCustomerData($data)
    {
        $storeId = $data["storeId"];
        $customerData = [];
        $customer = null;
        $customerForm = $this->customerForm->setFormCode("checkout_register");
        $quote = new \Magento\Framework\DataObject();
        $customerToken = $data["customerToken"] ?? "";
        $customerId = $this->helper->getCustomerByToken($customerToken) ?? 0;
        if ($customerId != 0) {
            $quote = $this->helper->getCustomerQuote($customerId);
        }
        if (isset($data["quoteId"]) && $data["quoteId"] != 0) {
            $quoteId = $data["quoteId"];
            $quote = $this->quoteFactory->create()->setStoreId($storeId)->load($quoteId);
        }
        if ($quote->getCustomerId()) {
            $customer = $quote->getCustomer();
            $customer = $this->customerFactory->create()->load($customer->getId());
            $customerForm->setEntity($customer);
            $customerData = $customer->getData();
        } else {
            $customer = $this->customerFactory->create();
            $customerForm->setEntity($customer);
            $newAddress = [];
            if (isset($data["billingData"])) {
                $billingData = $data["billingData"];
            } else {
                $billingData = $data["shippingData"];
            }

            if (isset($billingData["newAddress"])) {
                if (!empty($billingData["newAddress"])) {
                    $newAddress = $billingData["newAddress"];
                }
            }
            $customerData = [
                "lastname" => $newAddress["lastName"],
                "firstname" => $newAddress["firstName"],
                "dob" => $newAddress["dob"] ?? "",
                "email" => $newAddress["email"] ?? "",
                "prefix" => $newAddress["prefix"] ?? "",
                "suffix" => $newAddress["suffix"] ?? "",
                "taxvat" => $newAddress["taxvat"] ?? "",
                "gender" => $newAddress["gender"] ?? "",
                "middlename" => $newAddress["middleName"] ?? ""
            ];
        }
        $customerErrors = true;
        if ($customerErrors !== true) {
            return ["error"=>1, "message"=>implode(", ", $customerErrors)];
        }
        if ($quote->getCustomerId()) {
            return true;
        }
        if ($quote->getCheckoutMethod() == "register") {
            $customerForm->compactData($customerData);
            $customer->setPassword($data["password"]);
            $customer->setConfirmation($data["confirmPassword"]);
            $customer->setPasswordConfirmation($data["confirmPassword"]);
            $result = $customer->validate();
            if (true !== $result && is_array($result)) {
                return ["error"=>-1, "message"=>implode(", ", $result)];
            }
        }
        if ($quote->getCheckoutMethod() == "register") {
            $quote->setPasswordHash($customer->encryptPassword($customer->getPassword()));
            $quote->setCustomer($customer);
        }
        $quote->getBillingAddress()->setEmail($customer->getEmail());
        $this->objectCopyService->copyFieldsetToTarget("customer_account", "to_quote", $customer, $quote);
        return true;
    }
}
