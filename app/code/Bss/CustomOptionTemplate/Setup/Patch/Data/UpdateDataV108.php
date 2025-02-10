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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\CustomOptionTemplate\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpdateDataV108 implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var \Bss\CustomOptionTemplate\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer
     */
    protected $optionVisibleGroupCustomer;

    /**
     * UpgradeData constructor.
     *
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory $eavSetupFactory
     * @param \Bss\CustomOptionTemplate\Helper\Data $helperData
     * @param \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer $optionVisibleGroupCustomer
     */
    public function __construct(
        ModuleDataSetupInterface                                                 $setup,
        EavSetupFactory                                                          $eavSetupFactory,
        \Bss\CustomOptionTemplate\Helper\Data                                    $helperData,
        \Bss\CustomOptionTemplate\Model\ResourceModel\OptionVisibleGroupCustomer $optionVisibleGroupCustomer
    ) {
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->helperData = $helperData;
        $this->optionVisibleGroupCustomer = $optionVisibleGroupCustomer;
    }

    /**
     * Upgrade add tenplates_included and tenplates_excluded to 2 table catalog_product_flat_1 ,catalog_product_flat_2
     *
     * @return UpdateDataV108|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'tenplates_included',
            [
                'group' => 'Product Details',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => __('Custom Option Template Included'),
                'input' => 'Custom option template included',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable,grouped'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'tenplates_excluded',
            [
                'group' => 'Product Details',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => __('Custom Option Template Excluded'),
                'input' => 'Custom option template excluded',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => false,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable,grouped'
            ]
        );
        $this->setDefaultVisibility();
    }

    /**
     * Set default visible for option when install module
     */
    protected function setDefaultVisibility()
    {
        if ($this->setup->tableExists('bss_visible_custom_option_group_customer')
            && $this->setup->tableExists('bss_visible_custom_option_storeview')
        ) {
            $visibleData = [];
            $visibleData['customer'] = $this->helperData->getCustomerGroupsId();
            $visibleData['store'] = $this->helperData->getStoresId();
            $this->optionVisibleGroupCustomer->insertCustomerAndStoreVisibility($visibleData);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Compare ver module.
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.8';
    }
}
