<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://www.magezon.com)
 */

namespace Magezon\LookBook\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magezon\LookBook\Api\Data\CategoryInterface;
use Magezon\LookBook\Model\ResourceModel\Profile\Collection;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filter\FilterManager;
use \Magezon\Core\Helper\Data as CoreHelper;
use Magezon\LookBook\Helper\Data as DataHelper;

class Category extends AbstractModel implements CategoryInterface, IdentityInterface
{
    /**#@+
     * Profile's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * LookBook category cache tag
     */
    const CACHE_TAG = 'lookbook_c';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'lookbook_category';

    /**
     * @var Collection
     */
    protected $filter;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Collection
     */
    protected $urlBuilder;

    /**
     * @var Collection
     */
    protected $dataHelper;

    /**
     * @var Collection
     */
    protected $profileCollection;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $storeManager,
        FilterManager $filter,
        UrlInterface $urlBuilder,
        CoreHelper $coreHelper,
        DataHelper $dataHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->storeManager = $storeManager;
        $this->filter       = $filter;
        $this->urlBuilder   = $urlBuilder;
        $this->coreHelper   = $coreHelper;
        $this->dataHelper   = $dataHelper;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Category::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @param int $id
     * @return CategoryInterface|Category
     */
    public function setId($id)
    {
        return $this->setData(self::CATEGORY_ID, $id);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * @param string $identifier
     * @return CategoryInterface|Category
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param string $title
     * @return CategoryInterface|Category
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @return bool|int
     */
    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @param bool|int $isActive
     * @return CategoryInterface|Category
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return parent::getData(self::DESCRIPTION);
    }

    /**
     * @param string $description
     * @return CategoryInterface|Category
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return int
     */
    public function getIncludeInMenu()
    {
        return $this->getData(self::INCLUDE_IN_MENU);
    }

    /**
     * @param int $includeInMenu
     * @return CategoryInterface|Category
     */
    public function setIncludeInMenu($includeInMenu)
    {
        return $this->setData(self::INCLUDE_IN_MENU, $includeInMenu);
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @param string $metaTitle
     * @return CategoryInterface|Category
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * @param string $metaKeywords
     * @return CategoryInterface|Category
     */
    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @param string $metaDescription
     * @return CategoryInterface|Category
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @param int $position
     * @return CategoryInterface|Category
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @return string
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * @param string $creationTime
     * @return CategoryInterface|Category
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * @param string $updateTime
     * @return CategoryInterface|Category
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * @return string
     */
    public function getPageLayout()
    {
        return $this->getData(self::PAGE_LAYOUT) ? $this->getData(self::PAGE_LAYOUT) : '2columns-left';
    }

    /**
     * @param string $pageLayout
     * @return CategoryInterface|Category
     */
    public function setPageLayout($pageLayout)
    {
        return $this->setData(self::PAGE_LAYOUT, $pageLayout);
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        return parent::getData(self::CANONICAL_URL);
    }

    /**
     * @param string $canonicalUrl
     * @return CategoryInterface|Category
     */
    public function setCanonicalUrl($canonicalUrl)
    {
        return $this->setData(self::CANONICAL_URL, $canonicalUrl);
    }

    /**
     * @return ResourceModel\Profile\Collection
     */
    public function getProfileCollection()
    {
        if ($this->profileCollection === null) {
            $this->profileCollection = $this->_getResource()->getProfileCollection($this);
        }
        return $this->profileCollection;
    }

    /**
     * @return array
     */
    public function getProfilesPosition()
    {
        $array = $this->getData('profiles_position');
        if ($array === null) {
            $array = $this->_getResource()->getProfilesPosition($this);
            $this->setData('profiles_position', $array);
        }
        return $array;
    }

    /**
     * @param $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->filter->translitUrl($str);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $dataHelper = $this->dataHelper;
        $route      = $dataHelper->getRoute();
        $identifier = $route . '/';
        $identifier .= 'category' . '/';
        $identifier .= $this->getIdentifier() . $dataHelper->getCategoryUrlSuffix();
        return $this->urlBuilder->getUrl(null, ['_direct' => $identifier]);
    }

    /**
     * @return int
     */
    public function getProfileCount()
    {
        if (!$this->hasData('profile_count')) {
            $count = $this->_getResource()->getProfileCount($this);
            $this->setData('profile_count', $count);
        }
        return $this->getData('profile_count');
    }

    /**
     * @param $count
     * @return $this
     */
    public function setProfileCount($count)
    {
        $this->setData('profile_count', $count);
        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        $image = $this->getData('image');
        if (!$image) {
            return $this->dataHelper->getViewImageUrl('Magezon_LookBook::images/default.png');
        }
        return $this->coreHelper->getMediaUrl() . $image;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        $content = $this->coreHelper->filter($this->getDescription());
        $content = $this->filter->stripTags(
            $content,
            ['allowableTags' => '', 'escape' => null]
        );
        $short = $this->coreHelper->substr($content, 278);
        if (strlen($short) > strlen($content)) {
            $short = $content;
        } else {
            $short .= '...';
        }
        return $short;
    }
}
