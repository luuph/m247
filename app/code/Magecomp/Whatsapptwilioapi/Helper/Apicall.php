<?php
namespace Magecomp\Whatsapptwilioapi\Helper;

class Apicall extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_TWILIOWHATSAPP_ACCOUNTSID = 'whatsappultimate/smsgatways/twiliosid';
    const XML_TWILIOWHATSAPP_AUTHTOKEN = 'whatsappultimate/smsgatways/twiliotoken';
    const XML_TWILIOWHATSAPP_MOBILENUMBER = 'whatsappultimate/smsgatways/twilionumber';


    public function __construct(\Magento\Framework\App\Helper\Context $context)
    {
        parent::__construct($context);
    }

    public function getTitle()
    {
        return __("Twilio API");
    }

    public function getAccountsid($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_TWILIOWHATSAPP_ACCOUNTSID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAuthtoken($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_TWILIOWHATSAPP_AUTHTOKEN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    public function getMobileNumber($storeId)
    {
        return '+'.$this->scopeConfig->getValue(
            self::XML_TWILIOWHATSAPP_MOBILENUMBER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function validateSmsConfig($storeId)
    {
        
        $twilioclassExist = class_exists('Twilio\Rest\Client');

        if (!$twilioclassExist) {
            $this->_logger->error(__("Run 'composer require twilio/sdk' from CLI to use Twilio."));
        }

        return $twilioclassExist && $this->getAccountsid($storeId) && $this->getAuthtoken($storeId) && $this->getMobileNumber($storeId);
    }

    //public function callApiUrl($mobilenumbers, $message, $storeId)
    public function callApiUrl($mobilenumbers, $message,$storeId,$json,$dlt)
    {
    	$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/magecomp-twillio.log');
	    $logger = new \Zend_Log();
	    $logger->addWriter($writer);
	    $logger->info('mobilenumbers : '.$mobilenumbers);
	    $logger->info('message : '.$message);

        try {
            $account_sid = $this->getAccountsid($storeId);
            $auth_token = $this->getAuthtoken($storeId);
            $contentSid = $dlt;
            $contentVariables = $json;

           // $logger->info('contentVariables : '.print_r($contentVariables,true));
           // $logger->info('account_sid : '.print_r($account_sid,true));
           // $logger->info('auth_token : '.print_r($auth_token,true));

            /*$data=explode("\r", $message);
            $finalMessage="";
            foreach ($data as $key => $value) {
                if ($key==0) {
                    $finalMessage.=$value;
                } else {
                    $finalMessage.="\n".$value;
                }
            }
            $message=$finalMessage;*/

            if (substr($mobilenumbers, 0, 1) !== '+') {
                $mobilenumbers = '+'.$mobilenumbers;
            }

            $client = new \Twilio\Rest\Client($account_sid, $auth_token);
            /*$returntwilio = $client->messages->create(
                "whatsapp:".$mobilenumbers,
                ['from' => "whatsapp:".$this->getMobileNumber($storeId),'body' => $message]
            );*/

            $returntwilio = $client->messages->create(
                "whatsapp:".$mobilenumbers,
                ['from' => "whatsapp:".$this->getMobileNumber($storeId),'contentSid' => $contentSid,'contentVariables' => $contentVariables]
            );
           
            $logger->info('return twilio :  '.$mobilenumbers.' - '.print_r($returntwilio->status,true));

            if ($returntwilio->status == 'undelivered') {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
