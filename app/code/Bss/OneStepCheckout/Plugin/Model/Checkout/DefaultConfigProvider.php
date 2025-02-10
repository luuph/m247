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
     * Construct.
     *
     * @param CheckoutSession $checkoutSession
     * @param StockRegistryInterface $stockRegistry
     * @param Config $configHelper
     * @param Manager $moduleManager
     */
    public function __construct(
        CheckoutSession        $checkoutSession,
        StockRegistryInterface $stockRegistry,
        Config                 $configHelper,
        Manager                $moduleManager
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
                foreach ($quoteItems as $quoteItem) {
                    if (in_array($quoteItem->getProductType(), ["configurable", "bundle", "grouped"])) {
                        continue;
                    }
                    $product = $quoteItem->getProduct();
                    $productSku = $product->getData('sku') ?? $product->getSku();
                    $stock = $this->stockResolver->execute(
                        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
                        $this->storeManager->getWebsite()->getCode()
                    );
                    if ($stockId = $stock->getStockId()) {
                        $stockItemConfiguration = $this->getStockItemConfiguration->execute($productSku, $stockId);
                        if (!$this->getProductSalableQty) {
                            $this->getProductSalableQty =
                                \Magento\Framework\App\ObjectManager::getInstance()
                                    ->get('Magento\InventorySales\Model\GetProductSalableQty');
                        }

                        $salableQty = $this->getProductSalableQty->execute($productSku, $stockId);
                        $id = $quoteItem->getParentItemId() ?: $quoteItem->getItemId();
                        $data['saleableQty'][$id] = $salableQty;
                        $data['backorders'][$id] = $stockItemConfiguration->getBackorders();
                        $data['isManageStock'][$id] = $stockItemConfiguration->isManageStock();
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
                    if ($stockItemConfiguration->getBackorders() == 0) {
                        $salableQty = $product->getQty() - $stockItemConfiguration->getMinQty();
                    } else {
                        $salableQty = $product->getQty();
                    }
                    $id = $quoteItem->getParentItemId() ?: $quoteItem->getItemId();
                    $data['saleableQty'][$id] = $salableQty;
                    $data['backorders'][$id] = $stockItemConfiguration->getBackorders();
                    $data['isManageStock'][$id] = $stockItemConfiguration->getManageStock();
                }
            }
        }
        return $data;
    }
}
