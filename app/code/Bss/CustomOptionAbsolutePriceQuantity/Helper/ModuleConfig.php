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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Helper;

use Bss\CustomOptionAbsolutePriceQuantity\Plugin\PriceType;

class ModuleConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var integer
     */
    private $storeId;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $taxHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $magentoVersion;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * ModuleConfig constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Framework\App\ProductMetadataInterface $magentoVersion
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\App\ProductMetadataInterface $magentoVersion,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\State $state
    ) {
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
        $this->customerSession = $customerSession;
        $this->magentoVersion = $magentoVersion;
        $this->moduleManager = $moduleManager;
        $this->state = $state;
        parent::__construct($context);
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function checkModuleInstall($moduleName)
    {
        return $this->moduleManager->isEnabled($moduleName);
    }

    /**
     * @return bool
     */
    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(
            'coapnqty_config/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return bool
     */
    public function allowQtyReport()
    {
        return $this->isModuleEnable() && $this->scopeConfig->getValue(
            'coapnqty_config/general/allow_qty_report',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            // in admin area
            $storeId = (int) $this->_request->getParam('store', 0);
        } else {
            // frontend area
            $storeId = $this->storeId = $this->storeManager->getStore()->getId();
        }
        return $storeId;
    }
    /**
     * @return string
     */
    public function getTooltipMessage()
    {
        return $this->scopeConfig->getValue(
            'coapnqty_config/tooltip/message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @param string $priceType
     * @return bool
     */
    public function isEnableTooltip($priceType = PriceType::ABSOLUTE_PRICETYPE)
    {
        $result = $this->scopeConfig->getValue(
            'coapnqty_config/tooltip/enabled_tooltip',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return $this->isModuleEnable() && $priceType === PriceType::ABSOLUTE_PRICETYPE && $result;
    }

    /**
     * @return bool
     */
    public function isPriceInclTax()
    {
        return $this->taxHelper->priceIncludesTax($this->getStoreId());
    }

    /**
     * @return bool
     */
    public function displayCartPriceInclTax()
    {
        return $this->taxHelper->displayCartPriceInclTax($this->getStoreId());
    }

    /**
     * @return bool
     */
    public function displayCartPriceExclTax()
    {
        return $this->taxHelper->displayCartPriceExclTax($this->getStoreId());
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->customerSession->create()->getCustomerGroupId();
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerSession->create()->getCustomerId();
    }

    /**
     * @return bool
     *
     * @deprecated v1.1.9
     */
    public function checkCustomerExist()
    {
        return $this->customerSession->create()->isLoggedIn();
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->magentoVersion->getVersion();
    }

    /**
     * @return int
     */
    public function getPriceDisplayType()
    {
        return $this->taxHelper->getPriceDisplayType($this->getStoreId());
    }

    /**
     * @return mixed
     */
    public function checkTypeProductPriceDisplay()
    {
        $result = $this->scopeConfig->getValue(
            'tax/display/type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return $result;
    }

    /**
     * @return bool
     */
    public function isBssOptionImageEnable()
    {
        return $this->scopeConfig->getValue(
            'bss_coi/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config Catalog Prices. 0 is Excl Tax, 1 is Incl Tax
     *
     * @param int $websiteId
     * @return int
     */
    public function getConfigCatalogPrices($websiteId = null)
    {
        return (int)$this->scopeConfig->getValue(
            \Magento\Tax\Model\Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
