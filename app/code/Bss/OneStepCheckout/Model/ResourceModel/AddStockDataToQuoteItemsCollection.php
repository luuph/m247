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
 * @package    Bss_OneStepCheckout
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class AddStockDataToQuoteItemsCollection
{
    /**
     * @var ResourceConnection
     */
    protected $connection;

    /**
     * @param ResourceConnection $connection
     */
    public function __construct(
        ResourceConnection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * Add stock data to quote items collection
     *
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\Collection $collection
     * @return array
     */
    public function addStockDataToCollection($collection)
    {
        $collection->getSelect()->joinLeft(
            ['source_item' => $this->connection->getTableName('inventory_source_item')],
            'main_table.sku = source_item.sku',
            []
        )->joinLeft(
            ['reservation' => $this->connection->getTableName('inventory_reservation')],
            'main_table.sku = reservation.sku',
            []
        )->joinLeft(
            ['stock_item' => $this->connection->getTableName('cataloginventory_stock_item')],
            'main_table.product_id = stock_item.product_id',
            [
                'min_qty' => 'stock_item.min_qty',
                'backorders' => 'stock_item.backorders',
                'manage_stock' => 'stock_item.manage_stock',
                'use_config_manage_stock' => 'stock_item.use_config_manage_stock',
                'use_config_backorders' => 'stock_item.use_config_backorders'
            ]
        )->columns([
            'total_qty' => new \Zend_Db_Expr('COALESCE(SUM(source_item.quantity), 0)'),
            'reservation_qty' => new \Zend_Db_Expr('COALESCE(SUM(reservation.quantity), 0)'),
            'saleable_qty' => new \Zend_Db_Expr(
                '(COALESCE(SUM(source_item.quantity), 0) + COALESCE(SUM(reservation.quantity), 0) - COALESCE(stock_item.min_qty, 0))'
            )
        ])->group([
            'main_table.item_id',
            'main_table.sku',
            'stock_item.min_qty',
            'stock_item.backorders',
            'stock_item.manage_stock',
            'stock_item.use_config_manage_stock',
            'stock_item.use_config_backorders'
        ]);
        if ($result = $collection->getData()) {
            return $result;
        }
        return [];
    }
}
