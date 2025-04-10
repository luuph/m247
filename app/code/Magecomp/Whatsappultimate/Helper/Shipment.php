<?php
namespace Magecomp\Whatsappultimate\Helper;

use Magento\Store\Model\ScopeInterface;

class Shipment extends \Magecomp\Whatsappultimate\Helper\Data
{
    // USER TEMPLATE
    const WHATSAPP_IS_CUSTOMER_SHIPMENT_NOTIFICATION = 'usertemplate/usershipment/enable';
    const WHATSAPP_CUSTOMER_SHIPMENT_NOTIFICATION_TEMPLATE = 'usertemplate/usershipment/template';
    const WHATSAPP_CUSTOMER_SHIPMENT_NOTIFICATION_LANGCODE = 'usertemplate/usershipment/lang_code';
    const WHATSAPP_CUSTOMER_SHIPMENT_NOTIFICATION_TMPID = 'usertemplate/usershipment/tmp_id';
    const WHATSAPP_CUSTOMER_SHIPMENT_NOTIFICATION_PARAMS = 'usertemplate/usershipment/parms';
    const CSID_SMS_CUSTOMER_SHIPMENT_NOTIFICATION_TEMPLATE = 'usertemplate/usershipment/contentsid';

    //ADMIN TEMPLATE
    const WHATSAPP_IS_ADMIN_SHIPMENT_NOTIFICATION = 'admintemplate/adminshipment/enable';
    const WHATSAPP_ADMIN_SHIPMENT_NOTIFICATION_TEMPLATE = 'admintemplate/adminshipment/template';
    const WHATSAPP_ADMIN_SHIPMENT_NOTIFICATION_LANGCODE = 'admintemplate/adminshipment/lang_code';
    const WHATSAPP_ADMIN_SHIPMENT_NOTIFICATION_TMPID = 'admintemplate/adminshipment/tmp_id';
    const WHATSAPP_ADMIN_SHIPMENT_NOTIFICATION_PARAMS = 'admintemplate/adminshipment/parms';
    const CSID_SMS_ADMIN_SHIPMENT_NOTIFICATION_TEMPLATE = 'admintemplate/adminshipment/contentsid';


    public function isShipmentNotificationForUser($storeId)
    {
        return  $this->scopeConfig->getValue(
            self::WHATSAPP_IS_CUSTOMER_SHIPMENT_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getShipmentNotificationUserTemplate($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_SHIPMENT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getShipmentNotificationUserLangCode($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_SHIPMENT_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getShipmentNotificationUserTmpId($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_SHIPMENT_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getShipmentNotificationUserParams($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_SHIPMENT_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isShipmentNotificationForAdmin($storeId)
    {
        return $this->scopeConfig->getValue(
            self::WHATSAPP_IS_ADMIN_SHIPMENT_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getShipmentNotificationForAdminTemplate($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_SHIPMENT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getShipmentNotificationForAdminLangCode($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_SHIPMENT_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getShipmentNotificationForAdminTmpId($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_SHIPMENT_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getShipmentNotificationForAdminParams($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_SHIPMENT_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getShipmentNotificationUserSID($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_CUSTOMER_SHIPMENT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getShipmentNotificationForAdminSID($storeId = null)
    {
       
        if ($this->isEnabled($storeId)) {
            return $this->scopeConfig->getValue(
                self::CSID_SMS_ADMIN_SHIPMENT_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
}
