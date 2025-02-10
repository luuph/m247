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
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableProductWholesale\Model\Table\Data;

use Bss\ConfigurableProductWholesale\Helper\Data as WholesaleData;
use Bss\ConfigurableProductWholesale\Model\DataInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Bss\ConfigurableProductWholesale\Model\ConfigurableData;
use Magento\CatalogInventory\Api\StockStateInterface;

class Stock implements DataInterface
{
    const MODEL_NAME = 'stock';

    /**
     * @var WholesaleData
     */
    private $helper;

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var ConfigurableData
     */
    protected $configurableData;

    /**
     * @var int
     */
    protected $versionMagento = 0;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * TierPrice constructor.
     *
     * @param WholesaleData $helper
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param ConfigurableData $configurableData
     * @param StockStateInterface $stockState
     */
    public function __construct(
        WholesaleData $helper,
        StockRegistryProviderInterface $stockRegistryProvider,
        ConfigurableData $configurableData,
        StockStateInterface $stockState
    ) {
        $this->helper = $helper;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->configurableData = $configurableData;
        $this->stockState = $stockState;
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return self::MODEL_NAME;
    }

    /**
     * @param $productCollection
     * @return object
     */
    public function prepareCollection($productCollection)
    {
        return $productCollection;
    }

    /**
     * @param $productCollection
     * @return array
     */
    public function getData($productCollection)
    {
        $data = [];
        foreach ($productCollection as $product) {
            $productId = $product->getId();
            $websiteId = $product->getStore()->getWebsiteId();
            $stockItem = $this->stockRegistryProvider->getStockItem($productId, $websiteId);
            $status = $this->getStatus($product, $productId, $websiteId);
            $this->helper->getEventManager()->dispatch('bss_cpd_prepare_product_stock', ['product' => $product]);
            $data[$productId] = [
                'quantity' => $status,
                'saleable_quantity' => 1,
                'min_order_qty' => (float) $stockItem->getMinSaleQty(),
                'max_order_qty' => (float) $stockItem->getMaxSaleQty()
            ];
        }
        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $productChild
     * @param int $childProductId
     * @param int $websiteId
     * @return string|int
     */
    public function getStatus($productChild, $childProductId, $websiteId)
    {
        $this->checkVersionMagento();
        if ($productChild->isAvailable()) {
            if (!$this->helper->getConfig('/general/stock_number')) {
                $status = __('In stock');
            } else {
                $status = $this->getQtyofChildProduct($productChild, $childProductId, $websiteId);
            }
        } else {
            $status = __('Out of stock');
        }
        return $status;
    }

    /**
     * @param $productChild
     * @param $childProductId
     * @param $websiteId
     * @return float
     */
    protected function getQtyofChildProduct($productChild, $childProductId, $websiteId)
    {
        if ($this->versionMagento != 0) {
            return (float)$productChild->getQuantityBss();
        }
        return  $this->stockState->getStockQty($childProductId, $websiteId);
    }

    /**
     * @return $this
     */
    protected function checkVersionMagento()
    {
        if ($this->helper->validateMagentoVersion('2.3.0')
            && $this->helper->hasModuleEnabled('Magento_InventorySales')
        ) {
            $this->versionMagento = 1;
        }
        return $this;
    }
}
