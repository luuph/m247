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
use Mageplaza\AffiliateUltimate\Api\Data\AccountInterface;

/**
 * Interface AccountRepositoryInterface
 * @api
 */
interface AccountRepositoryInterface
{
    /**
     * @param int $id The account ID.
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\AccountInterface Account.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * Lists Account that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\AccountSearchResultInterface Account search result interface.
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param string $email
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\AccountInterface Account
     */
    public function getAccountByEmail($email);

    /**
     * @return int
     */
    public function count();

    /**
     * @param int $id account id
     *
     * @return bool true on success
     */
    public function deleteById($id);

    /**
     * @param int $id account id
     * @param int $value
     *
     * @return bool true on success
     */
    public function changeStatus($id, $value);

    /**
     * @param int $id account id
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\AccountSearchResultInterface Account search result interface.
     */
    public function getChildAccount($id);

    /**
     * @param string $email
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\AccountSearchResultInterface Account search result interface.
     */
    public function getChildAccountByEmail($email);

    /**
     * @param int $id
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\CampaignSearchResultInterface
     */
    public function getCampaignById($id);

    /**
     * @param string $email
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\CampaignSearchResultInterface
     */
    public function getCampaignByEmail($email);

    /**
     * Required(customer_id, group_id, status)
     *
     * @param \Mageplaza\AffiliateUltimate\Api\Data\AccountInterface $data
     *
     * @return int Account id created
     * @throws \Magento\Framework\Exception\LocalizedException;
     */
    public function save(AccountInterface $data);
}
