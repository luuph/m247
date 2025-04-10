<?php
namespace Magecomp\Whatsappultimate\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class CheckoutLoadObserver implements ObserverInterface
{
    protected $messageManager;
    protected $_urlManager;
    protected $urlModel;
    protected $helpercustomer;
    protected $_responseFactory;
    protected $customersession;
    protected $checkoutSession;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magecomp\Whatsappultimate\Helper\Customer $helpercustomer,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Customer\Model\Session $customersession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->urlModel = $urlFactory->create();
        $this->messageManager = $messageManager;
        $this->_urlManager = $urlManager;
        $this->helpercustomer = $helpercustomer;
        $this->_responseFactory = $responseFactory;
        $this->customersession = $customersession;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if (!$this->helpercustomer->isOrderConfirmationForUser()) {
                return $this;
            }

            if($this->customersession->isLoggedIn()) {
                $mobile = $this->customersession->getCustomer()->getMobilenumber();
                if ($mobile == '' || $mobile == null) {
                    $defaultUrl = $this->urlModel->getUrl('whatsappultimate/customer/update', ['_secure' => true]);
                    $controller = $observer->getControllerAction();
                    $controller->getResponse()->setRedirect($defaultUrl);
                }
                return $this;
            } else {
                $guestorderconfirm = $this->checkoutSession->getGuestOrderConfirm();
                if ($guestorderconfirm) {
                    return $this;
                } else {
                    $defaultUrl = $this->urlModel->getUrl('whatsappultimate/customer/checkout', ['_secure' => true]);
                    $controller = $observer->getControllerAction();
                    $controller->getResponse()->setRedirect($defaultUrl);
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
