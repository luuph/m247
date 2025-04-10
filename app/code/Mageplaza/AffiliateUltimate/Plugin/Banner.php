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

namespace Mageplaza\AffiliateUltimate\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Mageplaza\AffiliatePro\Block\Account\Banner as AffiliateBanner;
use Mageplaza\AffiliatePro\Model\BannerFactory;
use Mageplaza\AffiliatePro\Model\Banner as ModelBanner;
use Mageplaza\AffiliatePro\Model\ResourceModel\Banner as BannerResource;

/**
 * Class Banner
 * @package Mageplaza\AffiliateUltimate\Plugin
 */
class Banner
{
    /**
     * @var Random
     */
    private $randomDataGenerator;

    /**
     * @var BannerResource
     */
    private $bannerResource;

    /**
     * @var BannerFactory
     */
    private $bannerFactory;

    /**
     * the banner token
     */
    private $token = null;

    /**
     * @param Random $randomDataGenerator
     * @param BannerFactory $bannerFactory
     * @param BannerResource $bannerResource
     */
    public function __construct(
        Random $randomDataGenerator,
        BannerFactory $bannerFactory,
        BannerResource $bannerResource
    ) {
        $this->randomDataGenerator = $randomDataGenerator;
        $this->bannerFactory       = $bannerFactory;
        $this->bannerResource      = $bannerResource;
    }

    /**
     * @param AffiliateBanner $subject
     * @param $result
     * @param $banner
     *
     * @return array|string|string[]
     * @throws LocalizedException
     */
    public function afterGetContentText(AffiliateBanner $subject, $result, $banner)
    {
        preg_match_all('/<img.*?src="(.*?)"[^\>]+>/', $result, $matches);

        $banner = $this->createBannerToken($banner);

        $imgSrc = $matches[1];
        $newSrc = $subject->getUrl('affiliate/banner/view', ['key' => $banner->getToken()]);

        return str_replace($imgSrc, $newSrc, $result);
    }

    /**
     * @param $banner
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function createBannerToken($banner)
    {
        $this->token = $banner->getToken();
        if (!$this->token) {
            $attempt = 0;
            do {
                if ($attempt >= 10) {
                    throw new LocalizedException(
                        __('Something went wrong while saving object. Token exist')
                    );
                }
                $token       = $this->randomDataGenerator->getUniqueHash();
                $this->token = $token;
                ++$attempt;
            } while ($this->existToken($token));
        }

        $banner->setToken($this->token);

        return $banner->save();
    }

    /**
     * @param BannerResource $subject
     * @param AbstractModel|ModelBanner $object
     *
     * @return array
     */
    public function beforeSave($subject, $object)
    {
        $click      = $object->getClick();
        $impression = $object->getImpression();

        if ((int) $impression) {
            $ctr = ($click / $impression) * 100;
            $object->setCtr($ctr);
        }

        return [$object];
    }

    /**
     * @param string $token
     *
     * @return int
     */
    private function existToken($token)
    {
        $banner = $this->bannerFactory->create();
        $this->bannerResource->load($banner, $token, 'token');

        return $banner->getId();
    }
}
