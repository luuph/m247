<?php

namespace Unveels\AddCategoryTypeCarousel\Model\Carousel;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type model
 */
class Type implements OptionSourceInterface
{
    /**
     * ToOptionArray function
     */
    public function toOptionArray()
    {
        return [
            ["label"=>__("Image Type"), "value"=>1],
            ["label"=>__("Product Type"), "value"=>2],
            ["label"=>__("Category Type"), "value"=>3]
        ];
    }
}
