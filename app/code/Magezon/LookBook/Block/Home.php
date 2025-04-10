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

namespace Magezon\LookBook\Block;

use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\LookBook\Block\ListProfile;
use Magezon\LookBook\Helper\Data;
use Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory;
use Magezon\LookBook\Model\ResourceModel\Category\Collection;
use Magezon\LookBook\Ui\Component\Form\Field\HomeLayoutType;

class Home extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var integer
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    protected $collection;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var HomeLayoutType
     */
    protected $homeLayoutType;

    /**
     * @param Context                                                          $context
     * @param Registry                                                         $registry
     * @param \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory  $profileCollectionFactory
     * @param Data                                                             $dataHelper
     * @param HomeLayoutType                                                   $homeLayoutType
     * @param array                                                            $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        Data $dataHelper,
        HomeLayoutType $homeLayoutType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->profileCollectionFactory  = $profileCollectionFactory;
        $this->dataHelper                = $dataHelper;
        $this->homeLayoutType            = $homeLayoutType;
    }

    /**
     * @return \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    public function getCollection()
    {
        $collection = $this->collection;
        if ($this->showPager()) {
            $collection->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getPageVarName(), 1));
        }
        return $this->collection;
    }

    /**
     * @param \Magezon\LookBook\Model\ResourceModel\Profile\Collection $collection
     */
    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $this->pageConfig->getTitle()->set($this->dataHelper->getLookBookTitle());
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->dataHelper->getLookBookTitle());
        }
        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @return void
     */
    protected function _addBreadcrumbs()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbsBlock) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link'  => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $title = $this->dataHelper->getLookBookTitle();
            $breadcrumbsBlock->addCrumb(
                'lookbook',
                [
                    'label' => $title,
                    'title' => $title,
                ]
            );
        }
    }

    /**
     * @return Collection
     */
    public function getCategoryCollection()
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->prepareCollection();
        return $collection;
    }

    /**
     * @return \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    public function getProfileCollection()
    {
        if ($this->collection == NULL) {
            $this->collection = $this->profileCollectionFactory->create();
            $this->collection->prepareCollection();
        }
        return $this->collection;
    }

    /**
     * @param $profiles
     * @return string
     */
    public function getProfilesHtml($profiles)
    { 
        $layoutType = $this->dataHelper->getLookBookHomeLayoutType();
        $numberOfColumn = $this->dataHelper->getHomeNumberColumn();
        $block = $this->getLayout()->createBlock(ListProfile::class);
        $block->setCollection($profiles);
        $block->setLayoutType($layoutType);
        $block->setNumberOfCollumn($numberOfColumn);
        return $block->toHtml();
    }

    /**
     * @return string
     */
    public function getProfileListHtml()
    {
        $layoutType = $this->dataHelper->getLookBookHomeLayoutType();
        $numberOfColumn = $this->dataHelper->getHomeNumberColumn();
        $numberProfilesPerPage = $this->dataHelper->getHomeProfilesPerPage();
        $collection = $this->getProfileCollection();
        $block = $this->getLayout()->createBlock(ListProfile::class);
        $block->setCollection($collection);
        $block->setLayoutType('grid');
        $block->setProfilesPerPage($numberProfilesPerPage);
        $block->setNumberOfCollumn($numberOfColumn);
        $block->setShowPager(true);
        return $block->toHtml();
    }

    /**
     * @return string
     */ 
    public function getLayoutType()
    {
        $layoutType = $this->dataHelper->getLookBookHomeLayoutType();
        if ($_layoutType = $this->getRequest()->getParam('layout_type')) {
            if (isset($this->homeLayoutType->toOptionHash()[$_layoutType])) {
                $layoutType = $_layoutType;
            }
        }
        return $layoutType;
    }
}