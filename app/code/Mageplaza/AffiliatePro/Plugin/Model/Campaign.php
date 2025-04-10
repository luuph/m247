<?php

namespace Mageplaza\AffiliatePro\Plugin\Model;

use Mageplaza\Affiliate\Model\Campaign as AffiliateCampaign;

/**
 * Class Campaign
 * @package Mageplaza\AffiliatePro\Plugin\Model
 */
class Campaign
{
    /**
     * @param AffiliateCampaign $subject
     * @param callable $proceed
     *
     * @return string
     */
    public function aroundGetCommission(
        AffiliateCampaign $subject,
        callable $proceed
    )
    {
        return $subject->getData('commission');
    }
}
