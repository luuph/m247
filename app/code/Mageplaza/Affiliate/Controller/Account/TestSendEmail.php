<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Controller\Account;

use Mageplaza\Affiliate\Controller\Account;

/**
 * Class Setting
 * @package Mageplaza\Affiliate\Controller\Account
 */
class TestSendEmail extends Account
{
    const XML_PATH_EMAIL_NEW_CAMPAIGN_TEMPLATE = 'affiliate/email/campaign_email/new_campaign_template';
    const XML_PATH_EMAIL_EXPIRED_CAMPAIGN_TEMPLATE = 'affiliate/email/campaign_email/expired_campaign_template';
    const XML_PATH_EMAIL_SENDER                           = 'affiliate/email/sender';

    /**
     * @return void
     */
    public function execute()
    {
        $customer = $this->customerSession->getCustomer();
        // Example customer data

        // Example template path
        $template = self::XML_PATH_EMAIL_EXPIRED_CAMPAIGN_TEMPLATE; // Replace with your actual email template path

        // Example template parameters
        $templateParams = [
            'custom_variable' => 'Some value',
        ];

        try {

            $this->dataHelper->sendEmailTemplate($customer, $template, $templateParams,self::XML_PATH_EMAIL_SENDER,null,'noirelleuteugra9346@yopmail.com');
            $this->messageManager->addSuccessMessage(__('Email sent successfully.'));

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error sending email: %1', $e->getMessage()));
        }



    }
}
