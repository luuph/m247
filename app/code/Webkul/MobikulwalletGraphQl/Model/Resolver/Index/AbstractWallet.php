<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_MobikulwalletGraphQl
    * @author    Webkul Software Private Limited
    * @copyright Copyright (c)Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\MobikulwalletGraphQl\Model\Resolver\Index;
    use Magento\Sales\Model\Order;
    use Webkul\MobikulCore\Helper\Data;
    use Webkul\MobikulCore\Helper\Catalog;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Context;
    use Magento\Quote\Model\Quote\ItemFactory;
    use Magento\Quote\Model\Quote;
    use Magento\Framework\Unserialize\Unserialize;
    use Webkul\Walletsystem\Model\WalletUpdateData;
    use Webkul\Walletsystem\Model\WalletTransferData;
    use Webkul\Walletsystem\Helper\Mail as WalletMail;
    use Webkul\Walletsystem\Helper\Data as WalletHelper;
    use Webkul\Walletsystem\Model\WallettransactionFactory;
    use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
    use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
    use Webkul\Walletsystem\Model\WalletPayeeFactory;
    use Magento\Store\Model\StoreManagerInterface;
    use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;

    abstract class AbstractWallet extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController    {
        /**
         * Currency header static value
         */
        protected const CURRENT_CURRENCY = 'Current-Currency';
        /**
         * @var \Magento\Store\Model\StoreManagerInterface
         */
        protected $storeManager;
        protected $_date;
        protected $_order;
        protected $_baseDir;
        protected $_emulate;
        protected $_encryptor;
        protected $_walletMail;
        protected $_localeDate;
        protected $_priceFormat;
        protected $_itemFactory;
        protected $_unserialize;
        protected $_cartFactory;
        protected $_imageFactory;
        protected $_walletHelper;
        protected $_moduleReader;
        protected $_quoteFactory;
        protected $_walletUpdate;
        protected $_priceCurrency;
        protected $_waletTransfer;
        protected $_helperCatalog;
        protected $_productFactory;
        protected $_customerSession;
        protected $_checkoutSession;
        protected $_customerFactory;
        protected $_walletTransaction;
        protected $_walletrecordModel;
        protected $_customerCollection;
        protected $_customerRepository;
        protected $accountDetails;
        protected $_wallettransactionModel;
        protected $walletPayee;
        protected $_walletNotification;
        protected $store;
        protected $_helper;
        protected $scopeConfig;
        protected $quote;
        protected $accountResourceModel;

        public function __construct(
            Data $helper,
            Order $order,
            Context $context,
            Quote $quote,
            Emulation $emulate,
            Encryptor $encryptor,
            WalletMail $walletMail,
            Catalog $helperCatalog,
            Unserialize $unserialize,
            ItemFactory $itemFactory,
            WalletHelper $walletHelper,
            TimezoneInterface $localeDate,
            WalletUpdateData $walletUpdate,
            WalletPayeeFactory $walletPayee,
            WalletTransferData $walletTransfer,
            StoreManagerInterface $storeManager,
            CustomerCollection $customerCollection,
            WallettransactionFactory $walletTransaction,
            \Magento\Framework\App\RequestInterface $request,
            \Magento\Store\Model\Store $store,
            \Magento\Framework\Image\Factory $imageFactory,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Quote\Model\QuoteFactory $quoteFactory,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\Filesystem\DirectoryList $dir,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Checkout\Model\CartFactory $cartFactory,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            \Magento\Framework\Module\Dir\Reader $moduleReader,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Webkul\Walletsystem\Model\WalletNotification $walletNotification,
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Webkul\Walletsystem\Model\AccountDetails $accountDetails,
            \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
            \Webkul\Walletsystem\Model\ResourceModel\Walletrecord\CollectionFactory $walletrecordModel,
            \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $wallettransactionModel,
            \Magento\Framework\Pricing\Helper\Data $priceFormat,
            \Webkul\Walletsystem\Model\ResourceModel\AccountDetails\CollectionFactory $accountResourceModel
        ) {
            $this->_date                   = $date;
            $this->store                   = $store;
            $this->_walletNotification     = $walletNotification;
            $this->_order                  = $order;
            $this->_helper                 = $helper;
            $this->scopeConfig             = $scopeConfig;
            $this->walletPayee             = $walletPayee;
            $this->_baseDir                = $dir->getPath("media");
            $this->_emulate                = $emulate;
            $this->_encryptor              = $encryptor;
            $this->_localeDate             = $localeDate;
            $this->_walletMail             = $walletMail;
            $this->_unserialize            = $unserialize;
            $this->_itemFactory            = $itemFactory;
            $this->_cartFactory            = $cartFactory;
            $this->_priceFormat            = $priceFormat;
            $this->storeManager            = $storeManager;
            $this->_quoteFactory           = $quoteFactory;
            $this->_imageFactory           = $imageFactory;
            $this->_walletHelper           = $walletHelper;
            $this->_walletUpdate           = $walletUpdate;
            $this->_moduleReader           = $moduleReader;
            $this->_priceCurrency          = $priceCurrency;
            $this->_waletTransfer          = $walletTransfer;
            $this->accountDetails          = $accountDetails;
            $this->_helperCatalog          = $helperCatalog;
            $this->_productFactory         = $productFactory;
            $this->_customerSession        = $customerSession;
            $this->_checkoutSession        = $checkoutSession;
            $this->_customerFactory        = $customerFactory;
            $this->_walletTransaction      = $walletTransaction;
            $this->_walletrecordModel      = $walletrecordModel;
            $this->_customerCollection     = $customerCollection;
            $this->_customerRepository     = $customerRepository;
            $this->_wallettransactionModel = $wallettransactionModel;
            $this->quote = $quote;
            $this->accountResourceModel = $accountResourceModel;
            parent::__construct($helper, $request, $jsonHelper);
        }

        public function stripTags($data) {
            return strip_tags($data);
        }

        public function resizeNCache($basePath, $newPath, $width, $height, $forCustomer=false)   {
            if (!is_file($newPath) || $forCustomer) {
                $imageObj = $this->_imageFactory->create($basePath);
                $imageObj->keepAspectRatio(false);
                $imageObj->backgroundColor([255, 255, 255]);
                $imageObj->keepFrame(false);
                $imageObj->resize($width, $height);
                $imageObj->save($newPath);
            }
        }

    }
