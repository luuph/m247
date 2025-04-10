<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MobikulCore\Controller\Adminhtml\Support;

use Magento\Backend\App\Action;

class Store extends Action
{
    /**
     * Support Userguide Link.
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl("https://store.webkul.com/magento-mobile-app.html");
        return $resultRedirect;
    }
}
