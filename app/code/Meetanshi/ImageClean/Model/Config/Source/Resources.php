<?php

namespace Meetanshi\ImageClean\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Resources implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'unusedCategory', 'label' => 'Unused Category Images'],
            ['value' => 'unusedProduct', 'label' => 'Unused Product Images'],
            ['value' => 'dbRecordProduct', 'label' => "DB Record for Non Existing Product Images"],
        ];
    }
}
