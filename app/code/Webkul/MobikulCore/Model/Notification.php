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

namespace Webkul\MobikulCore\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Webkul\MobikulCore\Api\Data\NotificationInterface;

/**
 * Class NotificationImage model
 */
class Notification extends AbstractModel implements NotificationInterface, IdentityInterface
{
    protected const NOROUTE_ID = "no-route";
    protected const STATUS_ENABLED = 1;
    protected const STATUS_DISABLED = 0;
    protected const TYPE_PRODUCT = "product";
    protected const TYPE_CATEGORY = "category";
    protected const TYPE_OTHERS = "others";
    protected const TYPE_CUSTOM = "custom";
    protected const CACHE_TAG = "mobikul_notification";
    
    /**
     * CacheTag variable
     *
     * @var string
     */
    protected $_cacheTag = "mobikul_notification";
    
    /**
     * EventPrefix variable
     *
     * @var string
     */
    protected $_eventPrefix = "mobikul_notification";

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Notification::class);
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
            return $this->noRouteNotification();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteNotification function
     *
     * @return void
     */
    public function noRouteNotification()
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
            self::TYPE_CATEGORY => __("Category"),
            self::TYPE_OTHERS   => __("Others"),
            self::TYPE_CUSTOM   => __("Custom Collection")
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
     * GetTitle function
     *
     * @return void
     */
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * SetTitle function
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * GetContent function
     *
     * @return void
     */
    public function getContent()
    {
        return parent::getData(self::CONTENT);
    }

    /**
     * SetContent function
     *
     * @param mixed $content
     * @return void
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
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
     * @param boolean $status
     * @return void
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
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
     * @param timestamp $createdAt
     * @return void
     */
    public function setCreatedTime($createdAt)
    {
        return $this->setData(self::CREATED_TIME, $createdAt);
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
     * @param timestamp $updatedAt
     * @return void
     */
    public function setUpdateTime($updatedAt)
    {
        return $this->setData(self::UPDATE_TIME, $updatedAt);
    }
}
