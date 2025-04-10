<?php
namespace Magecomp\Whatsappultimate\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    // GENERAL Configuration
     const ENABLED = 'whatsappultimate/whatsappcountryflag/enable';
    const WHATSAPP_GENERAL_ENABLED = 'whatsappultimate/general/enable';
    const WHATSAPP_GENERALSECTION_BUTTONCLASS = 'whatsappultimate/generalsection/buttonclass';
    const WHATSAPP_COUNTRYFLAG_DEFAULTCOUNTRY = 'whatsappultimate/whatsappcountryflag/defaultcountry';
    const COUNTRY_CODE_PATH = 'general/country/default';
    const WHATSAPP_ADMIN_MOBILE = 'admintemplate/admingeneral/mobile';

    const WHATSAPP_COUNTRYFLAG_ENABLE = 'whatsappultimate/whatsappcountryflag/enable';
    const WHATSAPP_COUNTRYFLAG_DETECT_BY_IP = 'whatsappultimate/whatsappcountryflag/detect_by_ip';

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getStoreid()
    {
        return $this->_storeManager->getStore()->getId();
    }
    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    public function getStoreUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function isEnabled($storeId = Null)
    {
        return $this->scopeConfig->getValue(
            self::WHATSAPP_GENERAL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isCountryFlagEnabled($storeId = Null)
    {
        return $this->scopeConfig->getValue(
            self::ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getButtonclass()
    {
        return $this->scopeConfig->getValue(
            self::WHATSAPP_GENERALSECTION_BUTTONCLASS,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreid()
        );
    }

    /*public function getDefaultcontry()
    {
        if($this->getCountryFlagEnable() && $this->getCountryFlagDetectByIp()==0){
            return $this->scopeConfig->getValue(
                self::WHATSAPP_COUNTRYFLAG_DEFAULTCOUNTRY,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid()
            );
        }
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_STORE,$this->getStoreid()
        );

    }*/

    public function checkAdminNumber($storeId)
    {
        return $this->scopeConfig->getValue(
            self::WHATSAPP_ADMIN_MOBILE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAdminNumber($storeId)
    {
        if ($this->checkAdminNumber($storeId) != '' && $this->checkAdminNumber($storeId) != null) {
            return $this->checkAdminNumber($storeId);
        }
    }
}
