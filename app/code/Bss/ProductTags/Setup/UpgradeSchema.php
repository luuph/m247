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
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Bss\ProductTags\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<=')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('bss_protags_product_tagname_index'),
                'tag_key',
                [
                    'type' => Table::TYPE_TEXT,
                    'size' => 80,
                    'nullable' => false,
                    'comment' => 'Tag Key'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2', '<=')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('bss_protags_rule'),
                'tag_key',
                [
                    'type' => Table::TYPE_TEXT,
                    'size' => 80,
                    'nullable' => false,
                    'comment' => 'Tag Key'
                ]
            );

            $table = $installer->getConnection()
                ->newTable($installer->getTable('bss_protags_rule_key'))
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
                    'tag_key',
                    Table::TYPE_TEXT,
                    80,
                    ['nullable' => false, 'default' => ''],
                    'Tag Key'
                )
                ->addForeignKey(
                    $installer->getFkName('bss_protags_rule_key', 'protags_id', 'bss_protags_rule', 'protags_id'),
                    'protags_id',
                    $installer->getTable('bss_protags_rule'),
                    'protags_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Key of ProductTag');
            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<=')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('bss_protags_product_tagname_index'),
                'status',
                [
                    'type' => Table::TYPE_TEXT,
                    'size' => 10,
                    'nullable' => true,
                    'comment' => 'Status'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.5', '<=')) {
            $routeTagCol = $installer->getConnection()->tableColumnExists(
                $installer->getTable('bss_protags_rule'),
                'router_tag'
            );
            if (!$routeTagCol) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('bss_protags_rule'),
                    'router_tag',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'Customize Router'
                    ]
                );
            }
            $routeTagCol = $installer->getConnection()->tableColumnExists(
                $installer->getTable('bss_protags_product_tagname_index'),
                'router_tag'
            );
            if (!$routeTagCol) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('bss_protags_product_tagname_index'),
                    'router_tag',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Customize Router'
                    ]
                );
            }
        }

        $installer->endSetup();
    }
}
