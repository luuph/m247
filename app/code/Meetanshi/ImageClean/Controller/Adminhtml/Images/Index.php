<?php

namespace Meetanshi\ImageClean\Controller\Adminhtml\Images;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Meetanshi\ImageClean\Helper\Data;
use Magento\Backend\App\Action;

class Index extends Action
{
    protected $resultPageFactory;
    protected $helper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $data
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $data;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Meetanshi_ImageClean::imageclean');
        $resultPage->getConfig()->getTitle()->prepend((__('Unused Product Images')));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Meetanshi_ImageClean::imageclean');
    }
}
