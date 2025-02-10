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
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableProductWholesale\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData as WholesaleModel;
use Bss\ConfigurableProductWholesale\Helper\Data as WholesaleData;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Catalog\Helper\Product;

class ConfigurableData
{
    /**
     * @var ConfigurableProductType
     */
    private $configurableProductType;

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var WholesaleData
     */
    private $helperBss;

    /**
     * @var CollectionFactory
     */
    private $attrOptionCollectionFactory;

    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProduct = null;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * @var WholesaleModel
     */
    protected $configurableAttributeData;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var int
     */
    protected $versionMagento = 0;

    /**
     * @param ConfigurableProductType $configurableProductType
     * @param ProductRepository $productRepository
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param WholesaleModel $configurableAttributeData
     * @param WholesaleData $helperBss
     * @param CollectionFactory $attrOptionCollectionFactory
     * @param Product $catalogProduct
     * @param Data $helper
     * @param StockStateInterface $stockState
     */
    public function __construct(
        ConfigurableProductType $configurableProductType,
        StockRegistryProviderInterface $stockRegistryProvider,
        WholesaleModel $configurableAttributeData,
        WholesaleData $helperBss,
        CollectionFactory $attrOptionCollectionFactory,
        Product $catalogProduct,
        Data $helper,
        StockStateInterface $stockState
    ) {
        $this->configurableProductType = $configurableProductType;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->configurableAttributeData = $configurableAttributeData;
        $this->helperBss = $helperBss;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->catalogProduct = $catalogProduct;
        $this->helper = $helper;
        $this->stockState = $stockState;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $mergedIds
     * @param null $allowProducts
     * @return string
     */
    public function getJsonChildInfo($product, $mergedIds = [], $allowProducts = null)
    {
        $ids = [];
        $data = [];
        $code = $this->getJsonConfigTable($product, $allowProducts);
        $configurableProduct = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($configurableProduct as $item) {
            array_push($ids, $item->getId());
        }
        $childData = $this->getConfigChildProductIds($product, $mergedIds, $code['code']);
        foreach ($childData as $child) {
            if (in_array($child['id'], $ids)) {
                $data[] = $child;
            }
        }
        return $this->helperBss->serialize($data);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param bool|null $allowProducts
     * @return array
     */
    public function getJsonConfigTable($product, $allowProducts = null)
    {
        if (!$allowProducts) {
            $allowProducts = $this->getAllowProducts($product);
        }
        $options = $this->helper->getOptions($product, $allowProducts);
        return $this->configurableAttributeData->getTableOrdering($product, $options);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $mergedIds
     * @param null|string $code
     * @return array|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfigChildProductIds($product, $mergedIds, $code = null)
    {
        if (!$product) {
            return false;
        }
        $this->checkVersionMagento();
        $showOutOfStockConfig = $this->helperBss->getDisplayOutOfStock();
        $storeId = $this->helperBss->getMagentoHelper()->getStoreId();
        $usedProducts = $this->configurableProductType->getUsedProductCollection($product)
            ->addAttributeToSelect('*')->addStoreFilter($storeId);
        if (!empty($mergedIds)) {
            $usedProducts->addFieldToFilter('entity_id', ['in' => $mergedIds]);
        }
        $childrenList = [];
        $options = $this->helper->getOptions($product, $this->getAllowProducts($product));
        $attributesDatas = $this->configurableAttributeData->getAttributesData($product, $options);
        foreach ($usedProducts as $productChild) {
            $isSaleable = $productChild->isSaleable();
            if ($isSaleable || $showOutOfStockConfig) {
                $childrenList[] = $this->getConfigProduct($productChild, $attributesDatas, $code);
            }
        }

        uasort(
            $childrenList,
            function ($a, $b) {
                return (int)$a['sort_order'] - (int)$b['sort_order'];
            }
        );

        $childrenList = array_values($childrenList);
        return $childrenList;
    }

    /**
     * @return $this
     */
    protected function checkVersionMagento()
    {
        if ($this->helperBss->validateMagentoVersion('2.3.0')
            && $this->helperBss->hasModuleEnabled('Magento_InventorySales')
        ) {
            $this->versionMagento = 1;
        }
        return $this;
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
            return  (float)$productChild->getQuantityBss();
        }
        return $this->stockState->getStockQty($childProductId, $websiteId);
    }

    /**
     * @param \Magento\Catalog\Model\Product $productChild
     * @param array $attributesDatas
     * @param null $code
     * @return array
     * @throws \Zend_Currency_Exception
     */
    public function getConfigProduct($productChild, $attributesDatas, $code = null)
    {
        $childProductId = $productChild->getId();
        $this->helperBss->getEventManager()->dispatch('bss_prepare_product_price', ['product' => $productChild]);
        $websiteId = $productChild->getStore()->getWebsiteId();
        $stockItem = $this->stockRegistryProvider->getStockItem($childProductId, $websiteId);
        $status = $this->getStatus($productChild, $childProductId, $websiteId);
        if (!empty($dataOptions = $this->pushOptions($attributesDatas, $productChild))) {
            $data['option'] = $dataOptions;
        }

        $optionId = $productChild->getData($code);
        $attr = $productChild->getResource()->getAttribute($code);
        $sortOrder = $this->sortOptions($attr, $optionId);

        $priceModel = $productChild->getPriceInfo();
        $regularPrice = $priceModel->getPrice('regular_price');
        $finalPrice = $priceModel->getPrice('final_price');
        $canShowPrice = $productChild->getCanShowPrice();
        if ($canShowPrice === null || $canShowPrice) {
            $canShowPrice = true;
        }
        $data = [
            'id' => $productChild->getId(),
            'sku' => $productChild->getSku(),
            'price' => $finalPrice->getAmount()->getValue(),
            'current_price' => $finalPrice->getAmount()->getValue(),
            'price_excl_tax' => $finalPrice->getAmount()->getBaseAmount(),
            'current_price_excl_tax' => $finalPrice->getAmount()->getBaseAmount(),
            'order_qty' => 0,
            'subtotal' => 0,
            'old_price' => $regularPrice->getAmount()->getValue(),
            'special_price' => $productChild->getSpecialPrice(),
            'subtotal_excl_tax' => 0,
            'quantity' => $status,
            'saleable_quantity' => 1,
            'sort_order' => $sortOrder,
            'min_order_qty' => (float) $stockItem->getMinSaleQty(),
            'max_order_qty' => (float) $stockItem->getMaxSaleQty(),
            'hideprice_message' => $productChild->getBssHidePriceHtml(),
            'can_show_price' => $canShowPrice,
            'is_selected' => 0,
            'is_update_item' => 0,
            'is_update_value' => 0,
            'allow_pre_order' => $this->helperBss->convertTextPreOrder($productChild->getData('preorder'))
        ];

        $tierPriceModel = $productChild->getPriceInfo()->getPrice('tier_price');
        $tierPricesList = $tierPriceModel->getTierPriceList();
        if (isset($tierPricesList) && !empty($tierPricesList)) {
            foreach ($tierPricesList as $price) {
                $data['tierPrice'][] = [
                    'qty' => $price['price_qty'],
                    'price' => $price['price']->getValue(),
                    'price_excl_tax' => $price['price']->getValue(['tax']),
                    'save_percent' => $tierPriceModel->getSavePercent($price['price'])
                ];
            }
        }
        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function countAttributes($product)
    {
        $options = $this->helper->getOptions($product, $this->getAllowProducts($product));
        $attributesDatas = $this->configurableAttributeData->getAttributesData($product, $options);
        return count($attributesDatas['attributes']);
    }

    /**
     * @param $attr
     * @param $optionId
     * @return string
     */
    private function sortOptions($attr, $optionId)
    {
        $sortOrder = '';
        $optionCollection = $this->attrOptionCollectionFactory->create();
        $option = $optionCollection->setAttributeFilter(
            $attr->getId()
        )->setPositionOrder(
            'asc',
            true
        )->addFieldToFilter(
            'main_table.option_id',
            ['eq' => $optionId]
        );
        $optionData = $option->getData();
        if (!empty($optionData) && !empty($optionData[0])) {
            $sortOrder = $optionData[0]['sort_order'];
        }
        return $sortOrder;
    }

    /**
     * @param array $attributesDatas
     * @param \Magento\Catalog\Model\Product $productChild
     * @return array
     */
    private function pushOptions($attributesDatas, $productChild)
    {
        $dataOptions = [];
        if (!empty($attributesDatas['attributes'])) {
            foreach ($attributesDatas['attributes'] as $attributesData) {
                $codeAttr = $attributesData['code'];
                $idAttr = $attributesData['id'];

                $codeProduct = $productChild->getData($codeAttr);
                if (isset($codeProduct)) {
                    $dataOptions['data-option-' . $idAttr] = $codeProduct;
                }
            }
        }
        return $dataOptions;
    }

    /**
     * Get Allowed Products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getAllowProducts($product)
    {
        $products = [];
        $showOutOfStockConfig = $this->helperBss->getDisplayOutOfStock();
        $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();
        $allProducts = $product->getTypeInstance()->getUsedProducts($product, null);
        foreach ($allProducts as $product) {
            if ($product->isSaleable() || $skipSaleableCheck || $showOutOfStockConfig) {
                $products[] = $product;
            }
        }
        return $products;
    }

    /**
     * @param \Magento\Catalog\Model\Product $productChild
     * @param int $childProductId
     * @param int $websiteId
     * @return float|string
     */
    public function getStatus($productChild, $childProductId, $websiteId)
    {
        if ($productChild->isAvailable()) {
            if (!$this->helperBss->getConfig('/general/stock_number')) {
                $status = 'In stock';
            } else {
                $status = $this->getQtyofChildProduct($productChild, $childProductId, $websiteId);
            }
        } else {
            $status = 'Out of stock';
        }
        return $status;
    }
}
