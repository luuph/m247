<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Plugin;

use Closure;
use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Model\Config\Source\ShowBrandInfo;

/**
 * Class MiniCartItem
 * @package Mageplaza\Shopbybrand\Plugin
 */
class MiniCartItem
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * MiniCartItem constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param DefaultItem $subject
     * @param Closure $proceed
     * @param Item $item
     *
     * @return array
     */
    public function aroundGetItemData(DefaultItem $subject, Closure $proceed, Item $item)
    {
        $data      = $proceed($item);
        $brandData = $this->helperData->getItemBrandCheckoutData($item);

        return array_merge($data, $brandData);
    }
}
