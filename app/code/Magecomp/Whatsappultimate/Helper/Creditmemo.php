<?php
namespace Magecomp\Whatsappultimate\Helper;

use Magento\Store\Model\ScopeInterface;

class Creditmemo extends \Magecomp\Whatsappultimate\Helper\Data
{

    //USER TEMPLATE
    const SMS_IS_CUSTOMER_CREDITMEMO_NOTIFICATION = 'usertemplate/usercreditmemo/enable';
    const SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_TEMPLATE = 'usertemplate/usercreditmemo/template';
    const SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_LANGCODE = 'usertemplate/usercreditmemo/lang_code';
    const SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_TMPID = 'usertemplate/usercreditmemo/tmp_id';
    const SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_PARAMS = 'usertemplate/usercreditmemo/parms';
    const CSID_SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_TEMPLATE = 'usertemplate/usercreditmemo/contentsid';

    //ADMIN TEMPLATE
    const SMS_IS_ADMIN_CREDITMEMO_NOTIFICATION = 'admintemplate/admincreditmemo/enable';
    const SMS_ADMIN_CREDITMEMO_NOTIFICATION_TEMPLATE = 'admintemplate/admincreditmemo/template';
    const SMS_ADMIN_CREDITMEMO_NOTIFICATION_LANGCODE = 'admintemplate/admincreditmemo/lang_code';
    const SMS_ADMIN_CREDITMEMO_NOTIFICATION_TMPID = 'admintemplate/admincreditmemo/tmp_id';
    const SMS_ADMIN_CREDITMEMO_NOTIFICATION_PARAMS = 'admintemplate/admincreditmemo/parms';
    const CSID_SMS_ADMIN_CREDITMEMO_NOTIFICATION_TEMPLATE = 'admintemplate/admincreditmemo/contentsid';

    public function isCreditmemoNotificationForUser($storeId)
    {
        return  $this->scopeConfig->getValue(
            self::SMS_IS_CUSTOMER_CREDITMEMO_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCreditmemoNotificationUserTemplate($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getCreditmemoNotificationUserLangCode($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getCreditmemoNotificationUserTmpId($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getCreditmemoNotificationUserParams($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    public function isCreditmemoNotificationForAdmin($storeId)
    {
        return $this->scopeConfig->getValue(
            self::SMS_IS_ADMIN_CREDITMEMO_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCreditmemoNotificationForAdminTemplate($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_CREDITMEMO_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getCreditmemoNotificationForAdminLangCode($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_CREDITMEMO_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getCreditmemoNotificationForAdminTmpId($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_CREDITMEMO_NOTIFICATION_TMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getCreditmemoNotificationForAdminParams($storeId)
    {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_CREDITMEMO_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }
    public function getCreditmemoSID($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_CUSTOMER_CREDITMEMO_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getCreditmemoAdminSID($storeId = null)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::CSID_SMS_ADMIN_CREDITMEMO_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
}
