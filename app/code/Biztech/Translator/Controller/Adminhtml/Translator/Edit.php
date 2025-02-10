<?php

namespace Biztech\Translator\Controller\Adminhtml\Translator;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{

    protected $resultPageFactory;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Edit String Information'));
        $resultPage->getLayout()->getBlock('head');
        $resultPage->getLayout()->createBlock('\Biztech\Translator\Block\Adminhtml\Search\Edit');
        return $resultPage;
    }
}
