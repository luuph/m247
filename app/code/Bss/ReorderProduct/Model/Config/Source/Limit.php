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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ReorderProduct\Model\Config\Source;

/**
 * Class Limit
 *
 * @package Bss\ReorderProduct\Model\Config\Source
 */
class Limit implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 5, 'label' => __('No')],
            ['value' => 10, 'label' => __('Continue button')],
            ['value' => 15, 'label' => __('View Cart button')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [5 => __('No'), 10 => __('Continue button'), 15 => __('View Cart button')];
    }
}
