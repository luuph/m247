<?php
namespace Magecomp\Whatsappultimate\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Register extends \Magento\Framework\App\Action\Action
{
    protected $helperapi;
    protected $helpercustomer;
    protected $smsmodel;
    protected $emailfilter;
    protected $filter;
    protected $_storeManager;
    public function __construct(
        Context $context,
        \Magecomp\Whatsappultimate\Helper\Apicall $helperapi,
        \Magecomp\Whatsappultimate\Helper\Customer $helpercustomer,
        \Magecomp\Whatsappultimate\Model\WhatsappultimateFactory $smsmodel,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helperapi = $helperapi;
        $this->helpercustomer = $helpercustomer;
        $this->smsmodel = $smsmodel;
        $this->emailfilter = $filter;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        try {

            $mobilenumber = $this->getRequest()->getParam('mobile');
            $storeId = $this->_storeManager->getStore()->getId();
            $otp = $this->helpercustomer->getOtp($storeId);
            $this->emailfilter->setVariables(['otp' => $otp]);
            $message = $this->helpercustomer->getSignUpConfirmationUserTemplate($storeId);
            $finalmessage = $this->emailfilter->filter($message);
            $langcode = $this->helpercustomer->getSignUpConfirmationUserLangcode($storeId);
            $tempid = $this->helpercustomer->getSignUpConfirmationUserTempId($storeId);
            $params = $this->helpercustomer->getSignUpConfirmationUserParams($storeId);
            $finalparams='';
            if ($params) {
                $finalparams=$this->emailfilter->filter($params);
            }
            
            $csid = $this->helpercustomer->getSignUpConfirmationUserTemplateSID($storeId);
            $json = json_encode([
                'name' => $otp
            ]);
            
            //$responce = $this->helperapi->callApiUrl($mobilenumber, $finalmessage, $storeId, $langcode, $tempid, $finalparams);
             $responce =  $this->helperapi->callApiUrl($mobilenumber, $finalmessage,$storeId=null,$json,$csid);
            
            if ($responce === true) {
                $smsModel = $this->smsmodel->create();
                $smscollection = $smsModel->getCollection();
                $smscollection->addFieldToFilter('mobile_number', $mobilenumber);
                if (count($smscollection)>0) {
                    $smsModel = $this->smsmodel->create()->load($mobilenumber, 'mobile_number');
                }
                $smsModel->setMobileNumber($mobilenumber)->setOtp($otp)->setIsverify(0)->save();

                $data = ["success"];
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($data);
                return $resultJson;
            } else {
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($responce);
                return $resultJson;
            }
        } catch (\Exception $e) {
            $data = ["error"];
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($data);
            return $resultJson;
        }
    }
}
