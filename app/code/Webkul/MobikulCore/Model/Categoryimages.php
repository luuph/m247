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
use Webkul\MobikulCore\Api\Data\CategoryimagesInterface;

/**
 * Class Categoryimages model
 */
class Categoryimages extends AbstractModel implements CategoryimagesInterface, IdentityInterface
{
    protected const NOROUTE_ID = "no-route";
    protected const CACHE_TAG = "mobikul_categoryimages";
    
    /**
     * CacheTag variable
     *
     * @var string
     */
    protected $_cacheTag = "mobikul_categoryimages";
    
    /**
     * EventPrefix variable
     *
     * @var string
     */
    protected $_eventPrefix = "mobikul_categoryimages";

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Categoryimages::class);
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
            return $this->noRouteCategoryimages();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteCategoryimages function
     *
     * @return void
     */
    public function noRouteCategoryimages()
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
     * GetIcon function
     *
     * @return void
     */
    public function getIcon()
    {
        return parent::getData(self::ICON);
    }

    /**
     * SetIcon function
     *
     * @param string $icon
     * @return void
     */
    public function setIcon($icon)
    {
        return $this->setData(self::ICON, $icon);
    }

    /**
     * GetBanner function
     *
     * @return void
     */
    public function getBanner()
    {
        return parent::getData(self::BANNER);
    }

    /**
     * SetBanner function
     *
     * @param string $banner
     * @return void
     */
    public function setBanner($banner)
    {
        return $this->setData(self::BANNER, $banner);
    }

    /**
     * GetSmallbanner function
     *
     * @return void
     */
    public function getSmallbanner()
    {
        return parent::getData(self::SMALLBANNER);
    }

    /**
     * SetSmallbanner function
     *
     * @param string $smallbanner
     * @return void
     */
    public function setSmallbanner($smallbanner)
    {
        return $this->setData(self::SMALLBANNER, $smallbanner);
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
     * GetUpdatedAt function
     *
     * @return void
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * SetUpdatedAt function
     *
     * @param Timestamp $updatedAt
     * @return void
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * GetCategoryId function
     *
     * @return void
     */
    public function getCategoryId()
    {
        return parent::getData(self::CATEGORY_ID);
    }

    /**
     * SetCategoryId function
     *
     * @param int $categoryId
     * @return void
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    /**
     * GetCategoryName function
     *
     * @return void
     */
    public function getCategoryName()
    {
        return parent::getData(self::CATEGORY_NAME);
    }

    /**
     * Undocumented function
     *
     * @param string $categoryName
     * @return void
     */
    public function setCategoryName($categoryName)
    {
        return $this->setData(self::CATEGORY_NAME, $categoryName);
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
     * Undocumented function
     *
     * @param Boolean $status
     * @return void
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
