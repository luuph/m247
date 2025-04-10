<?php
namespace Magecomp\Whatsappultimate\Controller\Customer;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Updateotpnumber extends \Magento\Framework\App\Action\Action
{
    protected $smsmodel;
    protected $customersession;
    protected $customermodel;
    protected $customerresourcefactory;

    public function __construct(
        Context $context,
        \Magecomp\Whatsappultimate\Model\WhatsappultimateFactory $smsmodel,
        \Magento\Customer\Model\Session $customersession,
        \Magento\Customer\Model\Customer $customermodel,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerresourcefactory
    ) {
        $this->smsmodel = $smsmodel;
        $this->customersession = $customersession;
        $this->customermodel = $customermodel;
        $this->customerresourcefactory = $customerresourcefactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $mobilenumber = $this->getRequest()->getParam('mobile');
            $otp = $this->getRequest()->getParam('otp');
            $wpterms = $this->getRequest()->getParam('wpterms');
            if (!$wpterms) {
                $data = [__("Please select checkbox to Receive WhatsApp Notification first. ")];
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($data);
                return $resultJson;
            }

            if ($mobilenumber == '' || $mobilenumber == null || $otp == '' || $otp == null) {
                $data = [__("WhatsApp Number & OTP Required For Verification.")];
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($data);
                return $resultJson;
            }

            $smsModel = $this->smsmodel->create();
            $smscollection = $smsModel->getCollection();
            $smscollection->addFieldToFilter('mobile_number', $mobilenumber)
                          ->addFieldToFilter('otp', $otp);

            foreach ($smscollection as $smsdata) {
                $customerId = $this->customersession->getCustomer()->getId();

                $customer = $this->customermodel->load($customerId);
                $customerData = $customer->getDataModel();
                $customerData->setCustomAttribute('mobilenumber', $mobilenumber);
                $customer->updateData($customerData);
                $customerResource = $this->customerresourcefactory->create();
                $customerResource->saveAttribute($customer, 'mobilenumber');
                $customer->save();

                $smsdata->delete();

                $data = ["success"];
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($data);
                return $resultJson;
            }

            $data = [__("Invalid OTP.")];
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($data);
            return $resultJson;
        } catch (\Exception $e) {
            $data = ["error"];
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($data);
            return $resultJson;
        }
    }
}
