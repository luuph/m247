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
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Helper;

use Bss\PreOrder\Helper\Data as PreOrderHelper;
use Bss\PreOrder\Model\PreOrderAttribute;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

class Stock
{
    /**
     * @var PreOrderHelper
     */
    protected $preOrderHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * CheckStockOptions constructor.
     * @param PreOrderHelper $preOrderHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        PreOrderHelper $preOrderHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->preOrderHelper = $preOrderHelper;
        $this->storeManager = $storeManager;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Get Product not set preorder
     *
     * @param \Magento\CatalogInventory\Helper\Stock $layer
     * @param Collection $collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddIsInStockFilterToCollection(
        \Magento\CatalogInventory\Helper\Stock $layer,
        $collection
    ) {
        if ($this->preOrderHelper->isEnable()
            && $collection->hasFlag('allow_check_out_stock_pre_order')
            && $this->preOrderHelper->isDisplayOutOfStockProduct()
        ) {
            /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection */
            $productCollection = clone $collection;
            $collection->addAttributeToSelect(PreOrderAttribute::PRE_ORDER_STATUS);
            $productCollection->addAttributeToFilter(PreOrderAttribute::PRE_ORDER_STATUS, [['eq' => 0],['null' => true]], 'left')
                ->addAttributeToFilter('type_id', ['neq' => 'configurable']);
            $collection->setFlag('ignore_product', $productCollection->getAllIds());
        }
    }

    /**
     * Ignore list product out stock and not set preorder
     *
     * @param \Magento\CatalogInventory\Helper\Stock $layer
     * @param mixed $result
     * @param Collection $collection
     * @return void $result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddIsInStockFilterToCollection(
        \Magento\CatalogInventory\Helper\Stock $layer,
        $result,
        $collection
    ) {
        if ($this->preOrderHelper->isEnable()
            && $collection->hasFlag('allow_check_out_stock_pre_order')
            && !empty($collection->getFlag('ignore_product'))
            && $this->preOrderHelper->isDisplayOutOfStockProduct()
        ) {
            /** @var Collection $collection */
            $cloneCollection = clone $collection;
            $stockId = $this->getStockIdForCurrentWebsite->execute();
            if ($stockId === $this->defaultStockProvider->getId()) {
                $cloneCollection->getSelect()->where(
                    'stock_status_index.stock_status  = 0 AND e.entity_id in (?)',
                    $collection->getFlag('ignore_product')
                )->where('e.type_id != (?)', 'configurable');
            } else {
                $cloneCollection->getSelect()->where(
                    'stock_status_index.is_salable  = 0 AND e.entity_id in (?)',
                    $collection->getFlag('ignore_product')
                )->where('e.type_id != (?)', 'configurable');
            }
            $ignoreItem = $cloneCollection->getAllIds();
            if (!empty($ignoreItem)) {
                $collection->getSelect()->where(
                    ' e.entity_id not in (?)',
                    $ignoreItem
                );
            }
            $collection->setFlag('allow_check_out_stock_pre_order', false);
        }
        return $result;
    }
}
