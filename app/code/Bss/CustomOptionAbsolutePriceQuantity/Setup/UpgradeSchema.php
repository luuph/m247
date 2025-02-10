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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * UpgradeSchema.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->createTierPriceforOption($setup, $connection);
            $this->createTierPriceforOptionValue($setup, $connection);
            $this->createCustomOptionQtyReport($setup, $connection);
        }
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->createTableCustomOptionDescriptionType($setup, $connection);
            $this->createTableCustomOptionDescription($setup, $connection);
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function createTierPriceforOption($setup, $connection)
    {
        if (!$setup->tableExists('bss_tier_price_product_option')) {
            $table = $connection
                ->newTable(
                    $setup->getTable('bss_tier_price_product_option')
                )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'nullable' => false, 'auto_increment' => true],
                    'ID'
                )
                ->addColumn(
                    'option_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Option Id'
                )
                ->addColumn(
                    'tier_price',
                    Table::TYPE_TEXT,
                    65536,
                    ['nullable' => true, 'default' => null],
                    'Tier Price'
                )
                ->addIndex(
                    $setup->getIdxName('bss_tier_price_product_option', ['id', 'option_id']),
                    ['id', 'option_id']
                )
                ->setComment(
                    'Tier Price for custom option'
                );
            $connection->createTable($table);
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $setup->getTable('bss_tier_price_product_option'),
                    'option_id',
                    $setup->getTable('catalog_product_option'),
                    'option_id'
                ),
                $setup->getTable('bss_tier_price_product_option'),
                'option_id',
                $setup->getTable('catalog_product_option'),
                'option_id',
                Table::ACTION_SET_NULL
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function createTierPriceforOptionValue($setup, $connection)
    {
        if (!$setup->tableExists('bss_tier_price_product_option_type_value')) {
            $table = $connection
                ->newTable(
                    $setup->getTable('bss_tier_price_product_option_type_value')
                )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'nullable' => false, 'auto_increment' => true],
                    'ID'
                )
                ->addColumn(
                    'option_type_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Option Type Id'
                )
                ->addColumn(
                    'tier_price',
                    Table::TYPE_TEXT,
                    65536,
                    ['nullable' => true, 'default' => null],
                    'Tier Price'
                )
                ->addIndex(
                    $setup->getIdxName('bss_tier_price_product_option_type_value', ['id', 'option_type_id']),
                    ['id', 'option_type_id']
                )
                ->setComment(
                    'Tier Price for custom option type value '
                );
            $connection->createTable($table);
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $setup->getTable('bss_tier_price_product_option_type_value'),
                    'option_type_id ',
                    $setup->getTable('catalog_product_option_type_value'),
                    'option_type_id '
                ),
                $setup->getTable('bss_tier_price_product_option_type_value'),
                'option_type_id',
                $setup->getTable('catalog_product_option_type_value'),
                'option_type_id',
                Table::ACTION_SET_NULL
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public function createCustomOptionQtyReport($setup, $connection)
    {
        if (!$setup->tableExists('bss_custom_option_qty_report')) {
            $table = $connection
                ->newTable(
                    $setup->getTable('bss_custom_option_qty_report')
                )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'nullable' => false, 'auto_increment' => true],
                    'Option Type Image ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Product Id'
                )
                ->addColumn(
                    'product_name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => null],
                    'Product Name'
                )
                ->addColumn(
                    'product_sku',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => null],
                    'Product Sku'
                )
                ->addColumn(
                    'option_title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => null],
                    'Option Title'
                )
                ->addColumn(
                    'option_value',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => null],
                    'Option value'
                )
                ->addColumn(
                    'option_type_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Option Type Id'
                )
                ->addColumn(
                    'option_price',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => null],
                    'Option Price'
                )
                ->addColumn(
                    'qty',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Qty'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Order ID'
                )
                ->addColumn(
                    'creditmemo_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Creditmomo ID'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addIndex(
                    $setup->getIdxName('bss_custom_option_qty_report', ['id','created_at']),
                    ['id', 'created_at']
                )
                ->setComment(
                    'Custom Option Qty report'
                );
            $connection->createTable($table);
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $setup->getTable('bss_custom_option_qty_report'),
                    'order_id',
                    $setup->getTable('sales_order'),
                    'entity_id'
                ),
                $setup->getTable('bss_custom_option_qty_report'),
                'order_id',
                $setup->getTable('sales_order'),
                'entity_id',
                Table::ACTION_SET_NULL
            );
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $setup->getTable('bss_custom_option_qty_report'),
                    'creditmemo_id',
                    $setup->getTable('sales_creditmemo'),
                    'entity_id'
                ),
                $setup->getTable('bss_custom_option_qty_report'),
                'creditmemo_id',
                $setup->getTable('sales_creditmemo'),
                'entity_id',
                Table::ACTION_SET_NULL
            );
        }
    }

    /**
     * Create table BssCustomOptionDescriptionType.
     *
     * @param SchemaSetupInterface $setup
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function createTableCustomOptionDescriptionType($setup, $connection)
    {
        $nameTable = $setup->getTable('bss_custom_option_description_type');
        if (!$setup->tableExists($nameTable)) {
            $table = $connection
                ->newTable($nameTable)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'nullable' => false, 'auto_increment' => true],
                    'ID'
                )
                ->addColumn(
                    'option_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Option Id'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    10,
                    ['unsigned' => true],
                    'Store Id'
                )
                ->addColumn(
                    'bss_description_option_type',
                    Table::TYPE_SMALLINT,
                    6,
                    ['nullable' => true, 'default' => null],
                    'Type description'
                )
                ->addIndex(
                    $setup->getIdxName('bss_custom_option_description_type_option_id_store_id', ['option_id', 'store_id']),
                    ['option_id', 'store_id']
                )
                ->addIndex(
                    $setup->getIdxName('bss_custom_option_description_type_store_id', ['store_id']),
                    ['store_id']
                )
                ->setComment(
                    'Type description for custom option'
                );
            $connection->createTable($table);
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $nameTable,
                    'option_id',
                    $setup->getTable('catalog_product_option'),
                    'option_id'
                ),
                $nameTable,
                'option_id',
                $setup->getTable('catalog_product_option'),
                'option_id',
                Table::ACTION_CASCADE
            );
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $nameTable,
                    'store_id',
                    $setup->getTable('store'),
                    'store_id'
                ),
                $nameTable,
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            );
        }
    }

    /**
     * Create table BssCustomOptionDescriptionType.
     *
     * @param SchemaSetupInterface $setup
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function createTableCustomOptionDescription($setup, $connection)
    {
        $nameTable = $setup->getTable('bss_custom_option_description');
        if (!$setup->tableExists($nameTable)) {
            $table = $connection
                ->newTable($nameTable)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'nullable' => false, 'auto_increment' => true],
                    'ID'
                )
                ->addColumn(
                    'option_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true],
                    'Option Id'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    10,
                    ['unsigned' => true],
                    'Store Id'
                )
                ->addColumn(
                    'bss_description_option',
                    Table::TYPE_TEXT,
                    65536,
                    ['nullable' => true, 'default' => null],
                    'Type description'
                )
                ->addIndex(
                    $setup->getIdxName('bss_custom_option_description_option_id_store_id', ['option_id', 'store_id']),
                    ['option_id', 'store_id']
                )
                ->addIndex(
                    $setup->getIdxName('bss_custom_option_description_store_id', ['store_id']),
                    ['store_id']
                )
                ->setComment(
                    'Type description for custom option'
                );
            $connection->createTable($table);
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $nameTable,
                    'option_id',
                    $setup->getTable('catalog_product_option'),
                    'option_id'
                ),
                $nameTable,
                'option_id',
                $setup->getTable('catalog_product_option'),
                'option_id',
                Table::ACTION_CASCADE
            );
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $nameTable,
                    'store_id',
                    $setup->getTable('store'),
                    'store_id'
                ),
                $nameTable,
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            );
        }
    }
}
