<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class ReturnAddressSource implements OptionSourceInterface
{
    public const GENERAL_STORE_ADDRESS = 0;
    public const SHIPPING_ORIGIN_ADDRESS = 1;
    public const CUSTOM_ADDRESS = 2;

    public function toOptionArray()
    {
        $optionArray = [];

        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }
        return $optionArray;
    }

    public function toArray(): array
    {
        return [
            self::GENERAL_STORE_ADDRESS => __('General Store Address'),
            self::SHIPPING_ORIGIN_ADDRESS => __('Shipping Origin Address'),
            self::CUSTOM_ADDRESS => __('Custom Address'),
        ];
    }
}
