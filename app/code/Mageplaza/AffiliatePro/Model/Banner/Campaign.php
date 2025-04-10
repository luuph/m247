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

namespace Mageplaza\AffiliatePro\Model\Banner;

use Magento\Framework\Option\ArrayInterface;
use Mageplaza\Affiliate\Model\CampaignFactory;

/**
 * Class Campaign
 * @package Mageplaza\AffiliatePro\Model\Banner
 */
class Campaign implements ArrayInterface
{
    const CAMPAIGN_COLLECTION = 1;

    /**
     * @var CampaignFactory
     */
    protected $campaign;

    /**
     * Campaign constructor.
     *
     * @param CampaignFactory $campaignFactory
     */
    public function __construct(CampaignFactory $campaignFactory)
    {
        $this->campaign = $campaignFactory;
    }

    /**
     * @return mixed
     */
    protected function getCampaignCollection()
    {
        $campaignModel = $this->campaign->create();

        return $campaignModel->getCollection();
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $campaigns = $this->getCampaignCollection();
        $options = [];
        foreach ($campaigns as $campaign) {
            $options[] = ['value' => $campaign->getId(), 'label' => $campaign->getName()];
        }

        return $options;
    }
}
