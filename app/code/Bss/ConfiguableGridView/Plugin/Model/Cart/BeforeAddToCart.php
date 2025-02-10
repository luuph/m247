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
 * @package    Bss_ConfiguableGridView
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfiguableGridView\Plugin\Model\Cart;

/**
 * Class BeforeAddToCart
 *
 * @package Bss\ConfiguableGridView\Plugin\Model\Cart
 */
class BeforeAddToCart
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
     */
    private $getSalableQuantityDataBySku;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $preOrderHelper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurable;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
        $this->configurable = $configurable;
        $this->request = $request;
        $this->getSalableQuantityDataBySku = null;
        $this->preOrderHelper = null;
    }

    /**
     * Check if module PreOrder enable
     *
     * @param $storeId
     * @return bool
     */
    private function isPreOrderEnabled($storeId = null) {
        return $this->scopeConfig->isSetFlag('preorder/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check if config allow mixing order enable
     *
     * @param $storeId
     * @return bool
     */
    private function isAllowMixingPreOrder($storeId = null) {
        return $this->isPreOrderEnabled() && $this->scopeConfig->isSetFlag(
                'preorder/general/mix',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId);
    }

    /**
     * Update preorder status before add to cart
     * If child product is not preorder, set param is_preorder = 0 before add to cart
     *
     * @param $subject
     * @param $productInfo
     * @param $requestInfo
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        if ($this->moduleManager->isEnabled('Bss_PreOrder') && !$this->isAllowMixingPreOrder()
            && is_object($productInfo) && $productInfo->getTypeId() === 'configurable') {
            $childProduct = $this->configurable->getProductByAttributes(
                $requestInfo['super_attribute'],
                $productInfo
            );

            $isSetPreOrder = $childProduct->getData('pre_order_status');

            if ($this->moduleManager->isEnabled('Magento_Inventory') && $this->getSalableQuantityDataBySku) {
                $isSalable = (bool)$this->getSalableQuantityDataBySku->execute($childProduct->getSku());
            } else {
                $stockItem = $this->stockRegistry->getStockItem($childProduct->getId());
                $isSalable = $stockItem->getIsInStock();
            }

           if ($this->preOrderHelper) {
               $isAvailableDate = $this->preOrderHelper->isAvailablePreOrder($childProduct->getId());

               if (($isAvailableDate && $isSetPreOrder == 1) || ($isSetPreOrder == 2 && !$isSalable)) {
                   return [$productInfo, $requestInfo];
               } else {
                   $this->request->setParam('is_preorder', 0);
               }
           }
        }
        return [$productInfo, $requestInfo];
    }
}
