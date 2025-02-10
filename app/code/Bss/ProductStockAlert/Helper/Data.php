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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magento\Framework\App\ProductMetadataInterface;

class Data extends \Magento\Framework\Url\Helper\Data
{
    /**
     * Error email template configuration
     */
    const XML_PATH_ERROR_TEMPLATE = 'bss_productstockalert/productstockalert_cron/error_email_template';

    /**
     * Error email identity configuration
     */
    const XML_PATH_ERROR_IDENTITY = 'bss_productstockalert/productstockalert_cron/error_email_identity';

    /**
     * 'Send error emails to' configuration
     */
    const XML_PATH_ERROR_RECIPIENT = 'bss_productstockalert/productstockalert_cron/error_email';

    /**
     * Allow stock alert
     *
     */
    const XML_PATH_STOCK_ALLOW = 'bss_productstockalert/productstockalert/allow_stock';

    /**
     * Customer group allow
     *
     */
    const XML_PATH_CUSTOMER_ALLOW = 'bss_productstockalert/productstockalert/allow_customer';

    /**
     * allow email based qty
     *
     */
    const XML_PATH_EMAIL_SEND_BASED_QTY = 'bss_productstockalert/productstockalert/email_based_qty';

    /**
     * Limit send count
     *
     */
    const XML_PATH_SEND_LIMIT = 'bss_productstockalert/productstockalert/send_limit';

    /**
     * Qty allow send
     *
     */
    const XML_PATH_QTY_ALLOW = 'bss_productstockalert/productstockalert/allow_stock_qty';

    /**
     * notification message
     *
     */
    const XML_PATH_NOTIFICATION_MESSAGE = 'bss_productstockalert/productstockalert/message';

    /**
     * stop notification message
     *
     */
    const XML_PATH_STOP_NOTIFICATION_MESSAGE = 'bss_productstockalert/productstockalert/stop_message';

    /**
     * Button design
     */
    const XML_BUTTON_DESIGN_BUTTON_TEXT = 'bss_productstockalert/button_design/button_text';
    const XML_BUTTON_DESIGN_STOP_BUTTON_TEXT = 'bss_productstockalert/button_design/stop_button_text';
    const XML_BUTTON_DESIGN_BUTTON_TEXT_COLOR = 'bss_productstockalert/button_design/button_text_color';
    const XML_BUTTON_DESIGN_BUTTON_COLOR = 'bss_productstockalert/button_design/button_color';

    /**
     * Checkout cart
     */
    const CONFIG_THUMBNAIL_CONFIGURABLE_SOURCE = 'checkout/cart/configurable_product_image';
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/grouped_product_image';

    /**
     * Price Alert
     */
    const XML_PATH_ALLOW_PRICE_ALERT = 'bss_productstockalert/productpricealert/allow_price_alert';
    const XML_PATH_PRICE_ALLOW_CUSTOMER_GROUP = 'bss_productstockalert/productpricealert/allow_customer_group';
    const XML_PATH_PRICE_ALLOW_PRODUCT_TYPE = 'bss_productstockalert/productpricealert/allow_product_type';
    const XML_PATH_EMAIL_PRICE_INFO = 'bss_productstockalert/productpricealert/email_sender';
    const XML_PATH_PRICE_ALERT_EMAIL_SENDER = 'bss_productstockalert/productpricealert/price_alert_email_sender';
    const XML_PATH_NOTIFICATION_PRICE_MESSAGE = 'bss_productstockalert/productpricealert/notify_mess';
    const XML_PATH_STOP_NOTIFICATION_PRICE_MESSAGE = 'bss_productstockalert/productpricealert/stop_notify_mess';
    const XML_PATH_SEND_LIMIT_PRICE = 'bss_productstockalert/productpricealert/send_limit';

    /**
     * Current product instance (override registry one)
     *
     * @var null|\Magento\Catalog\Model\Product
     */
    protected $product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /** @var \Magento\Store\Model\StoreManagerInterface */

    protected $storeManager;

    /**
     * @var
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Bss\ProductStockAlert\Model\Stock
     */
    protected $model;

    /**
     * @var \Bss\ProductStockAlert\Model\PriceAlert
     */
    protected $modelPriceAlert;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var \Bss\ProductStockAlert\Model\Form\FormKey
     */
    protected $formKey;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param CustomerSession $customerSession
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Bss\ProductStockAlert\Model\Stock $model
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     * @param \Bss\ProductStockAlert\Model\Form\FormKey $formKey
     * @param UrlInterface $backendUrl
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        CustomerSession $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Bss\ProductStockAlert\Model\Stock $model,
        \Bss\ProductStockAlert\Model\PriceAlert $modelPriceAlert,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Bss\ProductStockAlert\Model\Form\FormKey $formKey,
        UrlInterface $backendUrl,
        ProductMetadataInterface $productMetadata
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->customer = $customer;
        $this->model = $model;
        $this->modelPriceAlert = $modelPriceAlert;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->formKey = $formKey;
        $this->backendUrl = $backendUrl;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Url\EncoderInterface
     */
    public function returnUrlEncode()
    {
        return $this->urlEncoder;
    }

    /**
     * Get current product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if ($this->product !== null) {
            return $this->product;
        }
        return $this->coreRegistry->registry('product');
    }

    /**
     * Set current product instance
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Bss\ProductStockAlert\Helper\Data
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getSaveUrl($type)
    {
        return $this->_getUrl(
            'productstockalert/add/' . $type,
            [
                'product_id' => $this->getProduct()->getId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
            ]
        );
    }

    /**
     * Function call cron run at back-end only
     *
     * @return string
     */
    public function getCronUrl()
    {
        $key = $this->getSecurityKey();
        return $this->backendUrl->getUrl(
            'productstockalert/cron/index',
            [
                'form_key' => $key
            ]
        );
    }

    /**
     * @return string
     */
    public function getSecurityKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @param int $product_id
     * @return string
     */
    public function getPostAction($product_id)
    {
        return $this->_getUrl(
            'productstockalert/add/stock',
            [
                'product_id' => $product_id,
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
            ]
        );
    }

    /**
     * Url get all product alert.
     *
     * @return string
     */
    public function getUrlAllProductAlert()
    {
        return $this->_getUrl('productstockalert/ajax/sessionproductalert');
    }

    /**
     * Get link cancel btn.
     *
     * @param int $product_id
     * @param int|null $parentId
     * @return string
     */
    public function getCancelPostAction($product_id, $parentId = null)
    {
        if (!$parentId) {
            $params = [
                'product_id' => $product_id,
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
            ];
        } else {
            $params = [
                'product_id' => $product_id,
                'parent_id' => $parentId,
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
            ];
        }

        return $this->_getUrl(
            'productstockalert/unsubscribe/stock',
            $params
        );
    }

    /**
     * @param string|null $store
     * @return bool
     */
    public function isStockAlertAllowed($store = null)
    {
        $allow = $this->scopeConfig->isSetFlag(
            self::XML_PATH_STOCK_ALLOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        $allowCustomer = $this->checkCustomer();
        return $allow && $allowCustomer;
    }

    /**
     * @return mixed
     */
    public function getNotificationMessage()
    {
        $message = $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFICATION_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$message) {
            return __('Notify me when this product is in stock');
        }
        return $message;
    }

    /**
     * @return mixed
     */
    public function getStopNotificationMessage()
    {
        $message = $this->scopeConfig->getValue(
            self::XML_PATH_STOP_NOTIFICATION_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$message) {
            return __('Stop Notify this product');
        }
        return $message;
    }

    /**
     * Check current customer
     *
     * @param int|string|null $customerGroupId
     * @param string|null $path
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkCustomer($customerGroupId = null, $path = self::XML_PATH_CUSTOMER_ALLOW)
    {
        $customerConfig = $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($customerConfig != '') {
            $customerConfigArr = explode(',', $customerConfig);
            if ($customerGroupId !== null) {
                if (in_array($customerGroupId, $customerConfigArr)) {
                    return true;
                }
            } elseif ($this->customerSession->create()->isLoggedIn()) {
                $customerGroupId = $this->customerSession->create()->getCustomerGroupId();
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
     * Check product type.
     *
     * @param string $productType
     * @param string $websiteId
     * @return bool
     */
    public function checkProductType($productType, $websiteId = null)
    {
        $productTypeConfig = $this->scopeConfig->getValue(
            self::XML_PATH_PRICE_ALLOW_PRODUCT_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        if ($productTypeConfig) {
            $productTypeArr = explode(',', $productTypeConfig);
            if (in_array($productType, $productTypeArr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null $store
     * @return mixed
     */
    public function getEmailSendBasedQty($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SEND_BASED_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string|null $store
     * @return mixed
     */
    public function getLimitCount($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEND_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * get qty product to send email
     *
     * @return int
     */
    public function getQtySendMail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_QTY_ALLOW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return string
     */
    public function getEmailErrorTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ERROR_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getEmailErrorIdentity()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ERROR_IDENTITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getEmailErrorRecipient()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ERROR_RECIPIENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getCustomerEmail()
    {
        if ($this->customerSession->create()->isLoggedIn()) {
            $customerData = $this->customer->load($this->customerSession->create()->getCustomerId());
            return $customerData->getEmail();
        }
        return "";
    }

    /**
     * @return bool|int|null
     */
    public function getCustomerId()
    {
        if ($this->customerSession->create()->isLoggedIn()) {
            return $this->customerSession->create()->getCustomerId();
        }
        return false;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @param string $productId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasEmail($productId)
    {
        if (!$productId) {
            return false;
        }
        if ($this->getCustomerId()) {
            return $this->model->hasEmail(
                $this->getCustomerId(),
                $productId,
                $this->storeManager->getStore()->getWebsiteId()
            );
        }
        $notify = $this->customerSession->create()->getNotifySubscription();
        return isset($notify[$productId]);
    }

    /**
     * @param string $productId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasEmailPrice($productId)
    {
        if ($this->getCustomerId()) {
            return $this->modelPriceAlert->hasEmail(
                $this->getCustomerId(),
                $productId,
                $this->storeManager->getStore()->getWebsiteId()
            );
        }
        $notify = $this->customerSession->create()->getNotifyPriceSubscription();
        return isset($notify[$productId]);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isConfigThumbnail($product)
    {
        $sourceConfig = $product->getTypeId() == 'configurable' ?
            self::CONFIG_THUMBNAIL_CONFIGURABLE_SOURCE : self::CONFIG_THUMBNAIL_SOURCE;
        $isThumbnail = $this->scopeConfig->getValue(
            $sourceConfig,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $isThumbnail == \Magento\Catalog\Model\Config\Source\Product\Thumbnail::OPTION_USE_PARENT_IMAGE;
    }

    /**
     * @return string
     */
    public function getModuleActionName()
    {
        return $this->_request->getModuleName();
    }

    /**
     * Get Button Config
     *
     * @param string $path
     * @param string $default
     * @param bool $phrase
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getButtonConfig($path, $default, $phrase = false)
    {
        $btnConfig = $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$btnConfig) {
            return $default;
        }
        if ($phrase) {
            return __($btnConfig);
        }
        return $btnConfig;
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getButtonText()
    {
        return $this->getButtonConfig(self::XML_BUTTON_DESIGN_BUTTON_TEXT, __('Notify Me'), true);
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getStopButtonText()
    {
        return $this->getButtonConfig(self::XML_BUTTON_DESIGN_STOP_BUTTON_TEXT, __('Stop notify'), true);
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getButtonTextColor()
    {
        return $this->getButtonConfig(self::XML_BUTTON_DESIGN_BUTTON_TEXT_COLOR, '#FFFFFF');
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getButtonColor()
    {
        return $this->getButtonConfig(self::XML_BUTTON_DESIGN_BUTTON_COLOR, '#2D7DB3');
    }

    /**
     * @return bool
     */
    public function isEnabledPreOrder()
    {
        $configPreOrder = $this->scopeConfig->getValue(
            'preorder/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $modulePreOrder = $this->_moduleManager->isEnabled('Bss_PreOrder');
        return $configPreOrder && $modulePreOrder;
    }

    /**
     * @return string
     */
    public function getGmtDate()
    {
        return $this->dateTimeFactory->create()->gmtDate();
    }

    /**
     * Get store
     *
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function getStores()
    {
        return $this->storeManager->getStores();
    }

    /**
     * Check is magento 2.4.x
     *
     * @return bool|int
     */
    public function isMagento24x()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        return version_compare($magentoVersion, '2.4.0', '>=');
    }

    /**
     * Check is magento EE
     *
     * @return bool|int
     */
    public function isEnterpriseEdition()
    {
        return $this->productMetadata->getEdition() != 'CE' &&
            $this->productMetadata->getEdition() != 'Community';
    }

    /**
     * Check enable config price alert.
     *
     * @param string|null $websiteId
     * @return mixed
     */
    public function isEnablePriceAlert($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ALLOW_PRICE_ALERT,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Check enable config price alert and check customer group.
     *
     * @param string|null $websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnablePriceAlertAndCustomer($websiteId = null)
    {
        $allow = $this->isEnablePriceAlert($websiteId);

        if ($allow) {
            return $this->checkCustomer(null, self::XML_PATH_PRICE_ALLOW_CUSTOMER_GROUP);
        }

        return false;
    }

    /**
     * Get count limit email price alert.
     *
     * @param string $websiteId
     * @return mixed
     */
    public function getLimitEmailPrice($websiteId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_SEND_LIMIT_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        ) ?: 1;
    }

    /**
     * Get email sender.
     *
     * @param string|null $storeId
     * @return mixed
     */
    public function getEmailPriceInfo($storeId = null)
    {
        $sender = $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_PRICE_INFO,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$sender) {
            $sender = 'general';
        }

        return $sender;
    }

    /**
     * Get email template
     *
     * @param string|null $storeId
     * @return mixed|string
     */
    public function getEmailPriceTemplate($storeId = null)
    {
        $config = $this->scopeConfig->getValue(
            self::XML_PATH_PRICE_ALERT_EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$config) {
            $config = 'bss_productstockalert_productpricealert_price_alert_email_sender';
        }

        return $config;
    }

    /**
     * Get notify mess
     *
     * @param string $storeId
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getNotifyPriceMessage($storeId = null)
    {
        $message = $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFICATION_PRICE_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$message) {
            return __('Sign up for price alert');
        }
        return $message;
    }

    /**
     * Get stop notify mess
     *
     * @param string $storeId
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getStopNotifyPriceMessage($storeId = null)
    {
        $message = $this->scopeConfig->getValue(
            self::XML_PATH_STOP_NOTIFICATION_PRICE_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$message) {
            return __('Stop price alert for this product');
        }
        return $message;
    }

    /**
     * Get email sender name
     *
     * @param string $storeId
     * @return string
     */
    public function getEmailPriceName($storeId = null)
    {
        $emailConfig = $this->getEmailPriceInfo($storeId);

        return (string)$this->scopeConfig->getValue(
            "trans_email/ident_" . $emailConfig . "/name",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get email sender name
     *
     * @param string $emailConfig
     * @param string $storeId
     * @return string
     */
    public function getEmailPriceEmail($emailConfig = null, $storeId = null)
    {
        if ($emailConfig == null) {
            $emailConfig = $this->getEmailPriceInfo($storeId);
        }

        return (string)$this->scopeConfig->getValue(
            "trans_email/ident_" . $emailConfig . "/email",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
