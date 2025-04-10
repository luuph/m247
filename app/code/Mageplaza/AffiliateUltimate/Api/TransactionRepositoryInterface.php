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
use Mageplaza\AffiliateUltimate\Api\Data\TransactionInterface;

/**
 * Interface TransactionRepositoryInterface
 * @api
 */
interface TransactionRepositoryInterface
{
    /**
     * Lists transaction that match specified search criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\TransactionSearchResultInterface Transaction search result
     *     interface.
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $id Affiliate id
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\TransactionSearchResultInterface Transaction search result
     *     interface.
     */
    public function getTransactionByAffiliateId($id);

    /**
     * @param int $id Order id
     *
     * @return \Mageplaza\AffiliateUltimate\Api\Data\TransactionSearchResultInterface Transaction search result
     *     interface.
     */
    public function getTransactionByOrderId($id);

    /**
     * Required(affiliate_id, amount)
     *
     * @param \Mageplaza\AffiliateUltimate\Api\Data\TransactionInterface $data
     *
     * @return int Transaction id created
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(TransactionInterface $data);

    /**
     * @return int
     */
    public function count();

    /**
     * Cancels a specified transaction.
     *
     * @param int $id The transaction ID.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancel($id);

    /**
     * Completes a specified transaction.
     *
     * @param int $id The transaction ID.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function complete($id);
}
