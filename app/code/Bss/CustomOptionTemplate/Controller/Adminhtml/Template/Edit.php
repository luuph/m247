<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomOptionTemplate\Controller\Adminhtml\Template;

class Edit extends \Bss\CustomOptionTemplate\Controller\Adminhtml\Template
{
    /**
     * Edit Template
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->getConfig()->getTitle()->prepend(__('New Custom Option Template'));

        $templateId = $this->getRequest()->getParam(static::PARAM_CRUD_ID, null);
        $template = $this->templateFactory->create()->load($templateId);
        if ($templateId) {
            if ($template->getId()) {
                $resultPage->getConfig()->getTitle()->prepend(
                    __("Edit Custom Option Template '%1'", $template->getTitle())
                );
            } else {
                $this->messageManager->addErrorMessage(__('This template custom options no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        $template->getConditions()->setJsFormObject('template_conditions_fieldset');
        $this->coreRegistry->register('bss_custom_option_template', $template);
        return $resultPage;
    }
}
