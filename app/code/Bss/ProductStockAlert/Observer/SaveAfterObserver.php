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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Observer;

class SaveAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\ProductStockAlert\Helper\MultiSourceInventory
     */
    protected $multiSourceInventory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param \Bss\ProductStockAlert\Helper\Data $helperData
     * @param \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\Data $helperData,
        \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->helperData = $helperData;
        $this->multiSourceInventory = $multiSourceInventory;
        $this->resource = $resource;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute($observer) {
        try {
            if ($this->helperData->isStockAlertAllowed()) {
                $product = $observer->getEvent()->getProduct();
                $productId = $product->getId();
                if ($product->getId()) {
                    $stockProduct = $this->stockRegistry->getStockItem($productId);
                    if (!$stockProduct->getIsInStock()) {
                        $resourceConnection = $this->resource->getConnection();
                        $stockAlertTable = $this->resource->getTableName('bss_product_alert_stock');
                        $websiteIds = $product->getWebsiteIds();
                        foreach ($websiteIds as $websiteId) {
                            $data = [
                                'status' => \Bss\ProductStockAlert\Model\Config\Source\Status::STATUS_PENDING
                            ];
                            $where = [
                                'product_id = ?' => (int)$productId,
                                'website_id = ?' => (int)$websiteId,
                                'status <> ?' => \Bss\ProductStockAlert\Model\Config\Source\Status::STATUS_SENTLIMIT
                            ];
                            $resourceConnection->update($stockAlertTable, $data, $where);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            //skip
        }
    }
}
