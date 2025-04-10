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

use Webkul\MobikulCore\Api\Data\UserImageInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class UserImage model
 */
class UserImage extends \Magento\Framework\Model\AbstractModel implements UserImageInterface, IdentityInterface
{
    protected const NOROUTE_ENTITY_ID = "no-route";

    protected const CACHE_TAG = "mobikul_userimage";

    /**
     * CacheTag Variable
     *
     * @var String
     */
    protected $_cacheTag = "mobikul_userimage";

    /**
     * EventPrefix Variable
     *
     * @var String
     */
    protected $_eventPrefix = "mobikul_userimage";

    /**
     * Construct function
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\UserImage::class);
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
            return $this->noRouteProduct();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteProduct function
     */
    public function noRouteProduct()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
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
     * GetBanner function
     */
    public function getBanner()
    {
        return parent::getData(self::BANNER);
    }

    /**
     * SetBanner function
     *
     * @param string $banner
     */
    public function setBanner($banner)
    {
        return $this->setData(self::BANNER, $banner);
    }

    /**
     * GetProfile function
     */
    public function getProfile()
    {
        return parent::getData(self::PROFILE);
    }

    /**
     * SetProfile function
     *
     * @param string $profile
     */
    public function setProfile($profile)
    {
        return $this->setData(self::PROFILE, $profile);
    }

    /**
     * GetIsSocial function
     */
    public function getIsSocial()
    {
        return parent::getData(self::IS_SOCIAL);
    }

    /**
     * SetIsSocial function
     *
     * @param Boolean $isSocial
     */
    public function setIsSocial($isSocial)
    {
        return $this->setData(self::IS_SOCIAL, $isSocial);
    }

    /**
     * GetCustomerId function
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * SetCustomerId function
     *
     * @param int $customerId
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
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
}
