<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageplaza\Affiliate\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class ChangeColumn
 *
 * @package Mageplaza\Affiliate\Setup\Patch\Schema
 */
class ChangeColumn implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * ChangeColumn constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        $setup->startSetup();
        $connection = $setup->getConnection();

        $quoteTable = $setup->getTable('quote');
        if (!$connection->tableColumnExists($quoteTable, 'affiliate_base_discount_shipping_amount')) {
            $connection->addColumn(
                $setup->getTable($quoteTable),
                'base_affiliate_discount_shipping_amount',
                [
                    'type'     => Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length'   => '12,4',
                    'comment'  => 'Base Affiliate Discount Shipping Amount'
                ]
            );
        } elseif (!$connection->tableColumnExists($quoteTable, 'base_affiliate_discount_shipping_amount')) {
            $connection->changeColumn(
                $setup->getTable($quoteTable),
                'affiliate_base_discount_shipping_amount',
                'base_affiliate_discount_shipping_amount',
                [
                    'type'     => Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length'   => '12,4',
                    'comment'  => 'Base Affiliate Discount Shipping Amount'
                ]
            );
        }

        $setup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}