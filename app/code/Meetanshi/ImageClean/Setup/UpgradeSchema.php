<?php

namespace Meetanshi\ImageClean\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('meetanshi_imageclean'),
                    'used',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'size' => 1,
                        'nullable' => true,
                        'comment' => 'Product Image Status'
                    ]
                );

            $installer->getConnection()
              ->addColumn(
                $installer->getTable('meetanshi_imageclean'),
                'product_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Product Id'
                ]
            );

            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('meetanshi_imageclean'),
                    'path',
                    [
                        'type' => Table::TYPE_TEXT,
                        'size' => 255,
                        'nullable' => true,
                        'comment' => 'File Path'
                    ]
                );
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('meetanshi_imageclean'),
                    'size',
                    [
                        'type' => Table::TYPE_TEXT,
                        'size' => 125,
                        'nullable' => true,
                        'comment' => 'File Size'
                    ]
                );

            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('meetanshi_imageclean'),
                    'product_name',
                    [
                        'type' => Table::TYPE_TEXT,
                        'size' => 225,
                        'nullable' => true,
                        'comment' => 'Product Name'
                    ]
                );
        }
        $installer->endSetup();
    }
}
