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

namespace Mageplaza\AffiliateUltimate\Block\Banner;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelper;
use Mageplaza\AffiliatePro\Model\BannerFactory;
use Mageplaza\AffiliatePro\Model\ResourceModel\Banner as BannerResource;

/**
 * Class Account
 * @package Mageplaza\Affiliate\Block
 */
class Ctr extends Template
{
    /**
     * @var AffiliateHelper
     */
    protected $_affiliateHelper;

    /**
     * @var BannerFactory
     */
    protected $bannerFactory;

    /**
     * @var BannerResource
     */
    protected $bannerResource;

    /**
     * @param Context $context
     * @param AffiliateHelper $affiliateHelper
     * @param BannerFactory $bannerFactory
     * @param BannerResource $bannerResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        AffiliateHelper $affiliateHelper,
        BannerFactory $bannerFactory,
        BannerResource $bannerResource,
        array $data = []
    ) {
        $this->_affiliateHelper = $affiliateHelper;
        $this->bannerFactory    = $bannerFactory;
        $this->bannerResource   = $bannerResource;

        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function checkBannerCtr()
    {
        $request   = $this->_request;
        $urlPrefix = $this->_affiliateHelper->getUrlPrefix() ?: 'u';
        $key       = $request->getParam($urlPrefix);
        if (!$key) {
            return $this;
        }

        if ($request->getParam('source') === 'banner') {
            $bannerId = $request->getParam('key');
            if (!$bannerId) {
                return $this;
            }

            $banner = $this->bannerFactory->create()->load($bannerId);
            $click  = $banner->getClick() + 1;

            $banner->setClick($click);

            try {
                $this->bannerResource->save($banner);
            } catch (\Exception $e) {
                return $this;
            }
        }

        return $this;
    }
}
