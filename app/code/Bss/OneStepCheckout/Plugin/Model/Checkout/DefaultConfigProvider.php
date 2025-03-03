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
 * @package   Bss_OneStepCheckout
 * @author    Extension Team
 * @copyright Copyright (c) 2023-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Plugin\Model\Checkout;

use Bss\OneStepCheckout\Helper\Config;
use Bss\OneStepCheckout\Model\ResourceModel\AddStockDataToQuoteItemsCollection;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager;

class DefaultConfigProvider
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var mixed
     */
    protected $getProductSalableQty;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|mixed
     */
    protected $storeManager;

    /**
     * @var \Magento\InventorySalesApi\Api\StockResolverInterface|mixed
     */
    protected $stockResolver;

    /**
     * @var \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface|mixed
     */
    protected $getStockItemConfiguration;

    /**
     * @var AddStockDataToQuoteItemsCollection
     */
    protected $addStockDataToQuoteItemsCollection;

    /**
     * Construct.
     *
     * @param CheckoutSession $checkoutSession
     * @param StockRegistryInterface $stockRegistry
     * @param Config $configHelper
     * @param Manager $moduleManager
     * @param AddStockDataToQuoteItemsCollection $addStockDataToQuoteItemsCollection
     */
    public function __construct(
        CheckoutSession        $checkoutSession,
        StockRegistryInterface $stockRegistry,
        Config                 $configHelper,
        Manager                $moduleManager,
        AddStockDataToQuoteItemsCollection $addStockDataToQuoteItemsCollection
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->stockRegistry = $stockRegistry;
        $this->configHelper = $configHelper;
        $this->moduleManager = $moduleManager;
        if ($this->moduleManager->isEnabled("Magento_Inventory")) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Store\Model\StoreManagerInterface');
            $this->stockResolver = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\InventorySalesApi\Api\StockResolverInterface');
            $this->getStockItemConfiguration = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface');
        }
        $this->addStockDataToQuoteItemsCollection = $addStockDataToQuoteItemsCollection;
    }

    /**
     * Add saleable qty to checkout config
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array|mixed $result
     * @return array|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig($subject, $result)
    {
        if ($this->configHelper->isEnabled()) {
            $isInventoryEnable = $this->moduleManager->isEnabled("Magento_Inventory");
            $this->addDataQty($result, $isInventoryEnable);
        }

        return $result;
    }

    /**
     * Add data QTY product.
     *
     * @param array|mixed $data
     * @param bool $isInventoryEnable
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addDataQty(&$data, $isInventoryEnable)
    {
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            $quoteItems = $this->checkoutSession->getQuote()->getItemsCollection();
            if ($isInventoryEnable) {
                $stock = $this->stockResolver->execute(
                    \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
                    $this->storeManager->getWebsite()->getCode()
                );
                $quoteItemsCollection = $this->addStockDataToQuoteItemsCollection->addStockDataToCollection($quoteItems);
                if ($quoteItemsCollection) {
                    foreach ($quoteItemsCollection as $quoteItem) {
                        if (in_array($quoteItem['product_type'], ["configurable", "bundle", "grouped"])) {
                            continue;
                        }
                        if ($stock->getStockId()) {
                            $id = $quoteItem['parent_item_id'] ?: $quoteItem['item_id'];
                            $data['saleableQty'][$id] = $quoteItem['saleable_qty'];
                            if ($quoteItem['use_config_manage_stock']) {
                                $data['isManageStock'][$id] = $this->configHelper->getManageStock();
                            } else {
                                $data['isManageStock'][$id] = $quoteItem["manage_stock"];
                            }
                            if ($quoteItem['use_config_backorders']) {
                                $data['backorders'][$id] = $this->configHelper->getBackOrders();
                            } else {
                                $data['backorders'][$id] = $quoteItem['backorders'];
                            }
                        }
                    }
                }
            } else {
                foreach ($quoteItems as $quoteItem) {
                    if (in_array($quoteItem->getProductType(), ["configurable", "bundle", "grouped"])) {
                        continue;
                    }
                    $product = $quoteItem->getProduct();
                    $stockItemConfiguration = $this->stockRegistry->getStockItem(
                        $product->getId(),
                        $product->getStore()->getWebsiteId()
                    );
                    $salableQty = $this->getSalableQty($product, $stockItemConfiguration);
                    $id = $quoteItem->getParentItemId() ?: $quoteItem->getItemId();
                    $data['saleableQty'][$id] = $salableQty;
                    $data['backorders'][$id] = $stockItemConfiguration->getBackorders();
                    $data['isManageStock'][$id] = $stockItemConfiguration->getManageStock();
                }
            }
        }
        return $data;
    }

    /**
     * Get salable Qty
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItemConfiguration
     * @return float
     */
    public function getSalableQty($product, $stockItemConfiguration)
    {
        if (!$stockItemConfiguration->getManageStock()) {
            return PHP_INT_MAX;
        }
        if ($stockItemConfiguration->getBackorders() == 0) {
            return $product->getQty() - $stockItemConfiguration->getMinQty();
        }
        return $product->getQty();
    }
}
