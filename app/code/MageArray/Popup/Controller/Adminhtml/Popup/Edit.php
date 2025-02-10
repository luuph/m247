<?php
namespace MageArray\Popup\Controller\Adminhtml\Popup;

/**
 * Class Edit
 * @package MageArray\Popup\Controller\Adminhtml\Popup
 */
class Edit extends \MageArray\Popup\Controller\Adminhtml\Popup
{

    /**
     *
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('popup_id');
        $model = $this->_popupFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getPopupId()) {
                $this->messageManager->addError(
                    __(
                        'This Popup no longer exists.'
                    )
                );
                $this->_redirect('*/*/');
                return;
            }
        }
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('popup', $model);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageArray_Popup::popup');
    }
}
