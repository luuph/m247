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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\RewardPoint\Controller\Adminhtml\Rule;

use Bss\RewardPoint\Model\RuleFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class edit
 *
 * Bss\RewardPoint\Controller\Adminhtml\Rule
 */
class Edit extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Bss\RewardPoint\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * Edit constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param Session     $backendSession
     * @param Registry    $registry
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Registry $registry,
        \Bss\RewardPoint\Model\RuleFactory $ruleFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->backendSession    = $backendSession;
        $this->registry          = $registry;
        $this->ruleFactory       = $ruleFactory;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initAction()
    {
        // load layout, set active menu and breadcrumbs
        /*
            @var \Magento\Backend\Model\View\Result\Page $resultPage
        */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bss_RewardPoint::rule_grid')
            ->addBreadcrumb(__('Earning Rule'), __('Earning Rule'))
            ->addBreadcrumb(__('Manage Rules'), __('Manage Rules'));
        return $resultPage;
    }

    /**
     * Execute
     *
     * @return $this|\Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = $this->ruleFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This earning rule no longer exists.'));
                /*
                    \Magento\Backend\Model\View\Result\Redirect $resultRedirect
                */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $model->getConditions()->setFormName('rwp_rule_form');
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId($model->getConditions()->getFormName())
        );

        $this->registry->register('rewardpoint_rule', $model);

        /*
            @var \Magento\Backend\Model\View\Result\Page $resultPage
        */
        $resultPage = $this->initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Earning Rule') : __('New Earning Rule'),
            $id ? __('Edit Earning Rule') : __('New Earning Rule')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Earning Rule'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Earning Rule'));
        // set entered data if was error when we do save
        $data = $this->backendSession->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        return $resultPage;
    }

    /**
     * Is allow
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_RewardPoint::rule');
    }
}
