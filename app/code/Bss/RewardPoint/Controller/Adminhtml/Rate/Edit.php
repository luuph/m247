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

namespace Bss\RewardPoint\Controller\Adminhtml\Rate;

/**
 * Class edit
 *
 * Bss\RewardPoint\Controller\Adminhtml\Rate
 */
class Edit extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Bss\RewardPoint\Model\RateFactory
     */
    protected $rateFactory;

    /**
     * Edit constructor.
     *
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     * @param \Bss\RewardPoint\Model\RateFactory         $rateFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Bss\RewardPoint\Model\RateFactory $rateFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry          = $registry;
        $this->rateFactory       = $rateFactory;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /*
         * Load layout, set active menu and breadcrumbs
         *
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bss_RewardPoint::rate_grid')
            ->addBreadcrumb(__('Exchange rates'), __('Exchange rates'))
            ->addBreadcrumb(__('Manage Exchange rates'), __('Manage Exchange rates'));
        return $resultPage;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id    = $this->getRequest()->getParam('rate_id');
        $model = $this->rateFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This exchange rate no longer exists.'));
                /*
                    \Magento\Backend\Model\View\Result\Redirect $resultRedirect
                */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->registry->register('rate_point', $model);

        /*
            @var \Magento\Backend\Model\View\Result\Page $resultPage
        */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Exchange Rates') : __('New Exchange Rates'),
            $id ? __('Edit Exchange Rates') : __('New Exchange Rates')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Exchange Rates'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Exchange Rates'));
        return $resultPage;
    }

    /**
     * Is allow
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_RewardPoint::rate');
    }
}
