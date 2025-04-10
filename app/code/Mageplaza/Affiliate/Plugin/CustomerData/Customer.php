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

namespace Mageplaza\Affiliate\Plugin\CustomerData;

use Mageplaza\Affiliate\Helper\Data;

/**
 * Class Customer
 * @package Mageplaza\Affiliate\Plugin\CustomerData
 */
class Customer
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Customer constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        $affData = [];

        if ($this->helper->isEnabled()) {
            $affData = [
                'mp_affiliate' => [
                    'affiliateId'      => $this->helper->getCurrentAffiliate()->getId(),
                    'sharethisPropertyId' => $this->helper->getShareThisPropertyId(),
                    'sharethisOption' => $this->helper->getShareThisOption(),
                    'affiliateCode'    => $this->helper->getCurrentAffiliate()->getCode(),
                    'urlType'          => $this->helper->getUrlType(),
                    'urlPrefix'        => $this->helper->getUrlPrefix(),
                    'urlParam'         => $this->helper->getGeneralUrlParam()
                ]
            ];
        }

        return array_merge($result, $affData);
    }
}
