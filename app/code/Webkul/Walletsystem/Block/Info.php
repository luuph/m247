<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block;

class Info extends \PayPal\Braintree\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_Walletsystem::info/default.phtml';

    /**
     * Get orderId
     *
     * @return int orderId
     */
    public function getOrderID()
    {
        return $this->getRequest()->getParam('order_id');
    }
}
