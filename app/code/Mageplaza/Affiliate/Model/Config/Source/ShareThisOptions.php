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

namespace Mageplaza\Affiliate\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Urltype
 * @package Mageplaza\Affiliate\Model\Config\Source
 */
class ShareThisOptions implements ArrayInterface
{
    const NULL = "";
    const TYPE_INLINE = 'inline';
    const TYPE_STICKY = 'sticky';
    const TYPE_BOTH = 'both';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $array = [];
        foreach ($this->getOptionHash() as $key => $label) {
            $array[] = [
                'value' => $key,
                'label' => $label
            ];
        }

        return $array;
    }

    /**
     * @return array
     */
    public function getOptionHash()
    {
        $array = [
            self::NULL => __('-- Please Select --'),
            self::TYPE_INLINE => __('Inline Share Button'),
            self::TYPE_STICKY => __('Sticky Share Buttons'),
            self::TYPE_BOTH => __('Inline and Sticky Share Buttons')
        ];

        return $array;
    }
}
