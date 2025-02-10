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
 * @package    Bss_CheckoutWithDisplayCurrency
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConfigurableMatrixView\Plugin\Model;

class Cart
{
    /**
     * @param \Magento\Checkout\Model\Cart $cart
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterRemoveItem(\Magento\Checkout\Model\Cart $cart, $result)
    {
        $number_items = count($cart->getItems());
        if ($number_items > 0) {
            $cart->updateItems([])->save();
        }
        return $result;
    }
}
