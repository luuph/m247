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
use Webkul\MobikulCore\Api\Data\FeaturedcategoriesInterface;

/**
 * Class Featuredcategories model
 */
class Featuredcategories extends AbstractModel implements FeaturedcategoriesInterface, IdentityInterface
{
    protected const NOROUTE_ID = "no-route";
    protected const STATUS_ENABLED = 1;
    protected const STATUS_DISABLED = 0;
    protected const CACHE_TAG = "mobikul_featuredcategories";
    
    /**
     * CacheTag Variable
     *
     * @var String
     */
    protected $_cacheTag = "mobikul_featuredcategories";
    
    /**
     * EventPrefix Variable
     *
     * @var String
     */
    protected $_eventPrefix = "mobikul_featuredcategories";

    /**
     * Construct function
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Featuredcategories::class);
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
            return $this->noRouteFeaturedcategories();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteCarouselimage function
     */
    public function noRouteFeaturedcategories()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * GetAvailableStatuses function
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED  => __("Enabled"),
            self::STATUS_DISABLED => __("Disabled")
        ];
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
     * GetFilename function
     */
    public function getFilename()
    {
        return parent::getData(self::FILENAME);
    }

    /**
     * SetFilename function
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME, $filename);
    }

    /**
     * GetCategoryId function
     */
    public function getCategoryId()
    {
        return parent::getData(self::CATEGORY_ID);
    }

    /**
     * GetCategoryId function
     *
     * @param int $categoryId
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    /**
     * GetStoreId function
     */
    public function getStoreId()
    {
        return parent::getData(self::STORE_ID);
    }

    /**
     * SetStoreId function
     *
     * @param int $storeId
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * GetSortOrder function
     */
    public function getSortOrder()
    {
        return parent::getData(self::SORT_ORDER);
    }

    /**
     * SetSortOrder function
     *
     * @param Boolean $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * GetStatus function
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * SetStatus function
     *
     * @param Boolean $status
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * GetCreatedTime function
     */
    public function getCreatedTime()
    {
        return parent::getData(self::CREATED_TIME);
    }

    /**
     * SetCreatedTime function
     *
     * @param Timestamp $createdAt
     */
    public function setCreatedTime($createdAt)
    {
        return $this->setData(self::CREATED_TIME, $createdAt);
    }

    /**
     * GetUpdateTime function
     */
    public function getUpdateTime()
    {
        return parent::getData(self::UPDATE_TIME);
    }

    /**
     * SetUpdateTime function
     *
     * @param Timestamp $updatedAt
     */
    public function setUpdateTime($updatedAt)
    {
        return $this->setData(self::UPDATE_TIME, $updatedAt);
    }
}
