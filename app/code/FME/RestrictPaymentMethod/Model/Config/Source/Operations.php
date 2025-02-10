<?php
/**
 * FME Restrict Payment Method  Model Config Source Options.
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @author    Adeel Anjum
 * @copyright Copyright (c) 2018 United Sol Private Limited (https://unitedsol.net)
 */
namespace FME\RestrictPaymentMethod\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Operations implements ArrayInterface
{
    /**
     * @return array
     */

    public function toOptionArray()
    {
        $options = [
            0 => [
                'label' => 'Apply as \'AND\'',
                'value' => 0
            ],
            1 => [
                'label' => 'Apply as \'OR\'',
                'value' => 1
            ],
        ];

        return $options;
    }
}
