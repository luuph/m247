<?php
namespace Magecomp\Whatsappultimate\Helper;

use Magento\Store\Model\ScopeInterface;

class Customer extends \Magecomp\Whatsappultimate\Helper\Data
{
    //GENERAL
    const SMS_OTP_TYPE = 'whatsappultimate/generalsection/otptype';
    const SMS_OTP_LENGTH = 'whatsappultimate/generalsection/otplength';

    // USER TEMPLATE
    const SMS_IS_CUSTOMER_SIGNUP_CONFIRMATIOM = 'usertemplate/usersignupconfirm/enable';
    const SMS_CUSTOMER_SIGNUP_CONFIRMATIOM_TEMPLATE = 'usertemplate/usersignupconfirm/template';
    const SMS_CUSTOMER_SIGNUP_CONFIRMATIOM_LANGCODE = 'usertemplate/usersignupconfirm/lang_code';
    const SMS_CUSTOMER_SIGNUP_CONFIRMATIOM_TEMPID = 'usertemplate/usersignupconfirm/tmp_id';
    const SMS_CUSTOMER_SIGNUP_CONFIRMATIOM_PARAMS = 'usertemplate/usersignupconfirm/parms';
    const SMS_IS_CUSTOMER_SIGNUP_NOTIFICATION = 'usertemplate/usersignup/enable';
    const SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPLATE = 'usertemplate/usersignup/template';
    const SMS_CUSTOMER_SIGNUP_NOTIFICATION_LANGCODE = 'usertemplate/usersignup/lang_code';
    const SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPID = 'usertemplate/usersignup/tmp_id';
    const SMS_CUSTOMER_SIGNUP_NOTIFICATION_PARAMS = 'usertemplate/usersignup/parms';
    const SMS_IS_CUSTOMER_MOBILE_CONFIRMATIOM = 'usertemplate/usermobileconfirm/enable';
    const SMS_CUSTOMER_MOBILE_CONFIRMATIOM_TEMPLATE = 'usertemplate/usermobileconfirm/template';
    const SMS_CUSTOMER_MOBILE_CONFIRMATIOM_LANGCODE = 'usertemplate/usermobileconfirm/lang_code';
    const SMS_CUSTOMER_MOBILE_CONFIRMATIOM_TEMPID = 'usertemplate/usermobileconfirm/tmp_id';
    const SMS_CUSTOMER_MOBILE_CONFIRMATIOM_PARAMS = 'usertemplate/usermobileconfirm/parms';
    const SMS_IS_CUSTOMER_ORDER_CONFIRMATIOM = 'usertemplate/userorderconfirm/enable';
    const SMS_CUSTOMER_ORDER_CONFIRMATIOM_TEMPLATE = 'usertemplate/userorderconfirm/template';
    const SMS_CUSTOMER_ORDER_CONFIRMATIOM_LANGCODE = 'usertemplate/userorderconfirm/lang_code';
    const SMS_CUSTOMER_ORDER_CONFIRMATIOM_TEMPID = 'usertemplate/userorderconfirm/tmp_id';
    const SMS_CUSTOMER_ORDER_CONFIRMATIOM_PARAMS = 'usertemplate/userorderconfirm/parms';

    
    const SID_SMS_CUSTOMER_SIGNUP_CONFIRM_NOTIFICATION_TEMPLATE = 'usertemplate/usersignupconfirm/contentsid';
    const SID_SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPLATE = 'usertemplate/usersignup/contentsid';

    const MY_ACCOUNTSID_SMS_CUSTOMER_SIGNUP_CONFIRM_NOTIFICATION_TEMPLATE = 'usertemplate/usermobileconfirm/contentsid';

    //ADMIN TEMPLATE
    const SMS_IS_ADMIN_SIGNUP_NOTIFICATION = 'admintemplate/adminsignup/enable';
    const SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPLATE = 'admintemplate/adminsignup/template';
    const SMS_ADMIN_SIGNUP_NOTIFICATION_LANGCODE = 'admintemplate/adminsignup/lang_code';
    const SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPID = 'admintemplate/adminsignup/tmp_id';
    const SMS_ADMIN_SIGNUP_NOTIFICATION_PARAMS = 'admintemplate/adminsignup/parms';

    const SID_SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPLATE = 'admintemplate/adminsignup/contentsid';

    public function getOtpType($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::SMS_OTP_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOtpLength($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::SMS_OTP_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOtp($storeId = null)
    {
        if ($this->getOtpType($storeId)) {
            return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $this->getOtpLength($storeId));
        } else {
            return $randomString  = substr(str_shuffle("0123456789"), 0, $this->getOtpLength($storeId));
        }
    }

    public function isSignUpConfirmationForUser($storeid = Null)
    {
        return $this->isEnabled($storeid) && $this->scopeConfig->getValue(
            self::SMS_IS_CUSTOMER_SIGNUP_CONFIRMATIOM,
            ScopeInterface::SCOPE_STORE,
            $storeid
        );
    }

    public function getSignUpConfirmationUserTemplate($storeId = Null)
    {
        if($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SIGNUP_CONFIRMATIOM_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpConfirmationUserTemplateSID($storeId = Null)
    {
        if($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SID_SMS_CUSTOMER_SIGNUP_CONFIRM_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getMyAccountConfirmationUserTemplateSID($storeId = Null)
    {
        if($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::MY_ACCOUNTSID_SMS_CUSTOMER_SIGNUP_CONFIRM_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpConfirmationUserLangcode($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SIGNUP_CONFIRMATIOM_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpConfirmationUserTempId($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SIGNUP_CONFIRMATIOM_TEMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpConfirmationUserParams($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SIGNUP_CONFIRMATIOM_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    public function isSignUpNotificationForAdmin($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_ADMIN_SIGNUP_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSignUpNotificationForAdminTemplate($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
               $storeId
            );
        }
    }
    public function getSignUpNotificationForAdminLangCode($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_SIGNUP_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpNotificationForAdminTmpId($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpNotificationForAdminParams($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_ADMIN_SIGNUP_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    public function isSignUpNotificationForUser($storeId = Null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_CUSTOMER_SIGNUP_NOTIFICATION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSignUpNotificationForUserTemplate($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpNotificationForUserLangCode($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SIGNUP_NOTIFICATION_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpNotificationForUserTmpId($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpNotificationForUserParams($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_SIGNUP_NOTIFICATION_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    public function isMobileConfirmationForUser($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_CUSTOMER_MOBILE_CONFIRMATIOM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMobileConfirmationUserTemplate($storeid = Null)
    {
        if ($this->isEnabled($storeid)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_MOBILE_CONFIRMATIOM_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeid
            );
        }
    }
    public function getMobileConfirmationUserLangCode($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_MOBILE_CONFIRMATIOM_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getMobileConfirmationUserTmpId($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_MOBILE_CONFIRMATIOM_TEMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getMobileConfirmationUserParams($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_MOBILE_CONFIRMATIOM_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    public function isOrderConfirmationForUser($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->scopeConfig->getValue(
            self::SMS_IS_CUSTOMER_ORDER_CONFIRMATIOM,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getOrderConfirmationUserTemplate($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_ORDER_CONFIRMATIOM_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getOrderConfirmationUserLangCode($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_ORDER_CONFIRMATIOM_LANGCODE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getOrderConfirmationUserTmpId($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_ORDER_CONFIRMATIOM_TEMPID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getOrderConfirmationUserParams($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SMS_CUSTOMER_ORDER_CONFIRMATIOM_PARAMS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }
    public function getSignUpNotificationForUserSID($storeId)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SID_SMS_CUSTOMER_SIGNUP_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid()
            );
        }
    }
    public function getSignUpNotificationForAdminSID($storeId)
    {
        if ($storeId==null) {
            $storeId = $this->getStoreid();
        }
        if ($this->isEnabled($storeId)) {
            return  $this->scopeConfig->getValue(
                self::SID_SMS_ADMIN_SIGNUP_NOTIFICATION_TEMPLATE,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreid()
            );
        }
    }
}
