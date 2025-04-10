<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphQl
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApiGraphQl\Block\Sales\Order\Invoice;

/**
 * Class Totals to calculate total
 */
class Totals extends \Magento\Sales\Block\Order\Invoice\Totals
{
    /**
     * Function to remove grandTotals
     *
     * @return object this
     */
    public function _initTotals()
    {
        parent::_initTotals();
        $this->removeTotal("base_grandtotal");
        return $this;
    }
}
