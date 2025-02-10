<?php
namespace MageArray\Popup\Controller\Adminhtml\Popup;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Save
 * @package MageArray\Popup\Controller\Adminhtml\Popup
 */
class Save extends \MageArray\Popup\Controller\Adminhtml\Popup
{

    /**
     *
     */
    public function execute()
    {
        $formPostValues = $this->getRequest()->getPostValue();
        /* echo "<pre>";
        print_r($formPostValues);
        exit; */
        if (isset($formPostValues) && !empty($formPostValues)) {
            $popupData = $formPostValues;

            if (isset($popupData) && !empty($popupData)) {
                if (isset($popupData['store_id']) && !empty($popupData['store_id'])) {
                    $storeIds = implode(',', $popupData['store_id']);
                    $popupData['store_id'] = $storeIds;
                }
            }

            if (isset($popupData) && !empty($popupData)) {
                if (isset($popupData['page']) && !empty($popupData['page'])) {
                    $page = implode(',', $popupData['page']);
                    $popupData['page'] = $page;
                } else {
                    $popupData['page'] = '';
                }
            }

            $popupId = isset($popupData['popup_id']) ? $popupData['popup_id'] : null;
            $model = $this->_popupFactory->create();
            if ($popupId) {
                $model->load($popupId);
            }



            if ($popupData['popup_type'] == 1) {

                if (isset($popupData['image'])) {
                    $imageData = $popupData['image'];
                    unset($popupData['image']);
                } else {
                    $imageData = [];
                }

                try {
                    $files = $this->getRequest()->getFiles();

                    if (isset($files['image']['name']) && $files['image']['name'] != '') {
                        $uploader = $this->_objectManager->create(
                            \Magento\MediaStorage\Model\File\Uploader::Class,
                            ['fileId' => 'image']
                        );

                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);
                        $mediaDirectory = $this->_objectManager
                        ->get(\Magento\Framework\Filesystem::Class)
                        ->getDirectoryRead(DirectoryList::MEDIA);
                        $result = $uploader->save($mediaDirectory
                        ->getAbsolutePath('popup/'));

                        if ($result['error'] == 0) {
                            $popupData['image'] = 'popup/' . $result['file'];
                        }
                    }
                } catch (\Exception $e) {
                     $this->messageManager->addError($e->getMessage());
                }

                if (isset($imageData['delete']) && $imageData['delete'] == '1') {
                    $popupData['image'] = '';
                }
            } else {
                if (isset($popupData['image'])) {
                    $imageData = $popupData['image'];
                    unset($popupData['image']);
                } else {
                    $imageData = [];
                }try {
                    $files = $this->getRequest()->getFiles();

                    if (isset($files['image']['name']) && $files['image']['name'] != '') {
                        $uploader = $this->_objectManager->create(
                            \Magento\MediaStorage\Model\File\Uploader::Class,
                            ['fileId' => 'image']
                        );

                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setFilesDispersion(false);
                        $mediaDirectory = $this->_objectManager
                        ->get(\Magento\Framework\Filesystem::Class)
                        ->getDirectoryRead(DirectoryList::MEDIA);
                        $result = $uploader->save($mediaDirectory
                        ->getAbsolutePath('popup/'));

                        if ($result['error'] == 0) {
                            $popupData['image'] = 'popup/' . $result['file'];
                        }
                    }
                } catch (\Exception $e) {
                     $this->messageManager->addError($e->getMessage());
                }

                if (isset($imageData['delete']) && $imageData['delete'] == '1') {
                    $popupData['image'] = '';
                }
            }

            $model->setData($popupData);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Popup has been saved.'));
                $this->_getSession()->setFormData(false);

                if ($this->getRequest()->getParam('back') === 'edit') {
                    $this->_redirect('*/*/edit', ['popup_id' => $model->getPopupId(), '_current' => true]);
                    return;
                } else {
                    if ($this->getRequest()->getParam('back') === "new") {
                        $this->_redirect('*/*/new', ['_current' => true]);
                        return;
                    }
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the Popup.'));
            }

            $this->_getSession()->setFormData($formPostValues);
            $this->_redirect('*/*/edit', ['popup_id' => $this->getRequest()->getParam('popup_id')]);
            return;
        }
        $this->_redirect('*/*/');
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageArray_Popup::popup');
    }
}
