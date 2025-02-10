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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionAbsolutePriceQuantity\Plugin\Model\Quote\Item;

use Magento\Quote\Api\Data\CartItemInterface;

class CartItemOptionsProcessor
{
    /**
     * Set Bss Option Qty after get buy request to work with API
     *
     * @param \Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor $subject
     * @param $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetBuyRequest(\Magento\Quote\Model\Quote\Item\CartItemOptionsProcessor $subject, $result, $productType, CartItemInterface $cartItem)
    {
        $bssQtyOption = $cartItem->getProductOption() ? $cartItem->getProductOption()->getExtensionAttributes()->getQtyOptionAttribute() : '';
        $optionQty = [];
        if ($bssQtyOption) {
            foreach ($bssQtyOption as $data) {
                $optionQty[$data->getData('option_id')] = $data->getData('option_qty');
            }
            $result->setData('option_qty', $optionQty);
        }
        return $result;
    }
}
