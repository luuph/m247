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

namespace Bss\ConfigurableProductWholesale\Block\Pricing\Render;

class FinalPriceBox extends \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox
{
    const DEFAULT_FINAL_PRICE_TEMPLATE = 'Magento_ConfigurableProduct::product/price/final_price.phtml';
    const CUSTOM_FINAL_PRICE_TEMPLATE = 'Bss_ConfigurableProductWholesale::product/price/final_price.phtml';

    /**
     * @var \Bss\ConfigurableProductWholesale\Helper\Price|null
     */
    protected $_priceHelper;

    /**
     * @return string
     */
    public function getTemplate()
    {
        $this->setHelper();
        return parent::getTemplate();
    }

    /**
     * @return \Bss\ConfigurableProductWholesale\Helper\Price
     */
    public function getHelper()
    {
        return $this->_priceHelper;
    }

    /**
     * @param null|string|\Bss\ConfigurableProductWholesale\Helper\Price $priceHelper
     * @return $this
     */
    public function setHelper($priceHelper = null)
    {
        if ($priceHelper && (!$this->_priceHelper ||
            !$this->_priceHelper instanceof \Bss\ConfigurableProductWholesale\Helper\Price)) {
            $this->_priceHelper = $priceHelper;
        }
        return $this;
    }
}
