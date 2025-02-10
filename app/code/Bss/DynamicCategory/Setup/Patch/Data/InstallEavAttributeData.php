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
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Install data schema
 */
class InstallEavAttributeData implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $dataSetup;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $dataSetup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $dataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->dataSetup = $dataSetup;
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
     * Install new attribute
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException|\Magento\Framework\Validator\ValidateException
     */
    public function apply()
    {
        $installer = $this->dataSetup->createMigrationSetup();
        $installer->appendClassAliasReplace(
            'bss_dynamic_category_rule',
            'conditions_serialized',
            Migration::ENTITY_TYPE_MODEL,
            Migration::FIELD_CONTENT_TYPE_SERIALIZED,
            ['rule_id']
        );

        $installer->doUpdateClassAliases();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->dataSetup]);
        $eavSetup->addAttribute(
            Category::ENTITY,
            'is_dynamic_category',
            [
                'type' => 'int',
                'label' => 'Dynamic Category',
                'input' => 'select',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'required' => false,
                'sort_order' => 10,
                'default' => '0',
                'group' => 'Products in Category',
            ]
        );
    }
}
