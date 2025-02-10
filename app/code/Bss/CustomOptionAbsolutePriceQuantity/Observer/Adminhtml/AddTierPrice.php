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
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Observer\Adminhtml;

use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Customer\Source\GroupSourceInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Field;

class AddTierPrice implements ObserverInterface
{
    const FIELD_TIER_PRICE = 'bss_tier_price_option';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var GroupSourceInterface
     */
    protected $customerGroupSource;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * AddTierPrice constructor.
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param GroupManagementInterface $groupManagement
     * @param ModuleManager $moduleManager
     * @param Data $directoryHelper
     * @param GroupSourceInterface $customerGroupSource
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        GroupManagementInterface $groupManagement,
        ModuleManager $moduleManager,
        Data $directoryHelper,
        GroupSourceInterface $customerGroupSource,
        \Magento\Framework\Serialize\Serializer\Json $json,
        ModuleConfig $moduleConfig
    ) {
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->groupManagement = $groupManagement;
        $this->moduleManager = $moduleManager;
        $this->directoryHelper = $directoryHelper;
        $this->customerGroupSource = $customerGroupSource;
        $this->json = $json;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isModuleEnable()) {
            $optionQtyField = [
                270 => ['index' => static::FIELD_TIER_PRICE, 'field' => $this->getTierPriceField(270)]
            ];
            $observer->getChild()->addData($optionQtyField);
        }
    }

    /**
     * Retrieve allowed customer groups
     *
     * @return array
     */
    private function getCustomerGroups()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }

        return $this->customerGroupSource->toOptionArray();
    }

    /**
     * Check tier_price attribute scope is global
     *
     * @return bool
     */
    private function isScopeGlobal()
    {
        return $this->locator->getProduct()
            ->getResource()
            ->getAttribute(ProductAttributeInterface::CODE_TIER_PRICE)
            ->isScopeGlobal();
    }

    /**
     * Get websites list
     *
     * @return array
     * @throws NoSuchEntityException
     */
    private function getWebsites()
    {
        $websites = [
            [
                'label' => __('All Websites') . ' [' . $this->directoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        $product = $this->locator->getProduct();

        if (!$this->isScopeGlobal() && $product->getStoreId()) {
            $storeIdProduct = $product->getStoreId();
            /** @var \Magento\Store\Model\Website $website */
            $website = $this->storeManager->getStore($storeIdProduct)->getWebsite();

            $websites[] = [
                'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];
        } elseif (!$this->isScopeGlobal()) {
            $websitesList = $this->storeManager->getWebsites();
            $productWebsiteIds = $product->getWebsiteIds();
            foreach ($websitesList as $website) {
                /** @var \Magento\Store\Model\Website $website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $websites[] = [
                    'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                    'value' => $website->getId(),
                ];
            }
        }

        return $websites;
    }

    /**
     * Retrieve default value for customer group
     *
     * @return int
     */
    private function getDefaultCustomerGroup()
    {
        return $this->groupManagement->getAllCustomersGroup()->getId();
    }

    /**
     * Retrieve default value for website
     *
     * @return int
     */
    protected function getDefaultWebsite()
    {
        if ($this->isShowWebsiteColumn() && !$this->isAllowChangeWebsite()) {
            return $this->storeManager->getStore($this->locator->getProduct()->getStoreId())->getWebsiteId();
        }

        return 0;
    }

    /**
     * Show group prices grid website column
     *
     * @return bool
     */
    private function isShowWebsiteColumn()
    {
        if ($this->isScopeGlobal() || $this->storeManager->isSingleStoreMode()) {
            return false;
        }
        return true;
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    private function isMultiWebsites()
    {
        return !$this->storeManager->isSingleStoreMode();
    }

    /**
     * Check is allow change website value for combination
     *
     * @return bool
     */
    private function isAllowChangeWebsite()
    {
        if (!$this->isShowWebsiteColumn() || $this->locator->getProduct()->getStoreId()) {
            return false;
        }
        return true;
    }
    /**
     * @param int $sortOrder
     * @return array
     */
    protected function getTierPriceField($sortOrder)
    {
        $jsonWebsite = $this->json->serialize($this->getWebsites());
        $customerGroup = $this->json->serialize($this->getCustomerGroups());
        $symbol = $this->storeManager
            ->getStore($this->locator->getProduct()->getStoreId())
            ->getBaseCurrency()
            ->getCurrencySymbol();

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Tier Price'),
                        'componentType' => Field::NAME,
                        'component' => 'Bss_CustomOptionAbsolutePriceQuantity/js/tier_price',
                        'elementTmpl' => 'Bss_CustomOptionAbsolutePriceQuantity/tier-price',
                        'dataScope' => static::FIELD_TIER_PRICE,
                        'dataType' => Text::NAME,
                        'formElement' => 'input',
                        'sortOrder' => $sortOrder,
                        'valueMap' => [
                            'true' => '1',
                            'false' => ''
                        ],
                        'defaultWebsite' => $this->getDefaultWebsite(),
                        'defaultCustomerGroup' => $this->getDefaultCustomerGroup(),
                        'websites' => $jsonWebsite,
                        'multiWebsite' => $this->isMultiWebsites(),
                        'customerGroup' =>$customerGroup,
                        'currencySymbol' => $symbol
                    ],
                ],
            ],
        ];
    }
}
