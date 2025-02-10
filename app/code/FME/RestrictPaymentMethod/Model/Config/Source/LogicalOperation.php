<?php
/**
 * FME Restrict Payment Method  Model Config Source Options.
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2018 United Sol Private Limited (https://unitedsol.net)
 */
namespace FME\RestrictPaymentMethod\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class LogicalOperation implements ArrayInterface
{
    /**
     * @return array
     */

    public function toOptionArray()
    {
        $options = [
                0 => ['value' => '','label' => __('--Please Select--')],
                1 => ['value' => '==','label' => __('is')],
                2 => ['value' => '!=','label' => __('is not')],
                3 => ['value' => '>=','label' => __('equals or greater than')],
                4 => ['value' => '<=','label' => __('equals or less than')],
                5 => ['value' => '>','label' => __('greater than')],
                6 => ['value' => '<','label' => __('less than')],
        ];
        return $options;
    }
}
