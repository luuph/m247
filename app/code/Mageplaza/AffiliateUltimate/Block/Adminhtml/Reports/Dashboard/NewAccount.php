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
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliateUltimate\Block\Adminhtml\Reports\Dashboard;

use Magento\Framework\Phrase;

/**
 * Class AffiliateUltimate
 * @package Mageplaza\AffiliateUltimate\Block\Adminhtml\Reports\Dashboard
 */
class NewAccount extends Reports
{
    const MAGE_REPORT_CLASS = NewAccount::class;

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AffiliateUltimate::reports/dashboard/new_account.phtml';

    /**
     * @return Phrase|string
     */
    public function getTitle()
    {
        return __('New Affiliate');
    }

    /**
     * @return mixed
     */
    public function getNewAccount()
    {
        $date = $this->getReportsHelper()->getDateRange();
        $accountCollection = $this->affiliateAccount->create()
            ->getCollection()
            ->addFieldToFilter('main_table.created_at', ['gteq' => $date[0]])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $date[1]])
            ->setOrder('created_at', 'DESC');
        $accountCollection->getSelect()->join(
            ['customer' => $accountCollection->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            ['email', 'firstname', 'lastname']
        );
        $accountCollection->setPageSize(5);

        return $accountCollection;
    }
}
