<?php
namespace Magecomp\Whatsappultimate\Plugin;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magecomp\Whatsappultimate\Helper\Customer;
use Magecomp\Whatsappultimate\Model\WhatsappultimateFactory;

class PluginConfirmRegistration
{
    protected $urlModel;
    protected $resultRedirectFactory;
    protected $messageManager;
    protected $helpercustomer;
    protected $smsmodel;
    protected $_customerFactory;

    public function __construct(
        UrlFactory       $urlFactory,
        RedirectFactory  $redirectFactory,
        ManagerInterface $messageManager,
        Customer         $helpercustomer,
        WhatsappultimateFactory    $smsmodel,
        CustomerFactory  $customerFactory
    ) {
        $this->urlModel = $urlFactory->create();
        $this->resultRedirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->helpercustomer = $helpercustomer;
        $this->smsmodel = $smsmodel;
        $this->_customerFactory = $customerFactory;
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\CreatePost $subject, \Closure $proceed)
    {
        if ($this->helpercustomer->isSignUpConfirmationForUser()) {
            try {
                $postdata = $subject->getRequest()->getPost();
                
                $finalnumber = $postdata['mobilenumber'];
                $smsModel = $this->smsmodel->create();
                $smscollection = $smsModel->getCollection()
                    ->addFieldToFilter('mobile_number', $finalnumber)
                    ->addFieldToFilter('otp', $postdata['otp']);

                $customer = $this->_customerFactory->create()->getCollection()
                    ->addAttributeToFilter("mobilenumber", ["eq" => $finalnumber])
                    ->addAttributeToFilter("website_id", $this->helpercustomer->getWebsiteId())
                    ->load();
                if (count($customer) > 0) {
                    $this->messageManager->addError(__('Customer already exists with this ('.$finalnumber.') whatsapp number'));
                    $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setUrl($defaultUrl);

                }
                if (count($smscollection) == 0) {
                    $this->messageManager->addError(__('Invalid OTP'));
                    $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setUrl($defaultUrl);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $proceed();
    }
}
