<?php
namespace Magecomp\Whatsappultimate\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Wpterms extends \Magento\Framework\App\Action\Action
{
    protected $customersession;
    protected $customermodel;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customersession,
        \Magento\Customer\Model\CustomerFactory $customermodel
    ) {
        $this->customersession = $customersession;
        $this->customermodel = $customermodel;
        parent::__construct($context);
    }

    public function execute()
    {

        try {
            $wpterms = $this->getRequest()->getParam('wpterms');
            $customerId = $this->customersession->getCustomer()->getId();
            $customer = $this->customermodel->create()->load($customerId);
            if ($wpterms==1) {

                $customer->setData('wpterms', '1')->save();
            } elseif ($wpterms==0) {
                $customer->setData('wpterms', '0')->save();
            }
            $data = ["success"];
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
