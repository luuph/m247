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

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Notification extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_reward_point_notification', 'notification_id');
    }

    /**
     * Get notification id
     *
     * @param AbstractModel $object
     * @param string $value
     * @param string|null $field
     * @return bool|int|string
     * @throws LocalizedException
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getNotificationId(AbstractModel $object, $value, $field = null)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'customer_id = ?',
            $value
        );

        $result = $this->getConnection()->fetchCol($select);

        $notificationId = count($result) ? $result[0] : false;

        return $notificationId;
    }
    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $notificationId = $this->getNotificationId($object, $value, $field);
        return parent::load($object, $notificationId);
    }

    /**
     * Get notification by customer
     *
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNotificationByCustomer($customerId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['notify_balance', 'notify_expiration']
        )->where(
            'customer_id = ?',
            $customerId
        );

        $result = $this->getConnection()->fetchRow($select);

        return $result;
    }

    /**
     * Get point expired per customer
     *
     * @param string $date
     * @param int $websiteId
     * @return \Zend_Db_Statement_Interface
     */
    public function getPointExpiredPerCustomer($date, $websiteId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('bss_reward_point_transaction'),
            [
                'customer_id',
                'expires_at',
                'created_at',
                'website_id',
                'point_balance' => 'sum(point)'
            ]
        )->joinLeft(
            ['ce' => 'customer_entity'],
            'ce.entity_id=bss_reward_point_transaction.customer_id',
            ['ce.store_id']
        )->where(
            'cast(date_add(bss_reward_point_transaction.created_at, interval expires_at day) as date) = ?',
            $date
        )->where(
            'is_expired = ?',
            1
        )->where(
            'point > ?',
            0
        )->where(
            'bss_reward_point_transaction.website_id = ?',
            $websiteId
        )->group('customer_id');

        return $this->getConnection()->query($select);
    }
}
