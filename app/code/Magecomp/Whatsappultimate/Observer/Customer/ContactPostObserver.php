<?php
namespace Magecomp\Whatsappultimate\Observer\Customer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class ContactPostObserver implements ObserverInterface
{
    protected $helperapi;
    protected $helpercontact;
    protected $emailfilter;
    protected $_storeManager;
    protected $customerRepository;
    protected $customerFactory;
    protected $customersession;

    public function __construct(
        \Magecomp\Whatsappultimate\Helper\Apicall $helperapi,
        \Magecomp\Whatsappultimate\Helper\Contact $helpercontact,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\Session $customersession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helperapi = $helperapi;
        $this->helpercontact = $helpercontact;
        $this->customerFactory = $customerFactory;
        $this->emailfilter = $filter;
        $this->customerRepository = $customerRepository;
        $this->customersession = $customersession;
        $this->_storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helpercontact->isEnabled()) {
            return $this;
        }

        $storeId = $this->_storeManager->getStore()->getId();
        $request = $observer->getRequest();
        $name = $request->getParam('name');
        $email = $request->getParam('email');
        $telephone = $request->getParam('telephone');
        $comment = $request->getParam('comment');
        $this->emailfilter->setVariables([
            'name' => $name,
            'email' => $email,
            'telephone' => $telephone,
            'comment' => $comment,
            'store_name' => $this->helpercontact->getStoreName()
        ]);

        $json = json_encode([
            'name' => $name,
            'email' => $email,
            'telephone' => $telephone,
            'comment' => $comment,
            'store_name' => $this->helpercontact->getStoreName(),
            'mobilenumber' => $telephone
        ]);


        if ($this->helpercontact->isContactNotificationForUser($storeId)) {
            $message = $this->helpercontact->getContactNotificationUserTemplate($storeId);
            $langcode = $this->helpercontact->getContactNotificationUserLangCode($storeId);
            $tempid = $this->helpercontact->getContactNotificationUserTmpId($storeId);
            $params = $this->helpercontact->getContactNotificationUserParams($storeId);


           
            $finalmessage = $this->emailfilter->filter($message);
            $finalparams='';
            if ($params) {
                $finalparams=$this->emailfilter->filter($params);
            }
            $customerModel = $this->customerFactory->create();
            $customercollection = $customerModel->getCollection();
            $customercollection->addFieldToFilter('email', $email);
            /*if ($customercollection->count()>0) {
                $customerData = $this->customerRepository->get($email);
                $customerId = (int)$customerData->getId();
                $customer = $this->customerFactory->create()->load($customerId);
                $wpterms = $customer->getWpterms();
                if($customer->getData()){
                if ($wpterms) {
                        $this->helperapi->callApiUrl($telephone, $finalmessage, $storeId, $langcode, $tempid, $finalparams);
                    }
                }
            }
            else{
                $this->helperapi->callApiUrl($telephone, $finalmessage, $storeId, $langcode, $tempid, $finalparams);
            }*/
            $csid = $this->helpercontact->getContactSidTemplate($storeId=null);
            $this->helperapi->callApiUrl($telephone, $finalmessage,$storeId=null,$json,$csid);
        }

        if ($this->helpercontact->isContactNotificationForAdmin($storeId) && $this->helpercontact->getAdminNumber($storeId)) {
            $message = $this->helpercontact->getContactNotificationForAdminTemplate($storeId);
            $langcode = $this->helpercontact->getContactNotificationForAdminLangCode($storeId);
            $tempid = $this->helpercontact->getContactNotificationForAdminTmpId($storeId);
            $params = $this->helpercontact->getContactNotificationForAdminParams($storeId);
            $finalmessage = $this->emailfilter->filter($message);
            $finalparams='';
            if ($params) {
                $finalparams=$this->emailfilter->filter($params);
            }
            //$this->helperapi->callApiUrl($this->helpercontact->getAdminNumber($storeId), $finalmessage, $storeId, $langcode, $tempid, $finalparams);
            $csid = $this->helpercontact->getContactSidAdmin($storeId=null);
            $this->helperapi->callApiUrl($this->helpercontact->getAdminNumber($storeId), $finalmessage,$storeId=null,$json,$csid);
        }

        return $this;
    }
}
