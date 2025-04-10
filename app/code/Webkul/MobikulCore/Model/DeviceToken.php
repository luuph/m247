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

use Magento\Framework\DataObject\IdentityInterface;
use Webkul\MobikulCore\Api\Data\DeviceTokenInterface;

/**
 * Class DeviceToken model
 */
class DeviceToken extends \Magento\Framework\Model\AbstractModel implements DeviceTokenInterface, IdentityInterface
{
    protected const NOROUTE_ID = "no-route";
    protected const CACHE_TAG = "mobikul_devicetoken";
    
    /**
     * CacheTag variable
     *
     * @var string
     */
    protected $_cacheTag = "mobikul_devicetoken";
    
    /**
     * EventPrefix variable
     *
     * @var string
     */
    protected $_eventPrefix = "mobikul_devicetoken";

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\DeviceToken::class);
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
            return $this->noRouteProduct();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteProduct function
     *
     * @return void
     */
    public function noRouteProduct()
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
     * GetToken function
     *
     * @return void
     */
    public function getToken()
    {
        return parent::getData(self::TOKEN);
    }

    /**
     * SetToken function
     *
     * @param string $token
     * @return void
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
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
}
