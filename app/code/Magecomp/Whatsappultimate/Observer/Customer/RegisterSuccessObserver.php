<?php
namespace Magecomp\Whatsappultimate\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;

class RegisterSuccessObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helpercustomer;
    protected $smsmodel;
    protected $emailfilter;
    protected $_storeManager;
    protected $_customerModel;

    public function __construct(
        \Magecomp\Whatsappultimate\Helper\Apicall $helperapi,
        \Magecomp\Whatsappultimate\Helper\Customer $helpercustomer,
        \Magecomp\Whatsappultimate\Model\WhatsappultimateFactory $smsmodel,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customerModel
    ) {
        $this->helperapi = $helperapi;
        $this->helpercustomer = $helpercustomer;
        $this->smsmodel = $smsmodel;
        $this->emailfilter = $filter;
        $this->_customerModel = $customerModel;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helpercustomer->isEnabled()) {
            return $this;
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $customer = $observer->getEvent()->getCustomer();
        $controller = $observer->getAccountController();
        $mobilenumber = $controller->getRequest()->getParam('mobilenumber');

        $tempcustomer =  $this->_customerModel->load($customer->getId());

        $this->emailfilter->setVariables([
            'customer' => $tempcustomer,
            'mobilenumber' => $mobilenumber
        ]);
        
        $json = json_encode([
            'firstname' => $tempcustomer->getData('firstname'),
            'lastname' => $tempcustomer->getData('lastname'),
            'email' => $tempcustomer->getData('email'),
            'created_in' => (string)$tempcustomer->getData('created_at'),
            'created_at' => (string)$tempcustomer->getData('created_at'),
            'mobilenumber' => $mobilenumber
        ]);

    
        if ($this->helpercustomer->isSignUpNotificationForAdmin() && $this->helpercustomer->getAdminNumber($storeId)) {
            $message = $this->helpercustomer->getSignUpNotificationForAdminTemplate();
            $langcode = $this->helpercustomer->getSignUpNotificationForAdminLangCode();
            $tempid = $this->helpercustomer->getSignUpNotificationForAdminTmpId();
            $params = $this->helpercustomer->getSignUpNotificationForAdminParams();
            $finalmessage = $this->emailfilter->filter($message);
            $finalparams='';
            if ($params) {
                $finalparams=$this->emailfilter->filter($params);
            }
            $csid = $this->helpercustomer->getSignUpNotificationForAdminSID($storeId=null);

            $this->helperapi->callApiUrl($this->helpercustomer->getAdminNumber($storeId), $finalmessage,$storeId=null,$json,$csid);

           // $this->helperapi->callApiUrl($this->helpercustomer->getAdminNumber($storeId), $finalmessage, $storeId, $langcode, $tempid, $finalparams);
        }

        if ($mobilenumber == '' || $mobilenumber == null) {
            return $this;
        }

        $smsModel = $this->smsmodel->create();
        $smscollection = $smsModel->getCollection()
                       ->addFieldToFilter('mobile_number', $mobilenumber);
        foreach ($smscollection as $sms) {
            $sms->delete();
        }

        if ($this->helpercustomer->isSignUpNotificationForUser($storeId=null)) {
            $message = $this->helpercustomer->getSignUpNotificationForUserTemplate($storeId=null);
            $langcode = $this->helpercustomer->getSignUpNotificationForUserLangCode();
            $tempid = $this->helpercustomer->getSignUpNotificationForUserTmpId();
            $params = $this->helpercustomer->getSignUpNotificationForUserParams();
            $finalmessage = $this->emailfilter->filter($message);
            $finalparams='';
            if ($params) {
                $finalparams=$this->emailfilter->filter($params);
            }
           // $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $storeId, $langcode, $tempid, $finalparams);
            $csid = $this->helpercustomer->getSignUpNotificationForUserSID($storeId=null);
            $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId=null,$json,$csid);
        }
        return $this;
    }
}
