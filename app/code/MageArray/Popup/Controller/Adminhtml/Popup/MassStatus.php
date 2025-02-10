<?php
namespace MageArray\Popup\Controller\Adminhtml\Popup;

use Magento\Backend\App\Action;

/**
 * Class MassStatus
 * @package MageArray\Popup\Controller\Adminhtml\Popup
 */
class MassStatus extends \Magento\Backend\App\Action
{

    /**
     * @return mixed
     */
    public function execute()
    {
        $popIds = $this->getRequest()->getParam('popup');
        if (!is_array($popIds) || empty($popIds)) {
            $this->messageManager->addError(__('Please select popup(s).'));
        } else {
            try {
                $status = (int)$this->getRequest()->getParam('status');
                foreach ($popIds as $_popId) {
                    $popModel = $this->_objectManager->get(\MageArray\Popup\Model\Popup::Class)->load($_popId);
                    $popModel->setIsActive($status)->save();
                }
                $this->messageManager->addSuccess(
                    __(
                        'A total of %1 record(s) have been updated.',
                        count($popIds)
                    )
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageArray_Popup::popup');
    }
}
