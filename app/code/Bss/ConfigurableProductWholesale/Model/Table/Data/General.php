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
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ConfigurableProductWholesale\Model\Table\Data;

use Bss\ConfigurableProductWholesale\Helper\Data as WholesaleData;
use Bss\ConfigurableProductWholesale\Model\DataInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

class General implements DataInterface
{
    const MODEL_NAME = 'general';

    /**
     * @var WholesaleData
     */
    private $helper;

    /**
     * @var ConfigurableProductType
     */
    protected $configurableProductType;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * General constructor.
     *
     * @param WholesaleData $helper
     * @param ConfigurableProductType $configurableProductType
     * @param ProductFactory $productFactory
     */
    public function __construct(
        WholesaleData $helper,
        ConfigurableProductType $configurableProductType,
        ProductFactory $productFactory
    ) {
        $this->helper = $helper;
        $this->configurableProductType = $configurableProductType;
        $this->productFactory = $productFactory;
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return self::MODEL_NAME;
    }

    /**
     * @param $productCollection
     * @return object
     */
    public function prepareCollection($productCollection)
    {
        return $productCollection;
    }

    /**
     * @param $productCollection
     * @return array
     */
    public function getData($productCollection)
    {
        $data = [];

        foreach ($productCollection as $product) {
            $productId = $product->getId();
            $this->helper->getEventManager()->dispatch('bss_cpd_prepare_product_info', ['product' => $product]);
            $this->helper->getEventManager()->dispatch('bss_prepare_product_price', ['product' => $product]);
            $priceModel = $product->getPriceInfo();
            $regularPrice = $priceModel->getPrice('regular_price');
            $finalPrice = $priceModel->getPrice('final_price');
            $canShowPrice = $product->getCanShowPrice();
            if ($canShowPrice === null || $canShowPrice) {
                $canShowPrice = true;
            }
            $data[$productId] = [
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'price' => $finalPrice->getAmount()->getValue(),
                'current_price' => $finalPrice->getAmount()->getValue(),
                'price_excl_tax' => $finalPrice->getAmount()->getBaseAmount(),
                'current_price_excl_tax' => $finalPrice->getAmount()->getBaseAmount(),
                'order_qty' => 0,
                'subtotal' => 0,
                'old_price' => $regularPrice->getAmount()->getValue(),
                'special_price' => $product->getSpecialPrice(),
                'subtotal_excl_tax' => 0,
                'hideprice_message' => $product->getBssHidePriceHtml(),
                'can_show_price' => $canShowPrice,
                'is_selected' => 0,
                'is_update_item' => 0,
                'is_update_value' => 0,
                'allow_pre_order' => $this->helper->convertTextPreOrder($product->getData('pre_order_status'))
            ];
        }

        return $data;
    }
}
