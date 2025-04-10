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
use Webkul\MobikulCore\Api\Data\BannerimageInterface;

/**
 * Class Bannerimage model
 */
class Bannerimage extends AbstractModel implements BannerimageInterface, IdentityInterface
{
    protected const NOROUTE_ID = "no-route";
    protected const STATUS_ENABLED = 1;
    protected const STATUS_DISABLED = 0;
    protected const TYPE_PRODUCT = "product";
    protected const TYPE_CATEGORY = "category";
    protected const CACHE_TAG = "mobikul_bannerimage";
    
    /**
     * CacheTag variable
     *
     * @var string
     */
    protected $_cacheTag = "mobikul_bannerimage";
    
    /**
     * EventPrefix variable
     *
     * @var string
     */
    protected $_eventPrefix = "mobikul_bannerimage";

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Bannerimage::class);
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
     * GetAvailableStatuses function
     *
     * @return void
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED  => __("Enabled"),
            self::STATUS_DISABLED => __("Disabled")
        ];
    }

    /**
     * GetAvailableTypes function
     *
     * @return void
     */
    public function getAvailableTypes()
    {
        return [
            self::TYPE_PRODUCT  => __("Product"),
            self::TYPE_CATEGORY => __("Category")
        ];
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
     * GetType function
     *
     * @return void
     */
    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    /**
     * SetType function
     *
     * @param string $type
     * @return void
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * GetStatus function
     *
     * @return void
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }
    
    /**
     * SetStatus function
     *
     * @param Boolean $status
     * @return void
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
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
     * GetFilename function
     *
     * @return void
     */
    public function getFilename()
    {
        return parent::getData(self::FILENAME);
    }

    /**
     * SetFilename function
     *
     * @param string $filename
     * @return void
     */
    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME, $filename);
    }

    /**
     * GetProCatId function
     *
     * @return void
     */
    public function getProCatId()
    {
        return parent::getData(self::PRO_CAT_ID);
    }

    /**
     * SetProCatId function
     *
     * @param int $proCatId
     * @return void
     */
    public function setProCatId($proCatId)
    {
        return $this->setData(self::PRO_CAT_ID, $proCatId);
    }

    /**
     * GetSortOrder function
     *
     * @return void
     */
    public function getSortOrder()
    {
        return parent::getData(self::SORT_ORDER);
    }

    /**
     * SetSortOrder function
     *
     * @param int $sortOrder
     * @return void
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * GetUpdateTime function
     *
     * @return void
     */
    public function getUpdateTime()
    {
        return parent::getData(self::UPDATE_TIME);
    }

    /**
     * SetUpdateTime function
     *
     * @param Timestamp $updatedAt
     * @return void
     */
    public function setUpdateTime($updatedAt)
    {
        return $this->setData(self::UPDATE_TIME, $updatedAt);
    }

    /**
     * GetCreatedTime function
     *
     * @return void
     */
    public function getCreatedTime()
    {
        return parent::getData(self::CREATED_TIME);
    }

    /**
     * SetCreatedTime function
     *
     * @param Timestamp $createdAt
     * @return void
     */
    public function setCreatedTime($createdAt)
    {
        return $this->setData(self::CREATED_TIME, $createdAt);
    }
}
