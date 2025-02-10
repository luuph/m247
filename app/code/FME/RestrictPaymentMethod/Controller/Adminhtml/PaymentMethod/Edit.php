<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Controller\Adminhtml\PaymentMethod;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use FME\RestrictPaymentMethod\Model\PaymentMethod;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\Session as BackendSession;

class Edit extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \FME\RestrictPaymentMethod\Model\PaymentMethodFactory
     */
    private $model;
    protected $resultPageFactory;
    protected $backendSession;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry,
     * @param \FME\RestrictPaymentMethod\Model\PaymentMethodFactory $typeFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PaymentMethod $model,
        PageFactory $resultPageFactory,
        BackendSession $backendSession
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->model = $model;
        $this->backendSession = $backendSession;
    }

    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('FME_RestrictPaymentMethod::paymentmethod')
                ->addBreadcrumb(__('New Payment Method'), __('New Payment Method'))
                ->addBreadcrumb(__('Manage Payment Method'), __('Manage Payment Method'));
        return $resultPage;
    }

    public function execute()
    {
        // 1. Get ID and create model
         $id = $this->getRequest()->getParam('id');
        // 2. Initial checking
        if ($id) {
            $model=$this->model->load($id);
            if (!$model->getRuleId()) {
                $this->messageManager->addError(__('This record no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        // 3. Set entered data if was error when we do save
        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $this->model->addData($data);
        }
          $this->model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        $this->coreRegistry->register('fme_paymentmethod', $this->model);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Payment Method') : __('New Payment Method'),
            $id ? __('Edit Payment Method') : __('New Payment Method')
        );

        $title = $this->model->getName();
        $resultPage->getConfig()->getTitle()->prepend(__('Restrict Payment Method'));
        $resultPage->getConfig()->getTitle()
                ->prepend($this->model->getPaymentMethodId() ? __('Edit ' . $title, $this->model->getName()) : __('Payment Method'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FME_RestrictPaymentMethod::paymentmethod');
    }
}
