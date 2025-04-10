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

namespace Webkul\MobikulCore\Controller\Adminhtml\Salesorder;

/**
 * Index Class controller
 */
class Index extends \Webkul\MobikulCore\Controller\Adminhtml\Salesorder
{
    /**
     * Execute function
     *
     * @return void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::salesorder");
        $resultPage->getConfig()->getTitle()->prepend(__("Mobikul Order History"));
        return $resultPage;
    }

    /**
     * IsAllowed function
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::salesorder");
    }
}
