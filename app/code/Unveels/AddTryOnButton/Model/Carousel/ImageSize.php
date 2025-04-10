<?php

namespace Unveels\AddTryOnButton\Model\Carousel;

use Magento\Framework\Data\OptionSourceInterface;

class ImageSize implements OptionSourceInterface
{
    /**
     * ToOptionArray function
     */
    public function toOptionArray()
    {
        return [
            ["label"=>__("Large"), "value"=>'large'],
            ["label"=>__("Medium"), "value"=>'medium'],
            ["label"=>__("Small"), "value"=>'small']
        ];
    }
}
