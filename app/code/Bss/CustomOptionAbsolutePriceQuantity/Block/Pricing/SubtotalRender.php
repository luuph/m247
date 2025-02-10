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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Block\Pricing;

use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleConfig;
use Bss\CustomOptionAbsolutePriceQuantity\Helper\ModuleHelper;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;

class SubtotalRender extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var PricingHelper
     */
    private $priceHelper;

    /**
     * @var ModuleConfig
     */
    private $moduleHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $taxHelper;

    /**
     * SubtotalRender constructor.
     * @param TemplateContext $context
     * @param Registry $registry
     * @param ModuleConfig $moduleConfig
     * @param ModuleHelper $moduleHelper
     * @param PricingHelper $priceHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Catalog\Helper\Data $taxHelper
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        ModuleConfig $moduleConfig,
        ModuleHelper $moduleHelper,
        PricingHelper $priceHelper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Catalog\Helper\Data $taxHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->moduleConfig = $moduleConfig;
        $this->moduleHelper = $moduleHelper;
        $this->priceHelper = $priceHelper;
        $this->json = $json;
        $this->taxHelper = $taxHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     *
     * @return float
     */
    public function getProductFinalPrice()
    {
        if ($this->getProduct()->getTypeId() === 'bundle') {
            return $this->getProduct()->getPriceInfo()->getPrice('base_price')->getAmount()->getValue();
        }
        $result = $this->getProduct()->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        if ($this->getProduct()->getTypeId() === 'configurable'
            && $this->moduleConfig->getMagentoVersion() < '2.2.0'
        ) {
            return $this->priceHelper->currency($result, false, false);
        }
        return $result;
    }

    /**
     * @return float
     */
    public function getProductBaseFinalPrice()
    {
        if ($this->getProduct()->getTypeId() === 'bundle') {
            return $this->getProduct()->getPriceInfo()->getPrice('base_price')->getAmount()->getBaseAmount();
        }
        $result = $this->getProduct()->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();
        if ($this->getProduct()->getTypeId() === 'configurable'
            && $this->moduleConfig->getMagentoVersion() < '2.2.0'
        ) {
            return $this->priceHelper->currency($result, false, false);
        }
        return $result;
    }

    /**
     * @return ModuleConfig
     */
    public function getConfigHelper()
    {
        return $this->moduleConfig;
    }

    /**
     * {@inheritdoc}
     * return mixed
     */
    public function toHtml()
    {
        if (!$this->getProduct()->getOptions()) {
            return '';
        }
        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getTierPricesData()
    {
        return $this->moduleHelper->getJsonTierPricesData($this->getProduct());
    }

    /**
     * @return string
     */
    public function getPricesData()
    {
        return $this->moduleHelper->getJsonPricesData($this->getProduct());
    }

    /**
     * @return string
     */
    public function getTierPricesOptionData()
    {
        return $this->moduleHelper->getOptionTierPrices($this->getProduct());
    }

    /**
     * @return bool|false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionQtyData()
    {
        $data = [];
        if ($this->getProduct()->getOptions()) {
            foreach ($this->getProduct()->getOptions() as $option) {
                $block = $this->getLayout()->createBlock(
                    \Bss\CustomOptionAbsolutePriceQuantity\Block\Render\QtyBox::class
                );
                $block->setProduct($this->getProduct())->setOption($option);
                $output = $block->toHtml();
                $data[$option->getOptionId()] = $output;
            }
        }
        return $this->json->serialize($data);
    }

    /**
     * @return bool|false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAbsToolTip()
    {
        $data = [];
        if ($this->getProduct()->getOptions()) {
            foreach ($this->getProduct()->getOptions() as $option) {
                $block = $this->getLayout()->createBlock(
                    \Bss\CustomOptionAbsolutePriceQuantity\Block\Render\Tooltip::class
                );
                $block->setProduct($this->getProduct())->setOption($option);
                $output = $block->toHtml();
                $data[$option->getOptionId()] = $output;
            }
        }
        return $this->json->serialize($data);
    }

    /**
     * Get regular price exclude tax
     *
     * @return float
     */
    public function getRegularPrice()
    {
        return $this->getProduct()->getPrice();
    }

    /**
     * Get regular price incl tax
     *
     * @return float
     */
    public function getRegularPriceIncl()
    {
        $product = $this->getProduct();
        $regularPrice = $product->getPrice();
        return $this->taxHelper->getTaxPrice($product, $regularPrice, true);
    }
}
