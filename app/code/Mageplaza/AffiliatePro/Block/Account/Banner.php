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
 * @package     Mageplaza_AffiliatePro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliatePro\Block\Account;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Affiliate\Block\Account;
use Mageplaza\Affiliate\Model\Config\Source\Urltype;
use Mageplaza\AffiliatePro\Model\Banner\Status;

/**
 * Class Banner
 * @package Mageplaza\AffiliatePro\Block\Account
 */
class Banner extends Account
{
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Affiliate Links and Banners'));

        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getAvailableBanners()
    {
        $affiliateGroupId = $this->_affiliateHelper->getCurrentAffiliate()->getGroupId() ?: null;
        $campaigns = $this->campaignFactory->create()->getCollection()
            ->getAvailableCampaignIgnoreDate($affiliateGroupId, $this->_storeManager->getWebsite()->getId())
            ->getColumnValues('campaign_id');
        $banner = $this->objectManager->create('Mageplaza\AffiliatePro\Model\Banner');
        $bannerCollection = $banner->getCollection()
            ->addFieldToFilter('campaign_id', ['in' => $campaigns])
            ->addFieldToFilter('status', Status::ENABLED);

        return $bannerCollection;
    }

    /**
     * @param $banner
     *
     * @return string
     */
    public function getLink($banner)
    {
        if (class_exists('\Zend\Validator\Uri')) {
            $validator = new \Zend\Validator\Uri();
        } else {
            $validator = new \Laminas\Validator\Uri();
        }
        $url = $banner->getLink();
        if (!$validator->isValid($url)) {
            $url = $this->getUrl('/');
        }

        return $this->_affiliateHelper->getSharingUrl(
            $url,
            ['source' => 'banner', 'key' => $banner->getId()],
            Urltype::TYPE_PARAM
        );
    }

    /**
     * @param $banner
     *
     * @return string
     */
    public function getContentText($banner)
    {
        if($banner->getContent()) {
            return $this->getBannerLink($banner, $banner->getContentHtml());
        }
        return $this->getBannerLink($banner, '');
    }

    /**
     * @param $banner
     * @param $text
     *
     * @return string
     */
    public function getBannerLink($banner, $text)
    {
        $html = '<a href="' . $this->getLink($banner) . '" ' . ($banner->getRelNofollow() ? "rel='nofollow' " : '') . 'target="_blank">';
        $html .= $this->escapeHtml($text);
        $html .= '</a>';

        return $html;
    }

    /**
     * @return boolc
     */
    public function isEnableBannerReport()
    {
        return $this->getAffiliateHelper()->isModuleOutputEnabled('Mageplaza_AffiliateUltimate')
            && $this->getAffiliateHelper()->getConfigGeneral('show_banner_report');
    }


    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getBannersById($campaign_id)
    {
        $affiliateGroupId = $this->_affiliateHelper->getCurrentAffiliate()->getGroupId() ?: null;
        if(!$campaign_id) {
            $campaigns = $this->campaignFactory->create()->getCollection()
                ->getAvailableCampaignIgnoreDate($affiliateGroupId, $this->_storeManager->getWebsite()->getId())
                ->getColumnValues('campaign_id');
            if(count($campaigns) ) {
                $campaign_id = $campaigns[0];
            }
        }
        $banner = $this->objectManager->create('Mageplaza\AffiliatePro\Model\Banner');
        $bannerCollection = $banner->getCollection()
            ->addFieldToFilter('campaign_id', $campaign_id)
            ->addFieldToFilter('status', Status::ENABLED);

        return $bannerCollection;
    }

    /**
     * @param $content
     *
     * @return int
     */
    public function getImageCount($content)
    {
        $dom = new \DOMDocument();
        $dom->loadHTML($content);

        $imgTags = $dom->getElementsByTagName('img');
        return $imgTags->length;
    }

}
