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

use Exception;
use Magento\Framework\View\Result\Page;
use Mageplaza\Affiliate\Controller\Account;

/**
 * Class EmailSubcription
 * @package Mageplaza\Affiliate\Controller\Account
 */
class EmailSubcription extends Account
{
    /**
     * @return Page|void
     */
    public function execute()
    {
        $accountData = $this->getRequest()->getParam('account');
        $subEmail['withdraw_completed'] = isset($accountData['withdraw_completed']);
        $subEmail['withdraw_cancel']    = isset($accountData['withdraw_cancel']);
        $subEmail['update_balance']     = isset($accountData['update_balance']);
        $subEmail['new_campaign']       = isset($accountData['new_campaign']);
        $subEmail['expired_campaign']   = isset($accountData['expired_campaign']);
        $account = $this->dataHelper->getCurrentAffiliate();
        if (!$account || !$account->getId()) {
            $this->_redirect('*/*/');
        }

        try {
            $account->setEmailSubcription($this->dataHelper->JsonEncode($subEmail))->save();
            $this->messageManager->addSuccessMessage(__('Saved successfully!'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the account.'));
        }

        $this->_redirect('*/account/subscribe');
    }
}
