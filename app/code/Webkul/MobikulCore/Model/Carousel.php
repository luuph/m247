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
use Webkul\MobikulCore\Api\Data\CarouselInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Carousel model
 */
class Carousel extends AbstractModel implements CarouselInterface, IdentityInterface
{
    protected const CACHE_TAG = "mobikul_carousel";
    protected const NOROUTE_ID = "no-route";
    protected const STATUS_ENABLED = 1;
    protected const STATUS_DISABLED = 0;
    
    /**
     * CacheTag variable
     *
     * @var string
     */
    protected $_cacheTag = "mobikul_carousel";
    
    /**
     * EventPrefix variable
     *
     * @var string
     */
    protected $_eventPrefix = "mobikul_carousel";

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Carousel::class);
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
            return $this->noRouteCarousel();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteCarousel function
     *
     * @return void
     */
    public function noRouteCarousel()
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
     * GetImages function
     *
     * @return void
     */
    public function getImages()
    {
        return parent::getData(self::IMAGES);
    }

    /**
     * SetImages function
     *
     * @param string $images
     * @return void
     */
    public function setImages($images)
    {
        return $this->setData(self::IMAGES, $images);
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
     * GetImageIds function
     *
     * @return void
     */
    public function getImageIds()
    {
        return parent::getData(self::IMAGE_IDS);
    }

    /**
     * SetImageIds function
     *
     * @param mixed $imageIds
     * @return void
     */
    public function setImageIds($imageIds)
    {
        return $this->setData(self::IMAGE_IDS, $imageIds);
    }

    /**
     * GetColorCode function
     *
     * @return void
     */
    public function getColorCode()
    {
        return parent::getData(self::COLOR_CODE);
    }

    /**
     * SetColorCode function
     *
     * @param string $colorCode
     * @return void
     */
    public function setColorCode($colorCode)
    {
        return $this->setData(self::COLOR_CODE, $colorCode);
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
     * GetProductIds function
     *
     * @return void
     */
    public function getProductIds()
    {
        return parent::getData(self::PRODUCT_IDS);
    }

    /**
     * SetProductIds function
     *
     * @param mixed $productIds
     * @return void
     */
    public function setProductIds($productIds)
    {
        return $this->setData(self::PRODUCT_IDS, $productIds);
    }
}
