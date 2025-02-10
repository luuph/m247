<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Api\Data;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface RewardPointInterface
 */
interface RewardPointInterface
{
    /**
     * Get Website id
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Get reward point of customer
     *
     * @param CustomerInterface $customer
     * @return float
     */
    public function getPoint($customer);

    /**
     * Get Point used
     *
     * @param CustomerInterface $customer
     * @return float
     */
    public function getPointUsed($customer);

    /**
     * Get point expired
     *
     * @param CustomerInterface $customer
     * @return float
     */
    public function getPointExpired($customer);

    /**
     * Get Amount
     *
     * @param CustomerInterface $customer
     * @return float
     */
    public function getAmount($customer);

    /**
     * Get Notify balance
     *
     * @param CustomerInterface $customer
     * @return int
     */
    public function getNotifyBalance(CustomerInterface $customer);

    /**
     * Get Notify expiration
     *
     * @param CustomerInterface $customer
     * @return int
     */
    public function getNotifyExpiration(CustomerInterface $customer);

    /**
     * Get Rate point
     *
     * @param CustomerInterface $customer
     * @return float
     */
    public function getRatePoint(\Magento\Customer\Api\Data\CustomerInterface $customer);
}
