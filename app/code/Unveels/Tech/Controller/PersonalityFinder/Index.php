<?php

namespace Unveels\Tech\Controller\PersonalityFinder;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }
    
    public function execute()
    {
        $resultPageLayout = $this->_pageFactory->create();
        $resultPageLayout->getLayout()->getUpdate()->removeHandle('default');
        return $resultPageLayout;
    }
}
