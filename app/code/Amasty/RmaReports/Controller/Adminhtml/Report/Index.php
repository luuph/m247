<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Controller\Adminhtml\Report;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Amasty\RmaReports\Controller\Adminhtml\AbstractReport
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_RmaReports::report');
        $resultPage->addBreadcrumb(__('RMA'), __('RMA'));
        $resultPage->addBreadcrumb(__('Overview'), __('Overview'));
        $resultPage->getConfig()->getTitle()->prepend(__('Overview'));

        return $resultPage;
    }
}
