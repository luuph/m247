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
use Mageplaza\Affiliate\Model\Transaction\Type;
use Mageplaza\AffiliateUltimate\Model\ResourceModel\Order\Collection;

/**
 * Class AffiliateUltimate
 * @package Mageplaza\AffiliateUltimate\Block\Adminhtml\Reports\Dashboard
 */
class TopAccount extends Reports
{
    const MAGE_REPORT_CLASS = TopAccount::class;

    /**
     * @var string
     */
    protected $topAffiliate = '';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AffiliateUltimate::reports/dashboard/top_account.phtml';

    /**
     * @return Phrase|string
     */
    public function getTitle()
    {
        return __('Top Affiliate');
    }

    /**
     * @return Collection
     */
    public function getNewAccount()
    {
        if (!$this->topAffiliate) {
            $affiliateAccount = $this->affiliateAccount->create()->getCollection();
            $customerCollection = $this->customerFactory->create()->getCollection()->addNameToSelect();
            $transactionCollection = $this->collection->create()->addFieldToFilter('type', Type::COMMISSION);
            $date = $this->getReportsHelper()->getDateRange();
            $this->topAffiliate = $this->orderCollection->jointSelectSql(
                $affiliateAccount,
                $customerCollection,
                $transactionCollection,
                $date
            );
        }

        return $this->topAffiliate;
    }
}
