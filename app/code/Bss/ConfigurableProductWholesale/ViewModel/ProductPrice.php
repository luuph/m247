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

namespace Bss\ConfigurableProductWholesale\ViewModel;

use Bss\ConfigurableProductWholesale\Helper\Data;

class ProductPrice implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * ProductPrice constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool|int|string
     */
    public function isAjax($product)
    {
        $ajaxConfig = $product->getBssCpwAjax();
        if ($ajaxConfig == 2 || $ajaxConfig === null) {
            $ajaxConfig = $this->helper->getConfig('/general/ajax_load');
        }
        return $ajaxConfig;
    }
}
