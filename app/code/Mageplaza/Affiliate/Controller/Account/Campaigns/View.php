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

namespace Mageplaza\Affiliate\Controller\Account\Campaigns;

use Mageplaza\Affiliate\Controller\Account;

/**
 * Class View
 * @package Mageplaza\Affiliate\Controller\Account\Withdraw
 */
class View extends Account
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $customerId = $this->customerSession->getId();
        $id         = $this->getRequest()->getParam('id');
        $campaign   = $this->campaignFactory->create()->load($id);
//    dd
//    ($campaign);
//        if (!$campaign || !$campaign->getId() || $campaign->getCustomerId() !== $customerId) {
//            $this->messageManager->addErrorMessage(__('Cannot find item.'));
//
//            return $this->_redirect('*/account/campaigns');
//        }

        $this->registry->register('campaign_view_data', $campaign);

        $resultPage = $this->resultPageFactory->create();

        return $resultPage;
    }
}
