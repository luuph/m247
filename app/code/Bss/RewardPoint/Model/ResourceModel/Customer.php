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

class Customer extends \Magento\Customer\Model\ResourceModel\Customer
{
    /**
     * Get customer name by id
     *
     * @param int $customerId
     * @return string
     */
    public function getNameById($customerId)
    {
        $connection = $this->getConnection();
        $idField = $this->getEntityIdField();
        $select = $connection->select()->from($this->getEntityTable(), ['firstname', 'lastname'])
            ->where("$idField = ?", $customerId);
        $result = $connection->fetchRow($select);
        return $result['firstname'] . " " . $result['lastname'];
    }

    /**
     * Get customer email by id
     *
     * @param int $customerId
     * @return string
     */
    public function getEmailById($customerId)
    {
        $connection = $this->getConnection();
        $idField = $this->getEntityIdField();
        $select = $connection->select()->from($this->getEntityTable(), ['email'])
            ->where("$idField = ?", $customerId);
        return $connection->fetchOne($select);
    }

    /**
     * Get store that customer was created
     *
     * @param int $customerId
     * @return string
     */
    public function getCreatedStore($customerId)
    {
        $connection = $this->getConnection();
        $idField = $this->getEntityIdField();
        $select = $connection->select()->from($this->getEntityTable(), ['store_id'])
            ->where("$idField = ?", $customerId);
        return $connection->fetchOne($select);
    }
}
