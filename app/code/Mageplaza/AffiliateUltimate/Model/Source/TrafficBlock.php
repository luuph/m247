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
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Model\Source;

use Magento\Cms\Model\ResourceModel\Block\Collection as BlockCollection;

/**
 * Class TrafficBlock
 * @package Mageplaza\AffiliateUltimate\Model\Source
 */
class TrafficBlock extends BlockCollection
{
    const CUSTOM_BLOCK_LABEL = 'mp_affiliate_traffic_custom';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [
            [
                'value' => self::CUSTOM_BLOCK_LABEL,
                'label' => __('Custom')
            ]
        ];

        return array_merge($result, parent::toOptionArray());
    }
}
