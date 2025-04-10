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

namespace Mageplaza\AffiliateUltimate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class InitReportCard
 * @package Mageplaza\AffiliateUltimate\Observer
 */
class InitReportCard implements ObserverInterface
{
    const MP_AFFILIATE_DASHBOARD = 'Mageplaza\AffiliateUltimate\Block\Adminhtml\Reports\Dashboard';
    const MP_AFFILIATE_TRANSACTION = self::MP_AFFILIATE_DASHBOARD . '\Transaction';
    const MP_AFFILIATE_NEW_ACCOUNT = self::MP_AFFILIATE_DASHBOARD . '\NewAccount';
    const MP_AFFILIATE_TOP_ACCOUNT = self::MP_AFFILIATE_DASHBOARD . '\TopAccount';
    const MP_AFFILIATE_BESTSELLERS = self::MP_AFFILIATE_DASHBOARD . '\Bestsellers';

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $carts = $observer->getEvent()->getCards();
        $carts->setMpAffiliateTransaction(self::MP_AFFILIATE_TRANSACTION)
            ->setNewAccount(self::MP_AFFILIATE_NEW_ACCOUNT)
            ->setTopAccount(self::MP_AFFILIATE_TOP_ACCOUNT)
            ->setBestsellers(self::MP_AFFILIATE_BESTSELLERS);//
    }
}
