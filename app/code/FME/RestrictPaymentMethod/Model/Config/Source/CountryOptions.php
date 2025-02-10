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

class CountryOptions implements ArrayInterface
{
    /**
     * @return array
     */

    public function toOptionArray()
    {
        $options = [
            0 => [
                'label' => __('--Please Select--'),
                'value' => ''
            ],
            1 => [
                'label' => 'Restrict By Country',
                'value' => 0
            ],
            2 => [
                'label' => 'Restrict By Region',
                'value' => 1
            ],
        ];

        return $options;
    }
}
