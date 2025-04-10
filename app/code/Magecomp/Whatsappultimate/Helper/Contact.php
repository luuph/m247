<?php
namespace Magecomp\Whatsappultimate\Helper;

use Magento\Store\Model\ScopeInterface;

class Contact extends \Magecomp\Whatsappultimate\Helper\Data
{
    // USER TEMPLATE
    const WHATSAPP_IS_CUSTOMER_CONTACT_NOTIFICATION = 'usertemplate/usercontactus/enable';
    const WHATSAPP_CUSTOMER_CONTACT_NOTIFICATION_TEMPLATE = 'usertemplate/usercontactus/template';
    const WHATSAPP_CUSTOMER_CONTACT_NOTIFICATION_LANGCODE = 'usertemplate/usercontactus/lang_code';
    const WHATSAPP_CUSTOMER_CONTACT_NOTIFICATION_TMPID = 'usertemplate/usercontactus/tmp_id';
    const WHATSAPP_CUSTOMER_CONTACT_NOTIFICATION_PARAMS = 'usertemplate/usercontactus/parms';
    const CSID_SMS_CUSTOMER_CONTACT_NOTIFICATION_TEMPLATE = 'usertemplate/usercontactus/contentsid';

    //ADMIN TEMPLATE
    const WHATSAPP_IS_ADMIN_CONTACT_NOTIFICATION = 'admintemplate/admincontactus/enable';
    const WHATSAPP_ADMIN_CONTACT_NOTIFICATION_TEMPLATE = 'admintemplate/admincontactus/template';
     const WHATSAPP_ADMIN_CONTACT_NOTIFICATION_LANGCODE = 'admintemplate/admincontactus/lang_code';
    const WHATSAPP_ADMIN_CONTACT_NOTIFICATION_TMPID = 'admintemplate/admincontactus/tmp_id';
    const WHATSAPP_ADMIN_CONTACT_NOTIFICATION_PARAMS = 'admintemplate/admincontactus/parms';
    const CSID_SMS_ADMIN_CONTACT_NOTIFICATION_TEMPLATE = 'admintemplate/admincontactus/contentsid';


    public function isContactNotificationForUser($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->getValue(
            self::WHATSAPP_IS_CUSTOMER_CONTACT_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
           $storeId
        );
    }

    public function getContactNotificationUserTemplate($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_CONTACT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getContactNotificationUserLangCode($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_CONTACT_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getContactNotificationUserTmpId($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_CONTACT_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getContactNotificationUserParams($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_CONTACT_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    public function isContactNotificationForAdmin($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->getValue(
            self::WHATSAPP_IS_ADMIN_CONTACT_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
           $storeId
        );
    }

    public function getContactNotificationForAdminTemplate($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_CONTACT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getContactNotificationForAdminLangCode($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_CONTACT_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getContactNotificationForAdminTmpId($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_CONTACT_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getContactNotificationForAdminParams($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_CONTACT_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getContactSidTemplate($storeId)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_CUSTOMER_CONTACT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getContactSidAdmin($storeId)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->getValue(
            self::CSID_SMS_ADMIN_CONTACT_NOTIFICATION_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
