<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Ui\Component\Form\Field;

class HomeLayoutType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Grid'),
                'value' => 'grid'
            ],
            [
                'label' => __('Masonry'),
                'value' => 'masonry'
            ]
        ];
        return $options;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionHash()
    {
        return [
            'grid' => __('Grid'),
            'masonry' => __('Masonry')
        ];
    }
}
