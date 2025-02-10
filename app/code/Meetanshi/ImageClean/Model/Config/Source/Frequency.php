<?php

namespace Meetanshi\ImageClean\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Frequency implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'monthly', 'label' => "Monthly"],
            ['value' => 'weekly', 'label' => "Weekly"],
            ['value' => 'daily', 'label' => "Daily"],
        ];
    }
}
