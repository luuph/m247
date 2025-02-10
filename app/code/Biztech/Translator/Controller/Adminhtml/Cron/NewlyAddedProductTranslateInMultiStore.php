<?php

namespace Biztech\Translator\Controller\Adminhtml\Cron;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class NewlyAddedProductTranslateInMultiStore extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();
        $this->resultPage ->getConfig()->getTitle()->set((__('Translation of newly added products in multiple stores')));
        return $this->resultPage;
    }
     /**
      * Check admin permissions for this controller
      *
      * @return boolean
      */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Biztech_Translator::biztech_newlyaddedproducttranslateinallstore');
    }
}
