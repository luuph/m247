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
use Mageplaza\AffiliateUltimate\Api\Data\WithdrawInterface;

/**
 * Interface WithdrawRepositoryInterface
 * @api
 */
interface WithdrawRepositoryInterface
{
    /**
     * Lists withdraw that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\WithdrawSearchResultInterface Withdraw search result interface.
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $id The withdraw ID.
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\WithdrawInterface Affiliate.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * @param int affiliateId The Affiliate ID.
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\WithdrawSearchResultInterface Withdraw search result interface.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByAffiliateId($affiliateId);

    /**
     * @param int $id The withdraw ID.
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function approve($id);

    /**
     * @param int $id The withdraw ID.
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function cancel($id);

    /**
     * Required(account_id, amount, payment_method)
     * Paypal method required paypal_email field
     *
     * @param \Mageplaza\AffiliateUltimate\Api\Data\WithdrawInterface $data
     *
     * @return int Withdraw id created
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(WithdrawInterface $data);
}
