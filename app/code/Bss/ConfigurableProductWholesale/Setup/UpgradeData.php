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
 * @category  BSS
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableProductWholesale\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Catalog\Model\Product;

/**
 * Upgrade Data script
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'bss_cpw_ajax',
                [
                    'type' => 'int',
                    'label' => 'Bss Configurable Product Wholesale Ajax Load',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'input' => 'select',
                    'source' => \Bss\ConfigurableProductWholesale\Model\Config\Source\Boolean::class,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => 'configurable'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.3.1', '<=')) {

            $categorySetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId(Product::ENTITY);
            $groupName = 'CPWD General';

            if (!$categorySetup->getAttributeGroup(Product::ENTITY, $attributeSetId, $groupName)) {
                $categorySetup->addAttributeGroup(Product::ENTITY, $attributeSetId, $groupName, 60);
            }

            // Remove attr bss_cpw_ajax to reinstall
            $categorySetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'bss_cpw_ajax'
            );

            $categorySetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'bss_cpw_ajax',
                [
                    'type' => 'int',
                    'label' => 'Use Ajax Load for Wholesale Display',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'input' => 'select',
                    'source' => \Bss\ConfigurableProductWholesale\Model\Config\Source\Boolean::class,
                    'default' => 2,
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => 'configurable',
                    'group' => $groupName,
                ]
            );

            // Install new attribute enable_cpwd
            $categorySetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'enable_cpwd',
                [
                    'label' => 'Enable Wholesale Display',
                    'input' => 'select',
                    'required' => false,
                    'sort_order' => 100,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => $groupName,
                    'default' => 1,
                    'apply_to' => 'configurable',
                    'type' => 'int',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'searchable' => true,
                    'used_in_product_listing' => true,
                ]
            );
        }
    }
}
