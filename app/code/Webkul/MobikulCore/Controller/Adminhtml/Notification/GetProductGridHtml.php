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

namespace Webkul\MobikulCore\Controller\Adminhtml\Notification;

/**
 * GetProductGridHtml Class controller
 */
class GetProductGridHtml extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    /**
     * Execute function
     *
     * @return void
     */
    public function execute()
    {
        $block = $this->_view->getLayout()->createBlock(
            \Webkul\MobikulCore\Block\Adminhtml\Notification\Edit\Tab\ProductGrid::class
        );
        $this->getResponse()->setBody($block->toHtml());
    }
}
