<?php
namespace Magecomp\Whatsappultimate\Helper;

use Magento\Store\Model\ScopeInterface;

class Invoice extends \Magecomp\Whatsappultimate\Helper\Data
{
    // USER TEMPLATE
    const WHATSAPP_IS_CUSTOMER_INVOICE_NOTIFICATION = 'usertemplate/userinvoice/enable';
    const WHATSAPP_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE = 'usertemplate/userinvoice/template';
    const WHATSAPP_CUSTOMER_INVOICE_NOTIFICATION_LANGCODE = 'usertemplate/userinvoice/lang_code';
    const WHATSAPP_CUSTOMER_INVOICE_NOTIFICATION_TMPID = 'usertemplate/userinvoice/tmp_id';
    const WHATSAPP_CUSTOMER_INVOICE_NOTIFICATION_PARAMS = 'usertemplate/userinvoice/parms';
    const CSID_SMS_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE = 'usertemplate/userinvoice/contentsid';

    //ADMIN TEMPLATE
    const WHATSAPP_IS_ADMIN_INVOICE_NOTIFICATION = 'admintemplate/admininvoice/enable';
    const WHATSAPP_ADMIN_INVOICE_NOTIFICATION_TEMPLATE = 'admintemplate/admininvoice/template';
    const WHATSAPP_ADMIN_INVOICE_NOTIFICATION_LANGCODE = 'admintemplate/admininvoice/lang_code';
    const WHATSAPP_ADMIN_INVOICE_NOTIFICATION_TMPID = 'admintemplate/admininvoice/tmp_id';
    const WHATSAPP_ADMIN_INVOICE_NOTIFICATION_PARAMS = 'admintemplate/admininvoice/parms';
    const CSID_SMS_ADMIN_INVOICE_NOTIFICATION_TEMPLATE = 'admintemplate/admininvoice/contentsid';

    public function isInvoiceNotificationForUser($storeId)
    {
        return $this->scopeConfig->getValue(
            self::WHATSAPP_IS_CUSTOMER_INVOICE_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getInvoiceNotificationUserTemplate($storeId)
    {
        return  $this->scopeConfig->getValue(
            self::WHATSAPP_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getInvoiceNotificationUserLangCode($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_INVOICE_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function getInvoiceNotificationUserTmpId($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_INVOICE_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function getInvoiceNotificationUserParams($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_CUSTOMER_INVOICE_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isInvoiceNotificationForAdmin($storeId)
    {
        return  $this->scopeConfig->getValue(
            self::WHATSAPP_IS_ADMIN_INVOICE_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getInvoiceNotificationForAdminTemplate($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_INVOICE_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getInvoiceNotificationForAdminLangCode($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_INVOICE_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getInvoiceNotificationForAdminTmpId($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::WHATSAPP_ADMIN_INVOICE_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getInvoiceNotificationForAdminParams($storeId)
    {
        return  $this->scopeConfig->getValue(
            self::WHATSAPP_ADMIN_INVOICE_NOTIFICATION_PARAMS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    public function getInvoiceNotificationUserSID($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_CUSTOMER_INVOICE_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getInvoiceNotificationForAdminSID($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_ADMIN_INVOICE_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
}
