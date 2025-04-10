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

class ProfileLayoutType implements \Magento\Framework\Option\ArrayInterface
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
                'label' => __('Layout 1'),
                'value' => 'default'
            ],
            [
                'label' => __('Layout 2'),
                'value' => 'type2'
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
            'default' => __('Layout 1'),
            'type2' => __('Layout 2')
        ];
    }
}
