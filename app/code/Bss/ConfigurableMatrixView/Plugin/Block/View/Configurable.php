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
namespace Bss\ConfigurableMatrixView\Plugin\Block\View;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as ConfigurableProduct;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventorySalesAdminUi\Model\ResourceModel\GetAssignedStockIdsBySku;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Swatches\Block\Product\Renderer\Configurable as ConfigurableSwatches;

class Configurable
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $helper;

    protected $product;

    /**
     * @var bool
     */
    protected $sametierPrice = false;

    /**
     * @var bool
     */
    protected $sametierPriceHtml = false;

    /**
     * @var array
     */
    protected $from_to_price = [];

    /**
     * @var array
     */
    protected $stockData = [];
    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory
     */

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaFactory;
    /**
     * @var \Magento\Framework\Locale\Format
     */

    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localeFormat;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $typeConfigurable;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var \Bss\ConfigurableMatrixView\Block\Product\View\ConfigurableMatrix
     */
    protected $configurableMatrix;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Locale\Format $localeFormat
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeConfigurable
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Bss\ConfigurableMatrixView\Helper\Data $helper
     * @param \Bss\ConfigurableMatrixView\Block\Product\View\ConfigurableMatrix $configurableMatrix
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Locale\Format $localeFormat,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeConfigurable,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Bss\ConfigurableMatrixView\Helper\Data $helper,
        \Bss\ConfigurableMatrixView\Block\Product\View\ConfigurableMatrix $configurableMatrix,
        \Magento\Framework\Module\Manager $moduleManager,
        GetProductSalableQtyInterface $getProductSalableQty,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
    ) {
        $this->request = $request;
        $this->localeFormat = $localeFormat;
        $this->eavConfig = $eavConfig;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->typeConfigurable = $typeConfigurable;
        $this->jsonDecoder = $jsonDecoder;
        $this->helper = $helper;
        $this->configurableMatrix = $configurableMatrix;
        $this->moduleManager = $moduleManager;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
    }

    /**
     * @return bool
     */
    public function isLayoutHandle()
    {
        return $this->request->getFullActionName() != 'catalog_product_view';
    }

    /**
     * @param ConfigurableProduct $subject
     * @param $result
     * @return string
     */
    public function afterGetTemplate(ConfigurableProduct $subject, $result)
    {
        if (!$this->isLayoutHandle()) {
            $this->product = $subject->getProduct();
            $count_child = (int)count($this->configurableMatrix->getAllowProducts());
            if ($this->product->getConfigurableMatrixView() && $this->helper->isEnabled() && $count_child > 0) {
                if ($result == ConfigurableSwatches::SWATCH_RENDERER_TEMPLATE
                    || $result == ConfigurableSwatches::CONFIGURABLE_RENDERER_TEMPLATE) {
                    $result = 'Bss_ConfigurableMatrixView::product/view/configurable.phtml';
                    $this->setBlockVariable();
                    $this->setBlockData($subject);
                }
            }
        }
        return $result;
    }

    /**
     *
     */
    public function setBlockVariable()
    {
        $this->configurableMatrix->setLocaleFormat($this->localeFormat);
        $this->configurableMatrix->setMagentoVersion16($this->helper->isMagentoVersion());
        $this->configurableMatrix->setMagentoVersion22($this->helper->isMagentoVersion22());
        $this->configurableMatrix->setData([
            'product'           => $this->product,
            'attribute_matrix'  => $this->getAttributeMatrix()
        ]);
    }

    /**
     * @param $subject
     */
    public function setBlockData($subject)
    {
        $subject->setData([
            'attribute_matrix'              => $this->getAttributeMatrix(),
            'attribute_matrix_array'        => $this->getAttributeMatrixArray(),
            'configurable_matrix_view_data' => $this->getConfigurableMatrixViewData(),
            'has_swatch_attribute_matrix'   => $this->configurableMatrix->hasSwatchAttributeMatrix(),
            'swatch_attributes_data_matrix' => $this->configurableMatrix->getSwatchAttributesDataMatrix(),
            'json_config_no_matrix'         => $this->configurableMatrix->getJsonConfigNoMatrix(),
            'json_config_m_hide'            => $this->configurableMatrix->getJsonConfigMHide(),
            'json_config_m'                 => $this->configurableMatrix->getJsonConfigM(),
            'json_swatch_no_matrix'         => $this->getJsonSwatchNoMatrix(),
            'rate_tax'                      => $this->getRateTax(),
            'price_format'                  => $this->getPriceFormat(),
            'price_range'                   => $this->getPriceRange(),
            'has_same_tier_price'           => $this->hasSameTierPrice(),
            'has_same_tier_price_html'      => $this->hasSameTierPriceHtml(),
            'price_display_type'            => $this->helper->getPriceDisplayType(),
            'helper_matrix_view'            => $this->getHelperMatrixView()
        ]);
    }

    /**
     * @return array
     */
    public function getAttributeMatrix()
    {
        $product = $this->getProduct();
        $productAttributeOptions = $this->typeConfigurable->getConfigurableAttributesAsArray($product);
        $attribute_code = [];

        foreach ($productAttributeOptions as $option) {
            if (in_array($option['attribute_code'], $attribute_code)) {
                continue;
            }

            $attribute = $this->eavConfig->getAttribute('catalog_product', $option['attribute_code']);

            if (!$attribute->getIsMatrixView()) {
                continue;
            }

            $attribute_code[$option['attribute_id']] = $option['attribute_code'];
        }

        return array_slice($attribute_code, -2, 2, true);
    }

    /**
     * @return array
     */
    public function getAttributeMatrixArray()
    {
        $product = $this->getProduct();
        $productAttributeOptions = $this->typeConfigurable->getConfigurableAttributesAsArray($product);
        $attributes_matrix = [];
        $attribute_matrix = $this->getAttributeMatrix();
        foreach (array_keys($attribute_matrix) as $k) {
            $attributes_matrix[] = $productAttributeOptions[$k];
        }

        return $attributes_matrix;
    }

    /**
     * @return array
     */
    public function getInfoTierPrices($tierPriceModel, $tierPricesList)
    {
        $tierPrices = [];
        $compareTierPrices = [];
        foreach ($tierPricesList as $tierPrice) {
            $tierPrices[] = [
                'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                'price' => $this->localeFormat->getNumber($tierPrice['price']->getValue()),
                'percentage' => $this->localeFormat->getNumber(
                    $tierPriceModel->getSavePercent($tierPrice['price'])
                )
            ];

            if (!isset($tierPrice['percentage_value']) || $tierPrice['percentage_value'] === null) {
                $compareTierPrices[] = [
                    'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                    'price' => $this->localeFormat->getNumber($tierPrice['price']->getValue())
                ];
            } else {
                $compareTierPrices[] = [
                    'qty' => $this->localeFormat->getNumber($tierPrice['price_qty']),
                    'percentage' => $this->localeFormat->getNumber(
                        $tierPriceModel->getSavePercent($tierPrice['price'])
                    )
                ];
            }
        }
        return ['tierPrices' => $tierPrices, 'compareTierPrices' => $compareTierPrices];
    }

    /**
     * @return $this
     */
    public function getDetailStockChildProduct()
    {
        $currentProduct = $this->getProduct();
        $childIds = $this->typeConfigurable->getChildrenIds($currentProduct->getId());
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter($childIds);
        $collection = $this->stockItemRepository->getList($criteria);
        $stockData = [];
        foreach ($collection->getItems() as $item) {
            $productId = $item->getProductId();
            $stockData[$productId] = [
                'manage_stock'   => $item->getManageStock(),
                'backorders'     => (bool)$item->getBackorders(),
                'is_qty_decimal' => $item->getIsQtyDecimal(),
                'qty'            => $item->getQty() - $item->getMinQty()
            ];
        }
        $this->stockData = $stockData;
        return $this;
    }

    public function getStockChild($product)
    {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $salableQty = $this->getProductSalableQty->execute($product->getSku(), $stockId);
        return $salableQty;
    }

    /**
     * @param $productId
     * @param $quantity
     * @return int
     */
    public function getStockStatus($productId, $quantity)
    {
        $stockData = $this->stockData;
        if ($stockData[$productId]['manage_stock'] && !$stockData[$productId]['backorders'] && $quantity == 0) {
            return 0;
        }
        return 1;
    }

    /**
     * @return string
     */
    public function getConfigurableMatrixViewData()
    {
        $this->getDetailStockChildProduct();
        $currentProduct = $this->getProduct();
        $childproducts = $this->configurableMatrix->getAllowProducts();
        $number_childs = (int)count($childproducts);
        $data = $product_allow = $qty = $is_in_stock = $tier_price = $_tierPrices = $_compareTierPrices = [];

        $price_array = [];
        $final_price = [];
        foreach ($this->configurableMatrix->getAllowProducts() as $product) {
            $priceInfo = $product->getPriceInfo();
            $tierPriceModel = $priceInfo->getPrice('tier_price');
            $tierPricesList = $tierPriceModel->getTierPriceList();
            $infoTierPrices = $this->getInfoTierPrices($tierPriceModel, $tierPricesList);
            $tierPrices = $infoTierPrices['tierPrices'];
            $compareTierPrices = $infoTierPrices['compareTierPrices'];

            $price_array[$product->getId()] = $product->getFinalPrice();
            $final_price[$product->getId()] = $this->configurableMatrix->getPriceHtml($product, 'final_price');

            if ($this->helper->canShowStock()) {
                $qty[$product->getId()] = $this->getStockChild($product);
                $is_in_stock[$product->getId()] = $product->getIsSalable();
            }

            $isManageStock[$product->getId()] = $this->stockData[$product->getId()]['manage_stock'];

            if ($this->helper->canShowTierPrice()) {
                $tier_price[$product->getId()] = $this->configurableMatrix->getPriceHtml($product, 'tier_price');
            }

            if (!empty($tierPrices)) {
                $_tierPrices[$product->getId()] = $tierPrices;
                $_compareTierPrices[$product->getId()] = $compareTierPrices;
                $number_childs--;
            }
            $product_allow[] = $product->getId();
        }

        if (!$this->helper->canShowUnitPrice()) {
            $final_price = [];
        }

        $min_price = (float)min($price_array);
        $max_price = (float)max($price_array);
        $this->sametierPrice = $this->helper->checkSameTierPrice($number_childs, array_values($_compareTierPrices));
        $this->sametierPriceHtml = $this->helper->checkSameTierPrice($number_childs, array_values($_tierPrices));

        if ($this->sametierPriceHtml && $this->helper->canShowTierPrice()) {
            $tier_price[$currentProduct->getId()] =  array_values($tier_price)[0];
            $tier_price  = [$currentProduct->getId() => $tier_price[$currentProduct->getId()]];
        }

        if ($min_price < $max_price) {
            array_unique($price_array);
            $minProductId = array_search($min_price, $price_array);
            $maxProductId = array_search($max_price, $price_array);

            $this->from_to_price = ['min' => $final_price[$minProductId], 'max' => $final_price[$maxProductId]];
        }

        $data = [
            'product_allow'     => $product_allow,
            'is_in_stock'       => $is_in_stock,
            'is_manage_stock'   => $isManageStock ?? 0,
            'final_price'       => $final_price,
            'tier_price'        => $tier_price,
            'tierPrices'        => $_tierPrices,
            'qty'               => $qty
        ];

        return $this->configurableMatrix->getJsonEncoder()->encode($data);
    }

    /**
     * @return string
     */
    public function getJsonSwatchNoMatrix()
    {
        $attribute_matrix = $this->getAttributeMatrix();
        $config = $this->jsonDecoder->decode($this->configurableMatrix->getJsonSwatchConfig());
        $config = array_diff_key($config, $attribute_matrix);
        return $this->configurableMatrix->getJsonEncoder()->encode($config);
    }

    /**
     * @return int
     */
    public function getRateTax()
    {
        $product = $this->getProduct();
        return $this->helper->getRateTax($product);
    }

    /**
     * @return string
     */
    public function getPriceFormat()
    {
        return $this->localeFormat->getPriceFormat();
    }

    public function getHelperMatrixView()
    {
        return $this->helper;
    }

    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return array
     */
    public function getPriceRange()
    {
        if ($this->helper->canShowPriceRange()) {
            return $this->from_to_price;
        }
        return [];
    }

    /**
     * @return bool
     */
    public function hasSameTierPrice()
    {
        return $this->sametierPrice;
    }

    /**
     * @return bool
     */
    public function hasSameTierPriceHtml()
    {
        return $this->sametierPriceHtml;
    }
}
