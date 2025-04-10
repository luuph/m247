<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Watch;

/**
 * Class Index for Watch
 */
class Index extends \Webkul\MobikulCore\Controller\Adminhtml\Watch
{
    /**
     * Execute Fucntion
     *
     * @return jSon
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::watch");
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Watch"));
        return $resultPage;
    }

    /**
     * Fucntion to check if this controller can be accessed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::watch");
    }
}
