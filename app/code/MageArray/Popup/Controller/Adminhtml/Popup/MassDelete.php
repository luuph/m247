<?php
namespace MageArray\Popup\Controller\Adminhtml\Popup;

use Magento\Backend\App\Action;

/**
 * Class MassDelete
 * @package MageArray\Popup\Controller\Adminhtml\Popup
 */
class MassDelete extends \Magento\Backend\App\Action
{

    /**
     * @return mixed
     */
    public function execute()
    {
        $popIds = $this->getRequest()->getParam('popup');
        if (!is_array($popIds) || empty($popIds)) {
            $this->messageManager->addError(__('Please select at least one popup.'));
        } else {
            try {
                foreach ($popIds as $_popId) {
                    $popModel = $this->_objectManager
                        ->get(\MageArray\Popup\Model\Popup::Class)->load($_popId);
                    $popModel->delete();
                }
                $this->messageManager->addSuccess(
                    __(
                        'A total of %1 record(s) have been deleted.',
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
