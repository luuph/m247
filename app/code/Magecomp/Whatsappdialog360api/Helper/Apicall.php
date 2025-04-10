<?php
namespace Magecomp\Whatsappdialog360api\Helper;

class Apicall extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_360DIALOG_API_KEY = 'whatsappultimate/smsgatways/360dialogapikey';
    const XML_360DIALOG_API_URL = 'whatsappultimate/smsgatways/dialogapiurl';
    const XML_360DIALOG_Name_SPACE = 'whatsappultimate/smsgatways/360dialogapinamespace';
    const XML_360DIALOG_CONTACT_URL = 'whatsappultimate/smsgatways/contactapiurl';


    public function __construct(\Magento\Framework\App\Helper\Context $context)
    {
        parent::__construct($context);
    }

    public function getTitle()
    {
        return __("360dialog API");
    }

    public function getApiKey()
    {
        return $this->scopeConfig->getValue(
            self::XML_360DIALOG_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getNamespace()
    {
        return $this->scopeConfig->getValue(
            self::XML_360DIALOG_Name_SPACE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getApiUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_360DIALOG_API_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getContactApiUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_360DIALOG_CONTACT_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function validateSmsConfig()
    {
        return $this->getApiKey() && $this->getNamespace() && $this->getApiUrl() && $this->getContactApiUrl();
    }

    public function callApiUrl($mobilenumbers, $message, $storeId, $langcode, $tempid, $params)
    {
       	$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/360message.log');
	$logger = new \Zend_Log();
	$logger->addWriter($writer);
	$logger->info('------------------------------');
	$logger->info('mobilenumbers : '.$mobilenumbers);
	$logger->info('message : '.$message);
	/*$logger->info('storeId : '.print_r($storeId,true));
	$logger->info('tempid : '.print_r($tempid,true));
	$logger->info('params : '.print_r($params,true));*/

        $url = $this->getApiUrl();
        $contacturl = $this->getContactApiUrl();
        try {
            /*$headers = [
            "Content-Type: application/json",
            "D360-Api-Key:".$this->getApiKey(),
            ];

            $curl = curl_init();
            $contact = ['blocking'=>"wait",'contacts'=>[$mobilenumbers],'force_check'=>true];
            $encodedcontact=json_encode($contact);

            curl_setopt_array($curl, [
                CURLOPT_URL => $contacturl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $encodedcontact,
                CURLOPT_HTTPHEADER => $headers,
                ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            $param = explode(",", $params);
            $paramvalue=[];
            foreach ($param as $key => $value) {
                $paramvalue[$key]=['type'=>"text",'text'=>$value];
            }
            $logger->info('paramvalue : '.print_r($paramvalue,true));
            
            $payload = ['to'=>$mobilenumbers,'type'=>"template",
                'template'=>['namespace'=>$this->getNamespace(),'language'=>['policy'=>"deterministic",'code'=> $langcode],
                    'name'=>$tempid,'components'=>[['type'=>"body",'parameters'=> $paramvalue]]]];
            $encodedpayload=json_encode($payload);

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $encodedpayload,
                CURLOPT_HTTPHEADER => $headers,
                ]);
            $response = curl_exec($curl);
            $logger->info('response : '.print_r($response,true));
            $err = curl_error($curl);*/
            $apiKey = $this->getApiKey();
            $headers = [
	    	"D360-API-KEY: ".$apiKey,
	    	"Content-Type: application/json"
	    ];
	    
	    $payload = [
		    "messaging_product"=> "whatsapp",
		    "recipient_type" => "individual",
		    "to" => $mobilenumbers, 
		    "type" => "text",
		    "text" => [
			"body" => $message
		    ] 
	    ];

	    $ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		$logger->info('response : '.print_r($response,true));
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$err = curl_error($ch);
		
            $logger->info('------------------------------');
            if ($err) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }
}
