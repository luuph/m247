<?php
namespace Unveels\RedirectLogin\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Controller\ResultInterface;

class RedirectGuestToLoginPlugin
{
    protected $customerSession;
    protected $resultRedirectFactory;
    protected $messageManager;
    protected $url;

    public function __construct(
        Session $customerSession,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        UrlInterface $url
    ) {
        $this->customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->url = $url;
    }

    public function afterExecute(\Magento\Checkout\Controller\Index\Index $subject, ResultInterface $result)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/RedirectGuestToLoginPlugin.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('RedirectGuestToLoginPlugin Executes');

        if (!$this->customerSession->isLoggedIn()) {
            $logger->info('Guest user detected, redirecting to login page.');
            $this->messageManager->getMessages(true);
            $this->messageManager->addNoticeMessage(__('You need to signup or login first.'));

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->url->getUrl('customer/account/login'));

            return $resultRedirect;
        }

        return $result;
    }
}
