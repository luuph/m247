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
 * @package    Bss_ConfigurableProductWholesale
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableProductWholesale\Plugin\Model\ResourceModel\Product\Type;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Bss\ConfigurableProductWholesale\Model\ResourceModel\Product\InventoryStock;

class Configurable
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var InventoryStock
     */
    private $inventoryStock;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Sql\ExpressionFactory
     */
    protected $expressionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Configurable constructor.
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param InventoryStock $inventoryStock
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\DB\Sql\ExpressionFactory $expressionFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        InventoryStock $inventoryStock,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\DB\Sql\ExpressionFactory $expressionFactory,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        $this->inventoryStock = $inventoryStock;
        $this->resource = $resource;
        $this->expressionFactory = $expressionFactory;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @param $result
     * @return object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetUsedProductCollection(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject,
        $result
    ) {
        $version = $this->productMetadata->getVerSion();
        if (version_compare($version, '2.3.0') >= 0 && $this->moduleManager->isEnabled('Magento_InventorySales')) {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $stockId = $this->inventoryStock->getStockIdByWebsiteCode($websiteCode);
            if ($stockId) {
                $stockTable = $this->inventoryStock->getStockTableName($stockId);
                if (!$stockTable) {
                    return $result;
                }
                $result->getSelect()->joinInner(
                    ['stock' => $stockTable],
                    'stock.sku = e.sku'
                );

                $connection  = $this->resource->getConnection();
                $table_sales_stock = $this->resource->getTableName('inventory_reservation');
                $query = $connection->select()->from($table_sales_stock, ['quantity' => 'SUM(quantity)'])
                    ->where('sku = e.sku')
                    ->where('stock_id = ?', $stockId)
                    ->limit(1);
                $result->getSelect()->columns(
                    [
                        'quantity_bss' => $this->expressionFactory->create(
                            ['expression' => 'IFNULL(('.$query.'),0) + stock.quantity']
                        )
                    ]
                );
            }
        }

        return $result;
    }
}
