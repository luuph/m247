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
 * @copyright Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableProductWholesale\Model;

use Bss\ConfigurableProductWholesale\Api\ConfigurableWholesaleRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\TierPriceStorageInterface;
use Magento\Framework\Exception\LocalizedException;

class ConfigurableWholesaleRepository implements ConfigurableWholesaleRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var TierPriceStorageInterface
     */
    protected $tierPriceStorage;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * ConfigurableWholesaleRepository constructor.
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param TierPriceStorageInterface $tierPriceStorage
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        ProductAttributeRepositoryInterface $attributeRepository,
        TierPriceStorageInterface $tierPriceStorage,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->tierPriceStorage = $tierPriceStorage;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param string $sku
     * @param null $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getChildData($sku, $storeId = null)
    {
        $data = [];
        try {
            if ($sku) {
                $product = $this->productRepository->get($sku, false, $storeId);
                if ($product) {
                    if ($product->getTypeId() != 'configurable') {
                        throw new  LocalizedException(__('This is not a Configurable Product'));
                    }
                    $productCollection = $product->getTypeInstance()->getUsedProducts($product);
                    $attributes = $product->getTypeInstance(true)
                        ->getConfigurableAttributes($product);

                    foreach ($productCollection as $childProduct) {
                        $data[] = [
                            'product_id' => $childProduct->getId(),
                            'sku' => $childProduct->getSku(),
                            'qty' => $childProduct->getData('quantity'),
                            'attributes' => $this->getAttributes($childProduct, $attributes),
                            'tier_price' => $this->getTierPrices($childProduct),
                            'price' => [
                                'product_price' => $childProduct->getPrice(),
                                'final_price' => $childProduct->getPriceInfo()->getPrice('final_price')->getAmount()
                                    ->getValue(),
                                'old_price' => $childProduct->getPriceInfo()->getPrice('regular_price')->getAmount()
                                    ->getValue()
                            ],
                            'stock' => [
                                'is_in_stock' => $this->stockRegistry->getStockItem($childProduct->getId())
                                    ->getIsInStock(),
                                'use_config_manage_stock' => $this->stockRegistry->getStockItem($childProduct->getId())
                                    ->getManageStock()
                            ]
                        ];
                    }
                    return $data;
                }
            }
        } catch (\Exception $exception) {
            throw new  LocalizedException(__($exception->getMessage()));
        }
        return [];
    }

    /**
     * @param $product
     * @param $attributes
     * @return array
     */
    private function getAttributes($product, $attributes)
    {
        $productAtt = [];
        foreach ($attributes as $attribute) {
            $attributeId = (int)$attribute->getAttributeId();
            $attributeCode = $this->attributeRepository->get($attributeId)->getAttributeCode();
            $productAtt[] = [
                'attribute_id' => $attributeId,
                'code' => $attributeCode,
                'label' => $product->getResource()->getAttribute($attributeCode)
                    ->getFrontend()->getValue($product)
            ];
        }
        return $productAtt;
    }

    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTierPrices($product)
    {
        $productTierPrice = [];
        $tierPrices = $this->tierPriceStorage->get([$product->getSku()]);
        foreach ($tierPrices as $price) {
            $productTierPrice[] = [
                'website_id' => $price['website_id'],
                'price_type' => $price['price_type'],
                'price' => $price['price'],
                'customer_group' => $price['customer_group'],
                'quantity' => $price['quantity']
            ];
        }
        return $productTierPrice;
    }
}
