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
use Webkul\MobikulCore\Api\Data\CarouselimageInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Carouselimage extends AbstractModel implements CarouselimageInterface, IdentityInterface
{
    protected const CACHE_TAG = "mobikul_carouselimage";
    protected const NOROUTE_ID = "no-route";
    protected const TYPE_PRODUCT = "product";
    protected const TYPE_CATEGORY = "category";
    protected const STATUS_ENABLED = 1;
    protected const STATUS_DISABLED = 0;
    /**
     * CacheTag Variable
     *
     * @var String
     */
    protected $_cacheTag = "mobikul_carouselimage";

    /**
     * EventPrefix Variable
     *
     * @var String
     */
    protected $_eventPrefix = "mobikul_carouselimage";

    /**
     * Construct function
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Carouselimage::class);
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
            return $this->noRouteCarouselimage();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteCarouselimage function
     */
    public function noRouteCarouselimage()
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
     * GetAvailableTypes function
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
     * GetType function
     */
    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    /**
     * SetType function
     *
     * @param string $type
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * GetTitle function
     */
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * SetTitle function
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
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
     * GetProCatId function
     */
    public function getProCatId()
    {
        return parent::getData(self::PRO_CAT_ID);
    }

    /**
     * SetProCatId function
     *
     * @param int $proCatId
     */
    public function setProCatId($proCatId)
    {
        return $this->setData(self::PRO_CAT_ID, $proCatId);
    }
}
