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

namespace Bss\ConfigurableMatrixView\Block\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Bss\ConfigurableMatrixView\Helper\Data as HelperData;
use Bss\ConfigurableMatrixView\Model\ResourceModel\Product\Type\Configurable as ResourceTypeConfigurable;


class ConfigurableMatrix extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    protected $_isMagentoVersion22;

    protected $_isMagentoVersion16;

    protected $localeFormat;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ResourceTypeConfigurable
     */
    protected $resourceTypeConfigurable;


    public function __construct(
        Context                   $context,
        ArrayUtils                $arrayUtils,
        EncoderInterface          $jsonEncoder,
        Data                      $helper,
        CatalogProduct            $catalogProduct,
        CurrentCustomer           $currentCustomer,
        PriceCurrencyInterface    $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData                $swatchHelper,
        Media                     $swatchMediaHelper,
        HelperData                $helperData,
        ResourceTypeConfigurable $resourceTypeConfigurable,
        array                     $data = [],
        SwatchAttributesProvider  $swatchAttributesProvider = null,
        UrlBuilder                $imageUrlBuilder = null
    ) {
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data,
            $swatchAttributesProvider,
            $imageUrlBuilder
        );
        $this->resourceTypeConfigurable = $resourceTypeConfigurable;
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getJsonConfigNoMatrix()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();

        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');

        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);
        $attribute_matrix = $this->getAttributeMatrix();

        foreach (array_keys($attribute_matrix) as $key) {
            unset($attributesData['attributes'][$key]);
        }

        if (!isset($attributesData['attributes']) || empty($attributesData['attributes'])) {
            return false;
        }

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->localeFormat->getNumber($regularPrice->getAmount()->getValue()),
                ],
                'basePrice' => [
                    'amount' => $this->localeFormat->getNumber($finalPrice->getAmount()->getBaseAmount()),
                ],
                'finalPrice' => [
                    'amount' => $this->localeFormat->getNumber($finalPrice->getAmount()->getValue()),
                ],
            ],
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'index' => isset($options['index']) ? $options['index'] : [],
        ];

        if ($this->_isMagentoVersion22) {
            $config['images'] = $this->getOptionImages();
        } else {
            $config['images'] = isset($options['images']) ? $options['images'] : [];
        }

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->jsonEncoder->encode($config);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getJsonConfigMHide()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();

        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');

        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        $attribute_matrix = $this->getAttributeMatrix();
        foreach (array_keys($attributesData['attributes']) as $key) {
            if (!isset($attribute_matrix[$key])) {
                unset($attributesData['attributes'][$key]);
            }
        }

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'currencyFormat' => $store->getCurrentCurrency()->getOutputFormat(),
            'optionPrices' => $this->getOptionPrices(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->localeFormat->getNumber($regularPrice->getAmount()->getValue()),
                ],
                'basePrice' => [
                    'amount' => $this->localeFormat->getNumber($finalPrice->getAmount()->getBaseAmount()),
                ],
                'finalPrice' => [
                    'amount' => $this->localeFormat->getNumber($finalPrice->getAmount()->getValue()),
                ],
            ],
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'index' => isset($options['index']) ? $options['index'] : [],
        ];

        if ($this->_isMagentoVersion22) {
            $config['images'] = $this->getOptionImages();
        } else {
            $config['images'] = isset($options['images']) ? $options['images'] : [];
        }

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->jsonEncoder->encode($config);
    }

    /**
     * @return string
     */
    public function getJsonConfigM()
    {
        $currentProduct = $this->getProduct();

        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        $attributesData = $this->configurableAttributeData->getAttributesData($currentProduct, $options);

        $config = [
            'attributes' => $attributesData['attributes'],
            'optionPrices' => $this->getOptionPrices(),
            'productId' => $currentProduct->getId(),
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->jsonEncoder->encode($config);
    }

    /**
     * @param Product $product
     * @param null $priceType
     * @param array $arguments
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPriceHtml(
        \Magento\Catalog\Model\Product $product,
                                       $priceType = null,
        array                          $arguments = []
    )
    {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = 'item_view';
        }

        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender && $priceType) {
            $price = $priceRender->render(
                $priceType,
                $product,
                $arguments
            );
        }
        return $price;
    }

    /**
     * @return bool
     */
    public function hasSwatchAttributeMatrix()
    {
        if ($this->_isMagentoVersion16) {
            return $this->isProductHasSwatchAttribute();
        } else {
            return $this->isProductHasSwatchAttribute;
        }
    }

    /**
     * @return array
     */
    public function getSwatchAttributesDataMatrix()
    {
        return $this->swatchHelper->getSwatchAttributesAsArray($this->getProduct());
    }

    /**
     * @return SwatchData
     */
    public function getHelperSwatch()
    {
        return $this->swatchHelper;
    }

    /**
     * @return Data
     */
    public function getHelperConfigurableProduct()
    {
        return $this->helper;
    }

    /**
     * @return EncoderInterface
     */
    public function getJsonEncoder()
    {
        return $this->jsonEncoder;
    }

    /**
     * @return ConfigurableAttributeData
     */
    public function getConfigurableAttributeData()
    {
        return $this->configurableAttributeData;
    }

    /**
     * @param $localeFormat
     * @return $this
     */
    public function setLocaleFormat($localeFormat)
    {
        $this->localeFormat = $localeFormat;
        return $this;
    }

    /**
     * @param $ver
     * @return $this
     */
    public function setMagentoVersion16($ver)
    {
        $this->_isMagentoVersion16 = $ver;
        return $this;
    }

    /**
     * @param $ver
     * @return $this
     */
    public function setMagentoVersion22($ver)
    {
        $this->_isMagentoVersion22 = $ver;
        return $this;
    }

    /**
     * Get AllowProducts
     *
     * @return Product[]|mixed
     */
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $this->setAllowProducts(
                $this->resourceTypeConfigurable->getUsedProductsConfigurable($this->getProduct())
            );
        }
        return $this->getData('allow_products');
    }
}
