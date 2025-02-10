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
namespace Bss\RewardPoint\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Rate extends AbstractDb
{
    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_reward_point_rate', 'rate_id');
    }

    /**
     * Fetch rate customer group and website
     *
     * @param \Bss\RewardPoint\Model\Rate $rate
     * @param int|string $customerGroupId
     * @param int|string $websiteId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(\Bss\RewardPoint\Model\Rate $rate, $customerGroupId, $websiteId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'website_id IN (:website_id, 24000)'
        )->where(
            'customer_group_id IN (:customer_group_id, 32000)'
        )->where(
            'status = 1'
        )->order(
            'customer_group_id ASC'
        )->order(
            'website_id ASC'
        )->limit(
            1
        );

        $bind = [
            ':website_id' => (int)$websiteId,
            ':customer_group_id' => (int)$customerGroupId
        ];

        $row = $this->getConnection()->fetchRow($select, $bind);
        if ($row) {
            $rate->addData($row);
        }

        $this->_afterLoad($rate);
        return $this;
    }
}
