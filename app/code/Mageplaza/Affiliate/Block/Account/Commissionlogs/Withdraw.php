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

namespace Mageplaza\Affiliate\Block\Account\Commissionlogs;

use Mageplaza\Affiliate\Block\Account\Commissionlogs;


/**
 * Class Withdraw
 * @package Mageplaza\Affiliate\Block\Account
 */
class Withdraw extends Commissionlogs
{
    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWithdraws()
    {
        $collection = $this->withdrawFactory->create()
            ->getCollection()
            ->addFieldToFilter('account_id', $this->getCurrentAccount()->getId());

        $collection->setOrder('withdraw_id', 'DESC');

        if ($collection->getSize()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager',
                'affiliate.withdraws_report.pager');
            // assign collection to pager
            $pager->setAvailableLimit([10 => "10", 20 => "20", 50 => "50"])->setCollection($collection);
            $this->setChild('pager', $pager);// set pager block in layout
        }

        return $collection;
    }


    /**
     * @return mixed
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }



}
