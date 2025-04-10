<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\MobikulCore\Api\Data\CacheInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Cache model
 */
class Cache extends AbstractModel implements CacheInterface, IdentityInterface
{
    protected const CACHE_TAG = "mobikul_cache";
    
    protected const NOROUTE_ID = "no-route";
    
    /**
     * CacheTag Variable
     *
     * @var String
     */
    protected $_cacheTag = "mobikul_cache";
    
    /**
     * EventPrefix Variable
     *
     * @var String
     */
    protected $_eventPrefix = "mobikul_cache";

    /**
     * Construct function
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Cache::class);
    }

    /**
     * Load function
     *
     * @param int $id
     * @param string $field
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteCache();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteCache function
     */
    public function noRouteCache()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * GetIdentities function
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . "_" . $this->getId()];
    }

    /**
     * GetId function
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * SetId function
     *
     * @param int $id
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * GetETag function
     */
    public function getETag()
    {
        return parent::getData(self::E_TAG);
    }

    /**
     * SetETag function
     *
     * @param string $eTag
     */
    public function setETag($eTag)
    {
        return $this->setData(self::E_TAG, $eTag);
    }

    /**
     * GetCounter function
     */
    public function getCounter()
    {
        return parent::getData(self::COUNTER);
    }

    /**
     * SetCounter function
     *
     * @param int $counter
     */
    public function setCounter($counter)
    {
        return $this->setData(self::COUNTER, $counter);
    }

    /**
     * GetRequestTag function
     */
    public function getRequestTag()
    {
        return parent::getData(self::REQUEST_TAG);
    }

    /**
     * SetRequestTag function
     *
     * @param string $requestTag
     */
    public function setRequestTag($requestTag)
    {
        return $this->setData(self::REQUEST_TAG, $requestTag);
    }

    /**
     * GetApiName function
     */
    public function getApiName()
    {
        return parent::getData(self::API_NAME);
    }

    /**
     * SetApiName function
     *
     * @param string $apiName
     */
    public function setApiName($apiName)
    {
        return $this->setData(self::API_NAME, $apiName);
    }
}
