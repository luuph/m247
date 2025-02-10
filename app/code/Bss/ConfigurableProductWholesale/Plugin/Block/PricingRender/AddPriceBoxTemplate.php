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
namespace Bss\ConfigurableProductWholesale\Plugin\Block\PricingRender;

use Bss\ConfigurableProductWholesale\Block\Pricing\Render\FinalPriceBox;
use Bss\ConfigurableProductWholesale\Helper\Price as PriceHelper;

class AddPriceBoxTemplate
{
    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * AddPriceBoxTemplate constructor.
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        PriceHelper $priceHelper
    ) {
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param FinalPriceBox $finalPriceBox
     * @param string $template
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetTemplate(
        FinalPriceBox $finalPriceBox,
        $template
    ) {
        $finalPriceBox->setHelper($this->priceHelper);
        return $this->getPriceTemplate($finalPriceBox);
    }

    /**
     *  Get price template
     *
     * @param FinalPriceBox $finalPriceBox
     * @return string
     */
    public function getPriceTemplate($finalPriceBox)
    {
        $product = $finalPriceBox->getSaleableItem();

        if ($this->priceHelper->isModuleEnabled()
            && $this->priceHelper->getConfig('/general/range_price')
            && $product->getEnableCpwd() || $product->getEnableCpwd() == null
        ) {
            return FinalPriceBox::CUSTOM_FINAL_PRICE_TEMPLATE;
        }
        return FinalPriceBox::DEFAULT_FINAL_PRICE_TEMPLATE;
    }
}
