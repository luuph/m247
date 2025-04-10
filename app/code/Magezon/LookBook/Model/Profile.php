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

use Magento\Rule\Model\AbstractModel;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filter\FilterManager;
use Magezon\LookBook\Api\Data\ProfileInterface;
use Magezon\LookBook\Helper\Data as DataHelper;

class Profile extends AbstractModel implements ProfileInterface, IdentityInterface
{
    const PRODUCT_MEDIA_URL = 'catalog/product';

    const SORT_TIME_ASC         = 'time_asc';
    const SORT_TIME_DESC        = 'time_desc';
    const SORT_NAME_ASC         = 'name_asc';
    const SORT_NAME_DESC        = 'name_desc';
    const SORT_POSITION_ASC     = 'position_asc';
    const SORT_POSITION_DESC    = 'position_desc';

    /**#@+
     * Profile's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'lookbook_profile';

    /**#@-*/
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    // protected $_storeManager;

    /**
     * @var Data\Condition\Converter
     */
    protected $ruleConditionConverter;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $coreRegistry;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $actionCollectionFactory;

    /**
     * @var ResourceModel\Profile\Collection
     */
    protected $products;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var FilterManager
     */
    protected $filter;

     /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var ResourceModel\Category\Collection
     */
    protected $categoryList;

    /**
     * @param \Magento\Framework\Model\Context                               $context
     * @param \Magento\Framework\Registry                                    $registry
     * @param \Magento\Framework\Data\FormFactory                            $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $localeDate
     * @param \Magento\CatalogRule\Model\Rule\Condition\CombineFactory       $combineFactory
     * @param \Magento\CatalogRule\Model\Rule\Action\CollectionFactory       $actionCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null   $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null             $resourceCollection
     * @param UrlInterface                                                   $urlBuilder
     * @param FilterManager                                                  $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Config                                  $catalogConfig
     * @param \Magezon\Core\Helper\Data                                      $coreHelper
     * @param DataHelper                                                     $dataHelper
     * @param array                                                          $relatedCacheTypes
     * @param array                                                          $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        UrlInterface $urlBuilder,
        FilterManager $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magezon\Core\Helper\Data $coreHelper,
        DataHelper $dataHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $relatedCacheTypes = [],
        array $data = []
    ) {
        $this->coreRegistry = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->filter = $filter;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogConfig      = $catalogConfig;
        $this->coreHelper = $coreHelper;
        $this->dataHelper = $dataHelper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magezon\LookBook\Model\ResourceModel\Profile::class);
    }

      /**
     * Getter for rule conditions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->coreRegistry->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Get conditions field set id.
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get actions field set id.
     *
     * @param string $formName
     * @return string
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Format URL key from name or defined key
     *
     * @param string $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->filter->translitUrl($str);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::PROFILE_ID);
    }

    /**
     * @param int $id
     * @return ProfileInterface|Profile
     */
    public function setId($id)
    {
        return $this->setData(self::PROFILE_ID, $id);
    }

    /**
     * @return array|bool
     */
    public function isActive()
    {
        return parent::getData(self::IS_ACTIVE);
    }

    /**
     * @param bool|int $isActive
     * @return ProfileInterface|Profile
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @return array|string|null
     */
    public function getIdentifier()
    {
        return parent::getData(self::IDENTIFIER);
    }

    /**
     * @param string $identifier
     * @return ProfileInterface|Profile
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * @return array|string|null
     */
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * @param string $title
     * @return ProfileInterface|Profile
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @return array|string|null
     */
    public function getDescription()
    {
        return parent::getData(self::DESCRIPTION);
    }

    /**
     * @param string $description
     * @return ProfileInterface|Profile
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return array|string|null
     */
    public function getImage()
    {
        return parent::getData(self::IMAGE);
    }

    /**
     * @param string $image
     * @return ProfileInterface|Profile
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @return array|string|null
     */
    public function getMarker()
    {
        return parent::getData(self::MARKER);
    }

    /**
     * @param string $marker
     * @return ProfileInterface|Profile
     */
    public function setMarker($marker)
    {
        return $this->setData(self::MARKER, $marker);
    }

    /**
     * @return array|string|null
     */
    public function getLayoutType()
    {
        return parent::getData(self::LAYOUT_TYPE);
    }

    /**
     * @param string $layout_type
     * @return ProfileInterface|Profile
     */
    public function setLayoutType($layout_type)
    {
        return $this->setData(self::LAYOUT_TYPE, $layout_type);
    }

    /**
     * @return array|string|null
     */
    public function getCreationTime()
    {
        return parent::getData(self::CREATION_TIME) ? parent::getData(self::CREATION_TIME) : 'empty';
    }

    /**
     * @param string $creation_time
     * @return ProfileInterface|Profile
     */
    public function setCreationTime($creation_time)
    {
        return $this->setData(self::CREATION_TIME, $creation_time);
    }

    /**
     * @return array
     */
    public function getListMarker()
    {
        $markers = $this->coreHelper->unserialize($this->getData('marker'));
        foreach ($markers as $k => $marker) {
            $newItem = new DataObject($marker);
            $markers[$k] = $newItem;
        }
        return array_values($markers);
    }

     /**
     * Get Page layout
     *
     * @return string|null
     */
    public function getPageLayout()
    {
        return parent::getData(self::PAGE_LAYOUT) ? parent::getData(self::PAGE_LAYOUT) : '2columns-left';
    }

    /**
     * Set Page Layout
     *
     * @param $pageLayout
     * @return ProfileInterface|Profile
     */
    public function setPageLayout($pageLayout)
    {
        return $this->setData(self::PAGE_LAYOUT, $pageLayout);
    }

    /**
     * @return string|null
     */
    public function getCustomLayoutUpdateXml()
    {
        return parent::getData(self::CUSTOM_LAYOUT_UPDATE_XML);
    }

    /**
     * @param string $customLayoutUpdateXml
     * @return ProfileInterface|Profile
     */
    public function setCustomLayoutUpdateXml($customLayoutUpdateXml)
    {
        return $this->setData(self::CUSTOM_LAYOUT_UPDATE_XML, $customLayoutUpdateXml);
    }

    /**
     * @return string|null
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @param string $metaTitle
     * @return ProfileInterface|Profile
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * @return string|null
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * @param string $metaKeywords
     * @return ProfileInterface|Profile
     */
    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @param string $metaDescription
     * @return ProfileInterface|Profile
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * @return string|null
     */
    public function getImageBaseUrl()
    {
        $baseUrl = $this->coreHelper->getMediaUrl();
        return $baseUrl;
    }

    /**
     * @return string|null
     */
    public function getImageProductBaseUrl()
    {
        $baseUrl = $this->coreHelper->getMediaUrl() . self::PRODUCT_MEDIA_URL;
        return $baseUrl;
    }

    /**
     * @return string|null
     */
    public function getImageUrl()
    {
        $imageUrl = '';
        if ($this->getData('image')) {
            return $imageUrl = $this->getImageBaseUrl().$this->getData('image');
        } else {
            return $imageUrl = $this->dataHelper->getViewImageUrl('Magezon_LookBook::images/thumbnail-profile.jpg');
        }
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        $markers = $this->coreHelper->unserialize($this->getData('marker'));
        foreach ($markers as $marker) {
            if ($this->products === null) {
                if ($this->getData($marker['sku'])) {
                    $this->products = $this->getData($marker['sku']);
                } else {
                    $this->products = $this->_getResource()->getProducts($this);
                }
            }
        }

        return $this->products;
    }


    public function getMakerProductCollection()
    {
        $listSku = [];
        $markers = $this->coreHelper->unserialize($this->getData('marker'));
        foreach ($markers as $marker) {
            $listSku[] = $marker['sku'];
        }
        $collection = $this->productCollectionFactory->create();
        $collection->addFieldToFilter('sku', ['in' => $listSku])
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents()
                    ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                    ->addUrlRewrite();
        return $collection;
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
        $short = $this->coreHelper->substr($content, 51);
        if (strlen($short) >= strlen($content)) {
            $short = $content;
        } else {
            $short .= '...';
        }
        return $short;
    }

    /**
     * @return string|null
     */
    public function getShortDescriptionProduct($description)
    {
        $content = $this->coreHelper->filter($description);
        $content = $this->filter->stripTags(
            $content,
            ['allowableTags' => '', 'escape' => null]
        );
        $short = $this->coreHelper->substr($content, 51);
        if (strlen($short) >= strlen($content)) {
            $short = $content;
        } else {
            $short .= '...';
        }
        return $short;
    }

    /**
     * @return ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryList()
    {
        if ($this->categoryList === null) {
            if ($this->getData('category_list')) {
                $this->categoryList = $this->getData('category_list');
            } else {
                $this->categoryList = $this->_getResource()->getCategoryList($this);
            }
        }
        return $this->categoryList;
    }

    /**
     * @param array $categoryList
     */
    public function setCategoryList(array $categoryList)
    {
        $this->categoryList = $categoryList;
        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this->getId());
            $this->setData('category_ids', $ids);
        }
        return (array) $this->_getData('category_ids');
    }

    /**
     * @return \Magezon\LookBook\Model\Profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategory()
    {
        if (($categories = $this->getCategoryList()) && !empty($categories)) {
            return $categories[0];
        }
    }

    /**
     * @return array
     */
    public function getProductsPosition()
    {
        $array = $this->getData('products_position');
        if ($array === null) {
            $array = $this->_getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }
        return $array;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUrl()
    {
        $dataHelper = $this->dataHelper;
        $route      = $dataHelper->getRoute();
        $identifier = $route . '/';
        if ($dataHelper->getProfileUseCategories() && ($category = $this->getCategory())) {
            $identifier .= $category->getIdentifier() . '/';
        }
        $identifier .= $this->getIdentifier() . $dataHelper->getProfileUrlSuffix();
        return $this->urlBuilder->getUrl(null, ['_direct' => $identifier]);
    }
}
