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
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Model\Campaign;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Status
 * @package Mageplaza\Affiliate\Model\Campaign
 */
class Type implements ArrayInterface
{
    const PAY_SALE = 1;
    const PAY_LEAD = 2;
    const PAY_CLICK = 3;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::PAY_SALE,
                'label' => __('Pay Per Sale')
            ],
            [
                'value' => self::PAY_LEAD,
                'label' => __('Pay Per Lead')
            ],
            [
                'value' => self::PAY_CLICK,
                'label' => __('Pay Per Click')
            ],
        ];

        return $options;
    }

    /**
     * @return array
     */
    public function toOptionHash()
    {
        return [
            self::PAY_SALE => __('Pay Per Sale'),
//            self::PAY_LEAD => __('Pay Per Lead'),
//            self::PAY_CLICK => __('Pay Per Click')
        ];
    }
}
