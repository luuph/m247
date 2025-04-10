<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\AffiliateUltimate\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface CampaignRepositoryInterface
 * @api
 */
interface CampaignRepositoryInterface
{
    /**
     * Lists campaign that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\CampaignSearchResultInterface Campaign search result interface.
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $id campaign id
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\CampaignInterface Campaign
     */
    public function get($id);

    /**
     * @param int $id campaign id
     * @param int $value
     *
     * @return bool true on success
     */
    public function changeStatus($id, $value);

    /**
     * @param int $id campaign id
     *
     * @return bool true on success
     */
    public function deleteById($id);
}
