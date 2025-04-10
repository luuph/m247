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
use Webkul\MobikulCore\Api\Data\WalkthroughInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Walkthrough extends AbstractModel implements WalkthroughInterface, IdentityInterface
{

    protected const CACHE_TAG = "mobikul_walkthrough";
    protected const NOROUTE_ID = "no-route";
    protected const STATUS_ENABLED = 1;
    protected const STATUS_DISABLED = 0;
    
    /**
     * CacheTag variable
     *
     * @var string
     */
    protected $_cacheTag = "mobikul_walkthrough";
    
    /**
     * EventPrefix variable
     *
     * @var string
     */
    protected $_eventPrefix = "mobikul_walkthrough";

    /**
     * Construct function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Walkthrough::class);
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
            return $this->noRouteWalkthrough();
        }
        return parent::load($id, $field);
    }

    /**
     * NoRouteWalkthrough function
     *
     * @return void
     */
    public function noRouteWalkthrough()
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
     * GetDescription function
     *
     * @return void
     */
    public function getDescription()
    {
        return parent::getData(self::DESCRIPTION);
    }

    /**
     * Undocumented function
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * GetImage function
     *
     * @return void
     */
    public function getImage()
    {
        return parent::getData(self::IMAGE);
    }

    /**
     * SetImage function
     *
     * @param string $image
     * @return void
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
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
}
