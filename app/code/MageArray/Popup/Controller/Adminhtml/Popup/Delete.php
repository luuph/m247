<?php
namespace MageArray\Popup\Controller\Adminhtml\Popup;

/**
 * Class Delete
 * @package MageArray\Popup\Controller\Adminhtml\Popup
 */
class Delete extends \Magento\Backend\App\Action
{

    /**
     * @return mixed
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('popup_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_objectManager->create(\MageArray\Popup\Model\Popup::Class);
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The Popup has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['popup_id' => $id]
                );
            }
        }
        $this->messageManager->addError(__('We can\'t find a Popup to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageArray_Faq::popup');
    }
}
