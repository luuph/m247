<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Webkul\MobikulCore\Api\Data\SalesOrderInterface;

/**
 * Class SalesOrder model
 */
class SalesOrder extends AbstractModel implements SalesOrderInterface, IdentityInterface
{
    protected const NOROUTE_ID = "no-route";
    protected const CACHE_TAG = "mobikul_sales_order";
    
    /**
     * CacheTag variable
     *
     * @var string
     */
    protected $_cacheTag = "mobikul_sales_order";
    
    /**
     * EventPrefix variable
     *
     * @var string
     */
    protected $_eventPrefix = "mobikul_sales_order";

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\SalesOrder::class);
    }

    /**
     * Load function
     *
     * @param int $id
     * @param string $field
     * @return void
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteBannerimage();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteBannerimage function
     *
     * @return void
     */
    public function noRouteBannerimage()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * GetIdentities function
     *
     * @return void
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . "_" . $this->getId()];
    }

    /**
     * GetId function
     *
     * @return void
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * SetId function
     *
     * @param int $id
     * @return void
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * GetOrderId function
     *
     * @return void
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * SetOrderId function
     *
     * @param int $orderId
     * @return void
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * GetStoreId function
     *
     * @return void
     */
    public function getStoreId()
    {
        return parent::getData(self::STORE_ID);
    }

    /**
     * SetStoreId function
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * GetCreatedAt function
     *
     * @return void
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * SetCreatedAt function
     *
     * @param Timestamp $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * GetCustomerId function
     *
     * @return void
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * SetCustomerId function
     *
     * @param int $customerId
     * @return void
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * GetOrderTotal function
     *
     * @return void
     */
    public function getOrderTotal()
    {
        return parent::getData(self::ORDER_TOTAL);
    }

    /**
     * SetOrderTotal function
     *
     * @param mixed $orderTotal
     * @return void
     */
    public function setOrderTotal($orderTotal)
    {
        return $this->setData(self::ORDER_TOTAL, $orderTotal);
    }

    /**
     * GetRealOrderId function
     *
     * @return void
     */
    public function getRealOrderId()
    {
        return parent::getData(self::REAL_ORDER_ID);
    }

    /**
     * SetRealOrderId function
     *
     * @param int $realOrderId
     * @return void
     */
    public function setRealOrderId($realOrderId)
    {
        return $this->setData(self::REAL_ORDER_ID, $realOrderId);
    }

    /**
     * GetCustomerName function
     *
     * @return void
     */
    public function getCustomerName()
    {
        return parent::getData(self::CUSTOMER_NAME);
    }

    /**
     * SetCustomerName function
     *
     * @param string $customerName
     * @return void
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }
}
