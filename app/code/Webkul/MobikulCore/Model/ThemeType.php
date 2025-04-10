<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Cmspages model
 */
class ThemeType implements OptionSourceInterface
{
    /**
     * ToOptionArray function
     *
     * @return void
     */
    public function toOptionArray()
    {
        $returnData[] =  [
            "value" => 0,
            "label" => __("Layout One")
        ];
        $returnData[] =  [
            "value" => 1,
            "label" => __("Layout Two")
        ];
        return $returnData;
    }
}
