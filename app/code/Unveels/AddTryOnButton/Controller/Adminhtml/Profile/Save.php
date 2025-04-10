<?php
namespace Unveels\AddTryOnButton\Controller\Adminhtml\Profile;

use Exception;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magezon\Core\Helper\Data;
use Magezon\LookBook\Model\ProfileFactory;

class Save extends \Magezon\LookBook\Controller\Adminhtml\Profile\Save
{
    /**
     * @param \Magento\Backend\App\Action\Context                   $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param TimezoneInterface                                     $date
     * @param Data                                                  $coreHelper
     * @param ProfileFactory                                        $profileFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        TimezoneInterface $date,
        Data $coreHelper,
        ProfileFactory $profileFactory
    ) {
        parent::__construct($context, $dataPersistor, $date, $coreHelper, $profileFactory);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        die('DIEEEE');
        $data = $this->getRequest()->getPostValue();
        $redirectBack = $this->getRequest()->getParam('back', false);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if (empty($data['profile_id'])) {
            unset($data['profile_id']);
        }

        if ($data) {

            $model = $this->profileFactory->create();
            $id    = $this->getRequest()->getParam('profile_id');

            if (!isset($data['category_ids'])) {
                $data['category_ids'] = [];
            }

            try {
                $model->load($id);
                $markers = $this->coreHelper->unserialize($data['marker']);
                $listProduct = [];
                foreach ($markers as $key => $marker) {
                    $listProduct[$marker['sku']] = $key + 1;
                }
                if (isset($markers)) {
                    $data['posted_products'] = $listProduct;
                } 
                if ($id && !$model->getId()) {
                    throw new LocalizedException(__('This profile no longer exists.'));
                }
                $data['creation_time'] = $this->date->date($data['creation_time'])->format('Y-m-d H:i:s');
                
                // Handle the custom field 'try_on_url'
                if (isset($data['try_on_url'])) {
                    $model->setData('try_on_url', $data['try_on_url']);
                }
                
                $model->setData($data);
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the profile.'));

                if ($redirectBack === 'save_and_new') {
                    return $resultRedirect->setPath('*/*/new');
                }

                if ($redirectBack === 'save_and_close') {
                    return $resultRedirect->setPath('*/*/*');
                }

                return $resultRedirect->setPath('*/*/edit', ['profile_id' => $model->getId(), '_current' => true]);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the profile.'));
            }
            $this->dataPersistor->set('current_profile', $data);
            return $resultRedirect->setPath('*/*/edit', ['profile_id' => $this->getRequest()->getParam('profile_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
