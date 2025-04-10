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
 * @package     Mageplaza_AffiliatePro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AffiliatePro\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface BannerRepositoryInterface
 * @api
 */
interface BannerRepositoryInterface
{
    /**
     * @param int $id The banner ID.
     *
     * @return \Mageplaza\AffiliatePro\Api\Data\BannerInterface Banner.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * Lists banner that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\AffiliatePro\Api\Data\BannerSearchResultInterface Banner search result
     *     interface.
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $id banner id
     *
     * @return bool true on success
     */
    public function delete($id);

    /**
     * Lists banner that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @param int|null $storeId
     *
     * @return \Mageplaza\AffiliatePro\Api\Data\BannerSearchResultInterface Banner search result
     *     interface.
     */
    public function getAffiliateBanner(SearchCriteriaInterface $searchCriteria, $storeId = null);
}
