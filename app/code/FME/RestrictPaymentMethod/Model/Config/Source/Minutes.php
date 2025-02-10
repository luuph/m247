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

class Minutes implements ArrayInterface
{
    /**
     * @return array
     */

    public function toOptionArray()
    {
        for ($i=0; $i<60; $i++) {
            if ($i<10) {
                $options[$i] = [
                'label' => '0'.__($i),
                'value' => '0'.__($i)
                ];
            } else {
                 $options[$i] = [
                'label' => __($i),
                'value' => __($i)
                 ];
            }
        }
        return $options;
    }
// }

    // public function toOptionArray()
    // {

    //     $options = [
    //         0 => ['label' => '00','value' => '00'],
    //         1 => ['label' => '01','value' => '01'],
    //         2 => ['label' => '02','value' => '02'],
    //         3 => ['label' => '03','value' => '03'],
    //         4 => ['label' => '04','value' => '04'],
    //         5 => ['label' => '05','value' => '05'],
    //         6 => ['label' => '06','value' => '06'],
    //         7 => ['label' => '07','value' => '07'],
    //         8 => ['label' => '08','value' => '08'],
    //         9 => ['label' => '09','value' => '09'],
    //         10 => ['label' => '10','value' => '10'],
    //         11 => ['label' => '11','value' => '11'],
    //         12 => ['label' => '12','value' => '12'],
    //         13 => ['label' => '13','value' => '13'],
    //         14 => ['label' => '14','value' => '14'],
    //         15 => ['label' => '15','value' => '15'],
    //         16 => ['label' => '16','value' => '16'],
    //         17 => ['label' => '17','value' => '17'],
    //         18 => ['label' => '18','value' => '18'],
    //         19 => ['label' => '19','value' => '19'],
    //         20 => ['label' => '20','value' => '20'],
    //         21 => ['label' => '21','value' => '21'],
    //         22 => ['label' => '22','value' => '22'],
    //         23 => ['label' => '23','value' => '23'],

    //     ];
    //     return $options;
    // }
}
