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
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'bss_protags_rule'
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable('bss_protags_rule')
        )->addColumn(
            'protags_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ProTag Id'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => 1],
            'Status'
        )->addColumn(
            'router_tag',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Customize Router'
        )->addIndex(
            $installer->getIdxName('bss_protags_rule', ['protags_id']),
            ['protags_id']
        )
        ->setComment('ProductTag');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'bss_protags_tag'
         */

        $this->createNameTagTable($installer);

        /**
         * Create table 'bss_protags_rule_product'
         */

        $installer->getConnection()->createTable($table);
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bss_protags_rule_product'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'protags_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'ProductTag Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'position',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Position'
            )
            ->addForeignKey(
                $setup->getFkName('bss_protags_rule', 'protags_id', 'bss_protags_rule_product', 'protags_id'),
                'protags_id',
                $setup->getTable('bss_protags_rule'),
                'protags_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Products of ProductTag');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'bss_protags_rule_store'
         */
        $this->creatProtagStoreTable($installer);

        /**
         * Create table 'custom_table_index'
         */
        $this->creatCustomTableIndex($installer);

        $installer->endSetup();
    }

    /**
     * @param object $installer
     */
    private function creatProtagStoreTable($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bss_protags_rule_store'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'protags_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'ProTag Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )
            ->addIndex(
                $installer->getIdxName(
                    'protag_store',
                    ['store_id']
                ),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'bss_protags_rule_store',
                    'protags_id',
                    'bss_protags_rule',
                    'protags_id'
                ),
                'protags_id',
                $installer->getTable('bss_protags_rule'),
                'protags_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Tag of Store');
        $installer->getConnection()->createTable($table);
    }

    /**
     * @param object $installer
     */
    private function createNameTagTable($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bss_protags_tag'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'protags_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'ProductTag Id'
            )
            ->addColumn(
                'name_tag',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Tag'
            )
            ->addForeignKey(
                $installer->getFkName('bss_protags_rule', 'protags_id', 'bss_protags_tag', 'protags_id'),
                'protags_id',
                $installer->getTable('bss_protags_rule'),
                'protags_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Name of ProductTag');
        $installer->getConnection()->createTable($table);
    }

    /**
     * @param object $installer
     */
    private function creatCustomTableIndex($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('bss_protags_product_tagname_index'))
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false],
                'ProductTag Id'
            )
            ->addColumn(
                'tag',
                Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Tag'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false],
                'Store ID'
            )
            ->setComment('Table Index ProductTag');
        $installer->getConnection()->createTable($table);
    }
}
