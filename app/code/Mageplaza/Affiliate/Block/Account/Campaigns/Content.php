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

namespace Mageplaza\Affiliate\Block\Account\Campaigns;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\Affiliate\Block\Account\Campaigns;

/**

 * @package Mageplaza\Affiliate\Block\Account\Campaigns
 */
class Content extends Campaigns
{
    /**
     * @return AbstractDb|AbstractCollection|null
     * @throws LocalizedException
     */
    public function getCampaigns()
    {
        $affiliateGroupId = $this->_affiliateHelper->getCurrentAffiliate()->getGroupId() ?: null;
        $collection = $this->campaignFactory->create()
            ->getCollection()->getAvailableCampaignIgnoreDate($affiliateGroupId, $this->_storeManager->getWebsite()->getId());

        $collection->setOrder('campaign_id', 'DESC');
        if ($collection->getSize()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'affiliate.campaigns.pager');
            // assign collection to pager
            $pager->setAvailableLimit([10 => "10",20 => "20", 50 => "50"])->setCollection($collection);
            $this->setChild('pager', $pager);// set pager block in layout
        }

        return $collection;
    }

    /**
     * @return mixed
     */
    public function getCouponCode()
    {
        $couponCode= $this->getCurrentAccount()->getCode();

        return $couponCode;
    }

    /**
     * @return mixed
     */
    public function getCouponSuffix()
    {
        return $this->getAffiliateHelper()->getAffiliateCoupon();
    }
    /**
     * @param $commission
     *
     * @return array
     */
    public function formatCommission($commission)
    {
        $listContent = [];
        if($commission) {
            foreach (json_decode($commission) as $obj) {
                if($obj->value || $obj->value_second) {
                    array_push($listContent,  '<b>' . $obj->name . '</b>');

                }
                if($obj->value) {
                    if($obj->type  === '3') {
                        array_push($listContent,  '<li>'. $this->formatPrice($obj->value)  . ' for each item on first order' . '</li>');
                    } else {
                        array_push($listContent,  '<li>'. round($obj->value,2) . '%' . ' for each item on first order' . '</li>');
                    }
                }
                if($obj->value_second) {
                    if($obj->type_second      === '3') {
                        array_push($listContent, '<li>'. $this->formatPrice($obj->value_second) . ' for each item on next order' . '</li>');
                    } else {
                        array_push($listContent, '<li>'. round($obj->value_second,2) . '%' . ' for each item on next order' . '</li>');
                    }
                }
            }
        }
        return  $listContent;
    }

    /**
     * @param $from
     * @param $to
     *
     * @return string
     */
    public function formatValidDate($from, $to)
    {
        if(!$from && !$to) {
            return 'No limit';
        } else {
            $validDate = '';
            if($from) {
                $validDate =  $validDate . $this->formatDate($from, \IntlDateFormatter::MEDIUM, false);
            }
            if($to && $from) {
                $validDate = $validDate . ' - ' .$this->formatDate($to, \IntlDateFormatter::MEDIUM, false);
            }
            if($to && !$from) {
                $validDate = $validDate . $this->formatDate($to, \IntlDateFormatter::MEDIUM, false);
            }
            return $validDate;
        }
    }

    /**
     * @param $action
     * @param $amount
     *
     * @return string
     */
    public function formatDiscountPolicy($action, $amount)
    {
        if((float)$amount !== 0) {
            if ($action === 'cart_fixed') {
                return $this->formatPrice($amount) . ' discount for for each item in the cart';
            } else {
                return round($amount,2) . '%' . ' discount for for each item in the cart' ;
            }
        }
    }

    /**
     * @return string
     */
    public function getCampaignReferLink($url)
    {
        return $this->_affiliateHelper->getSharingUrl($url);
    }

    /**
     * @param $commission
     *
     * @return array
     */
    public function formatCommissionView($commission)
    {
        $listContent = [];
        if($commission) {
            foreach (json_decode(($commission)) as $obj) {
                if($obj->value || $obj->value_second) {
                    array_push($listContent,  '<li><b> For the person you affiliate directly (' . $obj->name . '): </li> </b>');

                }
                if($obj->value) {
                    if($obj->type  === '3') {
                        array_push($listContent, 'You will receive <b>' . $this->formatPrice($obj->value) . '</b> commission for each product item of the <b>first purchase</b> made using the affiliate referral link');
                    } else {
                        array_push($listContent,  'You will receive <b>' . round($obj->value,2) . '%' . '</b> commission for each product item of the <b>first purchase</b> made using the affiliate referral link');
                    }
                }
                if($obj->value_second) {
                    if($obj->type_second      === '3') {
                        array_push($listContent, '. For the <b>subsequent purchases</b>, you will receive <b>' . $this->formatPrice($obj->value_second) . '</b> commission for each product item.');
                    } else {
                        array_push($listContent, '. For the <b>subsequent purchases</b>, you will receive <b>' . round($obj->value_second,2) . '%' . '</b> commission for each product item.' );
                    }
                }
            }
        }
        return  $listContent;
    }
}
