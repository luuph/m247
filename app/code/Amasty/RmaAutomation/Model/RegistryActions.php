<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Model;

class RegistryActions
{
    public const STATUS_ACTION = 'status_action';
    public const OWNER_ACTION = 'owner_action';
    public const EMAIL_CUSTOMER_ACTION = 'email_customer_action';
    public const EMAIL_CUSTOMER_SENDER = 'email_customer_sender';
    public const EMAIL_CUSTOMER_TEMPLATE = 'email_customer_template';
    public const EMAIL_ADMIN_ACTION = 'email_admin_action';
    public const EMAIL_ADMIN_TEMPLATE = 'email_admin_template';
    public const EMAIL_ADMIN_RECEIVERS = 'email_admin_receivers';

    /**
     * @return array
     */
    public function getActionKeys()
    {
        return [
            self::STATUS_ACTION,
            self::OWNER_ACTION,
            self::EMAIL_CUSTOMER_ACTION,
            self::EMAIL_ADMIN_ACTION
        ];
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function getAdditionalDataByKey($key)
    {
        $dataKeys = [];

        switch ($key) {
            case self::EMAIL_CUSTOMER_ACTION:
                $dataKeys = [
                    self::EMAIL_CUSTOMER_SENDER,
                    self::EMAIL_CUSTOMER_TEMPLATE
                ];
                break;
            case self::EMAIL_ADMIN_ACTION:
                $dataKeys = [
                    self::EMAIL_ADMIN_TEMPLATE,
                    self::EMAIL_ADMIN_RECEIVERS
                ];
                break;
        }

        return $dataKeys;
    }
}
