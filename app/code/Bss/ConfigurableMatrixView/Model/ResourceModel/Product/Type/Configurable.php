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
 * @package    Bss_ConfigurableMatrixView
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableMatrixView\Model\ResourceModel\Product\Type;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Bss\ConfigurableMatrixView\Model\ResourceModel\Product\InventoryStock;
use Magento\Framework\DB\Sql\ExpressionFactory;

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
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Bss\ConfigurableMatrixView\Helper\Data
     */
    protected $helper;

    /**
     * @var ExpressionFactory
     */
    protected $expressionFactory;

    /**
     * @var bool
     */
    public $allowGetQuantityBss = false;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Configurable constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param InventoryStock $inventoryStock
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Registry $registry
     * @param ExpressionFactory $expressionFactory
     * @param \Bss\ConfigurableMatrixView\Helper\Data $helper
     */
    public function __construct(
        StoreManagerInterface                     $storeManager,
        ProductMetadataInterface                  $productMetadata,
        InventoryStock                            $inventoryStock,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager         $moduleManager,
        \Magento\Framework\App\RequestInterface   $request,
        \Magento\Framework\Registry               $registry,
        ExpressionFactory                         $expressionFactory,
        \Bss\ConfigurableMatrixView\Helper\Data   $helper
    )
    {
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        $this->inventoryStock = $inventoryStock;
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
        $this->request = $request;
        $this->registry = $registry;
        $this->expressionFactory = $expressionFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetUsedProductCollection(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject,
                                                                     $result
    )
    {
        if ($this->isEnableMSI() && $this->allowGetQuantityBss ) {
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
                $this->selectQuantityBss($result->getSelect(), "e");
            }
            return $result;
        }
        return $result;
    }

    /**
     * Check table exists or not Query
     *
     * @param string $nameTable
     * @return bool
     */
    public function isTableExistsOrNot($nameTable)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName($nameTable);
        return $connection->isTableExists($tableName);
    }

    /**
     * Select quantity(salable qty) by stock table and inventory_reservation table
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $tableMain
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function selectQuantityBss($select, $tableMain)
    {
        $stockId = $this->getStockId();
        if ($stockId) {
            $connection = $this->resource->getConnection();
            $table_sales_stock = $this->resource->getTableName('inventory_reservation');
            $query = $connection->select()->from($table_sales_stock, ['quantity' => 'SUM(quantity)'])
                ->where(sprintf('sku = %s.sku', $tableMain))
                ->where('stock_id = ?', $stockId)
                ->limit(1);
            $table_stock_item = $this->resource->getTableName('cataloginventory_stock_item');
            $query_stock = $connection->select()->from($table_stock_item, ['min_qty'])
                ->where(sprintf('product_id = %s.product_id', $tableMain))
                ->where('stock_id = ?', $stockId)
                ->limit(1);
            $select->columns([
                'quantity' => new \Zend_Db_Expr(
                    'IFNULL((' . $query . '),0) + stock.quantity - IFNULL((' . $query_stock . '),0)'
                )
            ]);
        }
    }

    /**
     * Get Stock id current
     *
     * @return bool|int|mixed
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function getStockId()
    {
        $stockId = 0;
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $checkExistTable = $this->isTableExistsOrNot(
            $this->resource->getTableName('inventory_stock_sales_channel')
        );
        if ($checkExistTable) {
            $stockId = $this->inventoryStock->getStockIdByWebsiteCode($websiteCode);
        }
        return $stockId;
    }

    /**
     * Get stock table current
     *
     * @return false|string
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function getStockTable()
    {
        $stockId = $this->getStockId();
        if ($stockId) {
            return $this->inventoryStock->getStockTableName($stockId);
        }
        return false;
    }

    /**
     * Get used product configurable
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array|null $requiredAttributeIds
     * @return mixed
     */
    public function getUsedProductsConfigurable($product, $requiredAttributeIds = null)
    {
        $this->allowGetQuantityBss = true;
        $usedProducts = $product->getTypeInstance()->getUsedProducts($product, $requiredAttributeIds);
        $this->allowGetQuantityBss = false;
        return $usedProducts;
    }

    /**
     * Check enable disable MSI
     *
     * @return bool
     */
    public function isEnableMSI()
    {
        $version = $this->productMetadata->getVerSion();
        if (version_compare($version, '2.3.0') >= 0 && $this->moduleManager->isEnabled('Magento_Inventory')) {
            return true;
        }
        return false;
    }

}
