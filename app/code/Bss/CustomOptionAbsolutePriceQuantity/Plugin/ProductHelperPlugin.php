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
namespace Bss\CustomOptionAbsolutePriceQuantity\Plugin;

class ProductHelperPlugin
{
    /**
     * @param \Magento\Catalog\Helper\Product $subject
     * @param mixed $product
     * @param mixed $buyRequest
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePrepareProductOptions(
        \Magento\Catalog\Helper\Product $subject,
        $product,
        $buyRequest
    ) {
        $product->setBssReorderOptionQty($buyRequest->getOptionQty());
    }
}
