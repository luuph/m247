<?php

namespace WeltPixel\GA4\Helper;

use WeltPixel\GA4\Model\Dimension as DimensionModel;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableProducts  extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \WeltPixel\GA4\Helper\Data
     */
    protected $ga4Helper;


    /**
     * @var DimensionModel
     */
    protected $dimensionModel;


    /**
     * @param \WeltPixel\GA4\Helper\Data $ga4Helper
     * @param DimensionModel $dimensionModel
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \WeltPixel\GA4\Helper\Data $ga4Helper,
        DimensionModel $dimensionModel,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->ga4Helper = $ga4Helper;
        $this->dimensionModel = $dimensionModel;
    }

    /**
     * @param \Magento\Catalog\Model\Product $childProduct
     * @param boolean $isVariantEnabled
     * @param array $configurableOptions
     * @return array
     */
    public function getViewItemEventDataForSimpleProduct($childProduct, $isVariantEnabled, $configurableOptions)
    {
        $variant = '';
        if ($isVariantEnabled) {
            $variant = $this->getVariantForSimpleProduct($childProduct, $configurableOptions);
        }

        $currencyCode = $this->ga4Helper->getCurrencyCode();
        $productPrice = floatval(number_format($childProduct->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', ''));

        $productItemOptions = [];
        $productItemOptions['item_name'] = $this->ga4Helper->getProductName($childProduct);
        $productItemOptions['item_id'] = $this->ga4Helper->getGtmProductId($childProduct);
        $productItemOptions['affiliation'] = $this->ga4Helper->getAffiliationName();
        $productItemOptions['price'] = $productPrice;
        if ($this->ga4Helper->isBrandEnabled()) {
            $productItemOptions['item_brand'] = $this->ga4Helper->getGtmBrand($childProduct);
        }

        $productCategoryIds = $childProduct->getCategoryIds();
        $ga4Categories = $this->ga4Helper->getGA4CategoriesFromCategoryIds($productCategoryIds);
        $productItemOptions = array_merge($productItemOptions, $ga4Categories);
        $productItemOptions['quantity'] = 1;
        $productItemOptions['index'] = 0;
        $categoryName = $this->ga4Helper->getGtmCategoryFromCategoryIds($productCategoryIds);
        $productItemOptions['item_list_name'] = $categoryName;
        $productItemOptions['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : '';

        if ($this->ga4Helper->isVariantEnabled() && $variant) {
            $productItemOptions['item_variant'] = $variant;
        }

        /**  Set the custom dimensions */
        $customDimensions = $this->dimensionModel->getProductDimensions($childProduct, $this->ga4Helper);
        foreach ($customDimensions as $name => $value) :
            $productItemOptions[$name] = $value;
        endforeach;

        $ecommerceData = [
            'value' => $productPrice,
            'currency' => $currencyCode,
            'items' => [$productItemOptions],
            'event' => 'view_item'
        ];

        return  [
            'ecommerce' => $ecommerceData,
            'event' => 'view_item'
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $childProduct
     * @param array $configurableOptions
     * @return string
     */
    public function getVariantForSimpleProduct($childProduct, $configurableOptions)
    {
        $variant = [];
        foreach ($configurableOptions as $productAttributeOptions) {
            foreach ($productAttributeOptions as $attributeOption) {
                if ($attributeOption['sku'] == $childProduct->getSku()) {
                    $variant[] = $attributeOption['super_attribute_label'] . ": " . $attributeOption['option_title'];
                }
            }
        }

        if ($variant) {
            return implode(' | ', $variant);
        }

        return '';
    }
}
