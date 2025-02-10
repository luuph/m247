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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableProductWholesale\Block\Product\Renderer;

use Bss\ConfigurableProductWholesale\Helper\Data as WholesaleData;
use Bss\ConfigurableProductWholesale\Helper\MagentoHelper;
use Bss\ConfigurableProductWholesale\Model\ConfigurableData;
use Bss\ConfigurableProductWholesale\Model\Table\DataList;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Block\Product\Renderer\Configurable;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableProductWholesale extends Configurable
{
    /**
     * Template of product has swatches
     */
    const WHOLESALE_SWATCHES_TEMPLATE = 'product/view/renderer.phtml';

    /**
     * Template of normal product
     */
    const WHOLESALE_TEMPLATE = 'product/view/configurable.phtml';

    /**
     * @var WholesaleData
     */
    private $helperBss;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var /Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public $stockRegistry;

    /**
     * @var CollectionFactory
     */
    private $attrOptionCollectionFactory;

    /**
     * @var ConfigurableProductType
     */
    private $configurableProductType;

    /**
     * @var ConfigurableData
     */
    private $configurableData;

    /**
     * @var ConfigurableData
     */
    private $dataList;

    /**
     * @var Json
     */
    private $serialize;

    /**
     * @var MagentoHelper
     */
    protected $magentoHelper;

    /**
     * ConfigurableProductWholesale constructor.
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param StockStateInterface $stockState
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param ProductRepository $productRepository
     * @param WholesaleData $helperBss
     * @param CollectionFactory $attrOptionCollectionFactory
     * @param ConfigurableProductType $configurableProductType
     * @param ConfigurableData $configurableData
     * @param DataList $dataList
     * @param Json $serialize
     * @param MagentoHelper $magentoHelper
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        StockStateInterface $stockState,
        StockRegistryProviderInterface $stockRegistryProvider,
        ProductRepository $productRepository,
        WholesaleData $helperBss,
        CollectionFactory $attrOptionCollectionFactory,
        ConfigurableProductType $configurableProductType,
        ConfigurableData $configurableData,
        DataList $dataList,
        Json $serialize,
        MagentoHelper $magentoHelper,
        array $data = []
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
            $data
        );
        $this->helperBss = $helperBss;
        $this->stockState = $stockState;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->productRepository = $productRepository;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->configurableProductType = $configurableProductType;
        $this->configurableData = $configurableData;
        $this->dataList = $dataList;
        $this->serialize = $serialize;
        $this->magentoHelper = $magentoHelper;
    }

    /**
     * Return renderer template wholesale
     *
     * @return string
     */
    public function getRendererTemplate()
    {
        if ($this->helperBss->isModuleEnabled()) {
            if ($this->helperBss->validateMagentoVersion('2.1.6')) {
                $hasSwatch = $this->isProductHasSwatchAttribute();
            } else {
                $hasSwatch = $this->isProductHasSwatchAttribute;
            }
            if ($hasSwatch) {
                return self::WHOLESALE_SWATCHES_TEMPLATE;
            } else {
                return self::WHOLESALE_TEMPLATE;
            }
        } else {
            return parent::getRendererTemplate();
        }
    }

    /**
     * Get data json.
     *
     * @return string
     */
    public function getDataJson()
    {
        $data = [];
        $tierPrices = [];

        if (!$this->helperBss->isAjax($this->getProduct())) {
            $currentProduct = $this->getProduct();
            $productCollection = $this->configurableProductType->getUsedProductCollection($currentProduct);

            //Sort product like backend(sort by attribute).
            $attributeTable = $this->serialize->unserialize($this->getAttributeData());
            if (isset($attributeTable["code"])) {
                $productCollection = $productCollection->addAttributeToSort($attributeTable["code"]);
            }

            $preparedCollection = $this->dataList->prepareCollection($productCollection);
            $data = $this->dataList->getData($preparedCollection);
        }

        foreach ($data as $productId => $product) {
            if (isset($product['tierPrice'])) {
                foreach ($product['tierPrice'] as $key => $tierPrice) {
                    $tierPrices[$productId][$key]['qty'] = $tierPrice['qty'];
                    $tierPrices[$productId][$key]['price'] = $tierPrice['price'];
                    $tierPrices[$productId][$key]['price_excl_tax'] = $tierPrice['price_excl_tax'];
                }
            }
        }

        foreach ($tierPrices as $productId1 => $tierPrice1) {
            foreach ($tierPrices as  $productId2 => $tierPrice2) {
                if ($tierPrice1 === $tierPrice2) {
                    $data[$productId1]['checkAdvanceTierPrice'][$productId1] = $productId1;
                    $data[$productId1]['checkAdvanceTierPrice'][$productId2] = $productId2;
                }
            }
        }

        return $this->serialize->serialize($data);
    }

    /**
     * @return string
     */
    public function getPreselectData()
    {
        $product = $this->getProduct();
        $preselect = '';
        if ($product->getPreselectData()) {
            $preselect = $product->getPreselectData();
        }
        return $this->serialize->serialize($preselect);
    }

    /**
     * @return WholesaleData
     */
    public function getHelper()
    {
        return $this->helperBss;
    }

    /**
     * @return MagentoHelper
     */
    public function getMagentoHelper()
    {
        return $this->magentoHelper;
    }

    /**
     * @return string
     */
    public function getAttributeData()
    {
        $currentProduct = $this->getProduct();
        $productCollection = $this->configurableProductType->getUsedProductCollection($currentProduct);
        $options = $this->helper->getOptions($currentProduct, $productCollection);
        $productAttribute =  $this->configurableAttributeData->getTableOrdering($currentProduct, $options);
        return $this->serialize->serialize($productAttribute);
    }

    /**
     * @return string
     */
    public function getSwatchAttributeData()
    {
        $currentProduct = $this->getProduct();
        $productAttribute =  $this->configurableAttributeData->getAttributesDataTableOrdering($currentProduct);
        return $this->serialize->serialize($productAttribute);
    }

    /**
     * Get child product index
     * @return string
     */
    public function getProductOptionsIndex()
    {
        $options = $this->helper->getOptions($this->getProduct(), $this->getAllowProducts());
        $index = '';
        if (isset($options['index']) && !empty($options['index'])) {
            $index = $options['index'];
        }

        return $this->serialize->serialize($index);
    }

    /**
     * @return array
     */
    public function getJsonConfigTable()
    {
        $currentProduct = $this->getProduct();
        $options = $this->helper->getOptions($currentProduct, $this->getAllowProducts());
        return $this->configurableAttributeData->getTableOrdering($currentProduct, $options);
    }

    /**
     * @return object
     */
    public function getStockItem()
    {
        $productId = $this->getProduct()->getId();
        return $this->stockRegistry->getStockItem($productId);
    }

    /**
     * Get Product Information
     *
     * @return string
     */
    public function getJsonConfigTableOrdering()
    {
        $store = $this->getCurrentStore();
        $currentProduct = $this->getProduct();

        $regularPrice = $currentProduct->getPriceInfo()->getPrice('regular_price');
        $finalPrice = $currentProduct->getPriceInfo()->getPrice('final_price');
        $allowProducts = $this->getAllowProducts();
        $options = $this->helper->getOptions($currentProduct, $allowProducts);

        //fix 2.2
        if ($this->helperBss->validateMagentoVersion('2.2.0')) {
            foreach ($allowProducts as $product) {
                $productId = $product->getId();
                $tableImages = $this->helper->getGalleryImages($product);
                if ($tableImages) {
                    foreach ($tableImages as $image) {
                        $options['images'][$productId][] =
                            [
                                'thumb' => $image->getData('small_image_url'),
                                'img' => $image->getData('medium_image_url'),
                                'full' => $image->getData('large_image_url'),
                                'caption' => $image->getLabel(),
                                'position' => $image->getPosition(),
                                'isMain' => $image->getFile() == $product->getImage(),
                            ];
                    }
                }
            }
        }

        $attributesData = $this->configurableAttributeData->getAttributesDataTableOrdering($currentProduct, $options);

        $config = [
            'attributes' => $attributesData['attributes'],
            'template' => str_replace('%s', '<%- data.price %>', $store->getCurrentCurrency()->getOutputFormat()),
            'optionPrices' => $this->getOptionPrices(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->_registerJsPrice($regularPrice->getAmount()->getValue()),
                ],
                'basePrice' => [
                    'amount' => $this->_registerJsPrice($finalPrice->getAmount()->getBaseAmount()),
                ],
                'finalPrice' => [
                    'amount' => $this->_registerJsPrice($finalPrice->getAmount()->getValue()),
                ],
            ],
            'productId' => $currentProduct->getId(),
            'chooseText' => __('Choose an Option...'),
            'images' => isset($options['images']) ? $options['images'] : [],
            'index' => isset($options['index']) ? $options['index'] : [],
        ];

        if ($currentProduct->hasPreconfiguredValues() && !empty($attributesData['defaultValues'])) {
            $config['defaultValues'] = $attributesData['defaultValues'];
        }

        $config = array_merge($config, $this->_getAdditionalConfig());

        return $this->serialize->serialize($config);
    }

    /**
     * @return array
     */
    protected function _getAdditionalConfig()
    {
        $result = parent::_getAdditionalConfig();
        $product = $this->getProduct();
        $this->_eventManager->dispatch('bss_prepare_product_price', ['product' => $product]);
        if ($product->getBssHidePrice()) {
            $result['prices'] = [
                'oldPrice' => [
                    'amount' => $product->getBssHidePriceHtml(),
                ],
                'basePrice' => [
                    'amount' => $product->getBssHidePriceHtml(),
                ],
                'finalPrice' => [
                    'amount' => $product->getBssHidePriceHtml(),
                ],
            ];
        }
        return $result;
    }

    /**
     * Get Count Configurable Product Attributes
     *
     * @return int
     */
    public function getCountAttributes()
    {
        $product = $this->getProduct();
        return $this->configurableData->countAttributes($product);
    }

    /**
     * Check version
     *
     * @return bool
     */
    public function checkVersion()
    {
        return $this->helperBss->validateMagentoVersion('2.2.7');
    }
}
