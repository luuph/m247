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

namespace Webkul\Walletsystem\Controller\Adminhtml\Creditrules;

use Webkul\Walletsystem\Controller\Adminhtml\Creditrules as CreditrulesController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul Walletsystem Creditrules Controller Class
 */
class Creditrules extends CreditrulesController
{
    /**
     * Controller Execute function
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Walletsystem::walletcreditrules');
        $resultPage->getConfig()->getTitle()->prepend(__('Wallet System Credit Rules'));
        $resultPage->addBreadcrumb(__('Wallet System Credit Rules'), __('Wallet System Credit Rules'));
        return $resultPage;
    }
}
