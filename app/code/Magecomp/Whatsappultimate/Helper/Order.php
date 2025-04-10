<?php
namespace Magecomp\Whatsappultimate\Helper;

use Magento\Store\Model\ScopeInterface;

class Order extends \Magecomp\Whatsappultimate\Helper\Data
{
    // USER TEMPLATE
    const WHATSAPP_IS_CUSTOMER_ORDER_NOTIFICATION = 'usertemplate/userorderplace/enable';
    const WHATSAPP_CUSTOMER_ORDER_NOTIFICATION_TEMPLATE = 'usertemplate/userorderplace/template';
    const WHATSAPP_CUSTOMER_ORDER_NOTIFICATION_LANGCODE = 'usertemplate/userorderplace/lang_code';
    const WHATSAPP_CUSTOMER_ORDER_NOTIFICATION_TMPID = 'usertemplate/userorderplace/tmp_id';
    const WHATSAPP_CUSTOMER_ORDER_NOTIFICATION_PARAMS = 'usertemplate/userorderplace/parms';
    const CSID_SMS_CUSTOMER_ORDER_NOTIFICATION_TEMPLATE = 'usertemplate/userorderplace/contentsid';

    //ADMIN TEMPLATE
    const WHATSAPP_IS_ADMIN_ORDER_NOTIFICATION = 'admintemplate/adminorderplace/enable';
    const WHATSAPP_ADMIN_ORDER_NOTIFICATION_TEMPLATE = 'admintemplate/adminorderplace/template';
    const WHATSAPP_ADMIN_ORDER_NOTIFICATION_LANGCODE = 'admintemplate/adminorderplace/lang_code';
    const WHATSAPP_ADMIN_ORDER_NOTIFICATION_TMPID = 'admintemplate/adminorderplace/tmp_id';
    const WHATSAPP_ADMIN_ORDER_NOTIFICATION_PARAMS = 'admintemplate/adminorderplace/parms';
    const CSID_SMS_ADMIN_ORDER_NOTIFICATION_TEMPLATE = 'admintemplate/adminorderplace/contentsid';

    public function isOrderNotificationForUser($storeId)
    {
        return $this->scopeConfig->getValue(
            self::WHATSAPP_IS_CUSTOMER_ORDER_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOrderNotificationUserTemplate($storeId = null)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_ORDER_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getOrderNotificationUserLangCode($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_ORDER_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getOrderNotificationUserTmpId($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_ORDER_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getOrderNotificationUserParams($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_ORDER_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isOrderNotificationForAdmin($storeId = null)
    {
        return  $this->scopeConfig->getValue(
            self::WHATSAPP_IS_ADMIN_ORDER_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOrderNotificationForAdminTemplate($storeId = null)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_ORDER_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getOrderNotificationForAdminLangCode($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_ORDER_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getOrderNotificationForAdminTmpId($storeId = null)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_ORDER_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getOrderNotificationForAdminParams($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_ORDER_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getOrderNotificationUserSID($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_CUSTOMER_ORDER_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getOrderNotificationForAdminSID($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_ADMIN_ORDER_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
}
