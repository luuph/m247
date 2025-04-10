<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Controller\Adminhtml\Category;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\LayoutFactory;
use Magezon\LookBook\Model\Category;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magezon_LookBook::category_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->dataPersistor     = $dataPersistor;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory     = $layoutFactory;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        $hasError     = false;
        $data         = $this->getRequest()->getPostValue();
        $redirectBack = $this->getRequest()->getParam('back', false);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (empty($data['category_id'])) {
            unset($data['category_id']);
        }

        if ($data) {
            /** @var Category $model */
            $model = $this->_objectManager->create(Category::class);
            $id    = $this->getRequest()->getParam('category_id');
            try {
                $model->load($id);
                if ($id && !$model->getId()) {
                    throw new LocalizedException(__('This category no longer exists.'));
                }
                if (isset($data['category_profiles'])
                    && is_string($data['category_profiles'])
                    && !$model->getProfilesReadonly()
                ) {
                    $profiles = json_decode($data['category_profiles'], true);
                    $data['posted_profiles'] = $profiles;
                }
                $model->setData($data);
                $model->save();

                $this->messageManager->addSuccessMessage(__('You saved the category.'));
                $this->dataPersistor->clear('current_category');

                if ($redirectBack === 'save_and_new') {
                    return $resultRedirect->setPath('*/*/new');
                }

                if ($redirectBack === 'save_and_close') {
                    return $resultRedirect->setPath('*/*/*');
                }

                if (!$this->getRequest()->getPost('return_session_messages_only')) {
                    return $resultRedirect->setPath('*/*/edit', ['category_id' => $model->getId(), '_current' => true]);
                }
            } catch (LocalizedException $e) {
                $hasError = true;
                $this->messageManager->addExceptionMessage($e->getPrevious() ?:$e);
            } catch (Exception $e) {
                $hasError = true;
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the category.'));
            }

            if ($this->getRequest()->getPost('return_session_messages_only')) {

                $model->load($model->getId());
                /** @var $block Messages */
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));

                $data = $model->toArray();
                $data['name']   = $model->getTitle();
                $data['id']     = $model->getId();
                $data['parent'] = (int)$model->getParentId();
                $data['level']  = 0;

                /** @var Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData(
                    [
                        'messages' => $block->getGroupedHtml(),
                        'error'    => $hasError,
                        'item'     => $data
                    ]
                );
            }

            $this->dataPersistor->set('current_category', $data);
            return $resultRedirect->setPath('*/*/edit', ['category_id' => $this->getRequest()->getParam('category_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}