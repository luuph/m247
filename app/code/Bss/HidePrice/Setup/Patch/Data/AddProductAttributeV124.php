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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\HidePrice\Setup\Patch\Data;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Data patch format
 */
class AddProductAttributeV124 implements DataPatchInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * Construct.
     *
     * @param ModuleDataSetupInterface $setup
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup = $setup;
    }

    /**
     * Add product attribute
     *
     * @return AddProductAttributeV124|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $this->setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'hideprice_applychild',
            [
                'group' => 'Hide Price',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Apply for child product',
                'input' => 'select',
                'wysiwyg_enabled' => false,
                'source' => \Bss\HidePrice\Model\Config\Source\ApplyForChildProduct::class,
                'class' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => Configurable::TYPE_CODE,
                'system' => false
            ]
        );

        $this->setup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
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
        return '1.2.4';
    }
}
