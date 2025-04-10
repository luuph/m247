<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Api\Data;

/**
 * Interface SalesOrderInterface
 */
interface SalesOrderInterface
{
    public const ID = "id";
    public const ORDER_ID = "order_id";
    public const STORE_ID = "store_id";
    public const CREATED_AT = "created_at";
    public const CUSTOMER_ID = "customer_id";
    public const ORDER_TOTAL = "order_total";
    public const CUSTOMER_NAME = "customer_name";
    public const REAL_ORDER_ID = "real_order_id";

    /**
     * Function getId
     *
     * @return integer
     */
    public function getId();

    /**
     * Function setId
     *
     * @param integer $id id
     */
    public function setId($id);

    /**
     * Function getOrderId
     *
     * @return integer
     */
    public function getOrderId();

    /**
     * Function setOrderId
     *
     * @param integer $orderId orderId
     */
    public function setOrderId($orderId);

    /**
     * Function getStoreId
     *
     * @return integer
     */
    public function getStoreId();

    /**
     * Function setStoreId
     *
     * @param integer $storeId storeId
     */
    public function setStoreId($storeId);

    /**
     * Function getCreatedAt
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Function setCreatedAt
     *
     * @param string $createdAt createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * Function getCustomerId
     *
     * @return integer
     */
    public function getCustomerId();

    /**
     * Function setCustomerId
     *
     * @param integer $customerId customerId
     */
    public function setCustomerId($customerId);

    /**
     * Function getOrderTotal
     *
     * @return integer
     */
    public function getOrderTotal();

    /**
     * Function setOrderTotal
     *
     * @param integer $orderTotal orderTotal
     */
    public function setOrderTotal($orderTotal);

    /**
     * Function getCustomerName
     *
     * @return string
     */
    public function getCustomerName();

    /**
     * Function setCustomerName
     *
     * @param string $customerName customerName
     */
    public function setCustomerName($customerName);

    /**
     * Function getRealOrderId
     *
     * @return integer
     */
    public function getRealOrderId();

    /**
     * Function setRealOrderId
     *
     * @param integer $realOrderId realOrderId
     */
    public function setRealOrderId($realOrderId);
}
