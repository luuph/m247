<?php
namespace Magecomp\Whatsappultimate\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Updateotp extends \Magento\Framework\App\Action\Action
{
    protected $helperapi;
    protected $helpercustomer;
    protected $smsmodel;
    protected $customersession;
    protected $emailfilter;
    protected $_storeManager;

    public function __construct(
        Context $context,
        \Magecomp\Whatsappultimate\Helper\Apicall $helperapi,
        \Magecomp\Whatsappultimate\Helper\Customer $helpercustomer,
        \Magecomp\Whatsappultimate\Model\WhatsappultimateFactory $smsmodel,
        \Magento\Customer\Model\Session $customersession,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helperapi = $helperapi;
        $this->helpercustomer = $helpercustomer;
        $this->smsmodel = $smsmodel;
        $this->customersession = $customersession;
        $this->emailfilter = $filter;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $mobilenumber = $this->getRequest()->getParam('mobile');
            $wpterms = $this->getRequest()->getParam('wpterms');
            $storeId = $this->_storeManager->getStore()->getId();
            if ($mobilenumber == $this->customersession->getCustomer()->getMobilenumber()) {
                $data = [__("Your WhatsApp Number is Already Verified.")];
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($data);
                return $resultJson;
            }

            $otp = $this->helpercustomer->getOtp();
            
            $json = json_encode([
                'name' => $otp
            ]);

            $this->emailfilter->setVariables(['otp' => $otp]);
            $message = $this->helpercustomer->getMobileConfirmationUserTemplate();
            $langcode = $this->helpercustomer->getMobileConfirmationUserLangCode();
            $tempid = $this->helpercustomer->getMobileConfirmationUserTmpId();
            $params = $this->helpercustomer->getMobileConfirmationUserParams();
            $finalmessage = $this->emailfilter->filter($message);
            $finalparams='';
            if ($params) {
                $finalparams = $this->emailfilter->filter($params);
            }
            
            $csid = $this->helpercustomer->getMyAccountConfirmationUserTemplateSID($storeId);
            
            $responce = false;
            if ($wpterms) {
                //$responce = $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $storeId, $langcode, $tempid, $finalparams);
                $responce =  $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId=null,$json,$csid);
            }

            if ($responce == "true") {
                $smsModel = $this->smsmodel->create();
                $smscollection = $smsModel->getCollection();
                $smscollection->addFieldToFilter('mobile_number', $mobilenumber);

                if (count($smscollection)>0) {
                    $smsModel = $this->smsmodel->create()->load($mobilenumber, 'mobile_number');
                }
                $smsModel->setMobileNumber($mobilenumber)
                        ->setOtp($otp)
                        ->setIsverify(0)
                        ->save();

                $data = ["success"];
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($data);
                return $resultJson;
            } else {
                $data = [__("Please select checkbox to Receive WhatsApp Notification first. ")];
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($data);
                return $resultJson;
            }
        } catch (\Exception $e) {
             $data = ["First Enter OTP"];
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($data);
            return $resultJson;
        }
    }
}
