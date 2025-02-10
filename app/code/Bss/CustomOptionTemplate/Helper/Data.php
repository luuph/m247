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
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Helper;

use Magento\Customer\Model\Customer\Source\GroupSourceInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_COMPATIBLE_CUSTOM_OPTION_IMAGE = 'bss_coi/general/enable';
    const XML_PATH_COMPATIBLE_DEPENDENT_CUSTOM_OPTION = 'dependent_co/general/enable';
    const XML_PATH_COMPATIBLE_ABSOLUTE_PRICE_QUANTIRY = 'coapnqty_config/general/active';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @var \Bss\CustomOptionTemplate\Model\OptionFactory
     */
    protected $bssOption;

    /**
     * @var \Bss\CustomOptionTemplate\Model\Option\ValueFactory
     */
    protected $bssOptionValue;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var GroupSourceInterface
     */
    protected $customerGroupSource;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroupColl;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\CustomOptionTemplate\Model\OptionFactory $bssOption
     * @param \Bss\CustomOptionTemplate\Model\Option\ValueFactory $bssOptionValue
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupColl
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param GroupSourceInterface $customerGroupSource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\CustomOptionTemplate\Model\OptionFactory $bssOption,
        \Bss\CustomOptionTemplate\Model\Option\ValueFactory $bssOptionValue,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupColl,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Directory\Helper\Data $directoryHelper,
        GroupSourceInterface $customerGroupSource
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->bssOption = $bssOption;
        $this->bssOptionValue = $bssOptionValue;
        $this->customerGroupColl = $customerGroupColl;
        $this->moduleManager = $moduleManager;
        $this->directoryHelper = $directoryHelper;
        $this->customerGroupSource = $customerGroupSource;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     */
    public function getCustomerGroupsArray()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }

        return $this->customerGroupSource->toOptionArray();
    }

    /**
     * @return mixed
     */
    public function getCurrencySymbol()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseCurrency()
            ->getCurrencySymbol();
    }

    /**
     * Get websites list
     *
     * @return array
     */
    public function getWebsitesArray()
    {
        $websites = [
            [
                'label' => __('All Websites') . ' [' . $this->directoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        $websitesList = $this->storeManager->getWebsites();
        foreach ($websitesList as $website) {
            $websites[] = [
                'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];
        }

        return $websites;
    }

    /**
     * @return bool
     */
    public function isCompatibleCOImage()
    {
        return $this->moduleManager->isEnabled('Bss_CustomOptionImage')
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_COMPATIBLE_CUSTOM_OPTION_IMAGE,
                ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * @return bool
     */
    public function isCompatibleDependentCO()
    {
        return $this->moduleManager->isEnabled('Bss_DependentCustomOption')
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_COMPATIBLE_DEPENDENT_CUSTOM_OPTION,
                ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * @return bool
     */
    public function isCompatibleAbsolutePriceQuantity()
    {
        return $this->moduleManager->isEnabled('Bss_CustomOptionAbsolutePriceQuantity')
        && $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMPATIBLE_ABSOLUTE_PRICE_QUANTIRY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return \Bss\CustomOptionTemplate\Model\Option
     */
    public function getObjectOption()
    {
        return $this->bssOption->create();
    }

    /**
     * @return \Bss\CustomOptionTemplate\Model\Option\Value
     */
    public function getObjectOptionValues()
    {
        return $this->bssOptionValue->create();
    }

    /**
     * get list store view
     */
    public function getListStoreView()
    {
        $listStore = [];
        foreach ($this->storeManager->getStores() as $store) {
            $listStore[] = ['label' => $store->getName(), 'value' => $store->getId()];
        }
        return $listStore;
    }

    /**
     * @return array
     */
    public function getListCustomerGroupArray()
    {
        $customerGroups = $this->customerGroupColl->toOptionArray();
        return $customerGroups;
    }

    /**
     * @return string
     */
    public function getStoresId()
    {
        $listStore = [];
        foreach ($this->storeManager->getStores() as $store) {
            $listStore[] = $store->getId();
        }
        return implode(",", $listStore);
    }

    /**
     * @return string
     */
    public function getCustomerGroupsId()
    {
        $listGroup = [];
        foreach ($this->customerGroupColl->toOptionArray() as $customerGroup) {
            $listGroup[] = $customerGroup['value'];
        }
        return implode(",", $listGroup);
    }
}
