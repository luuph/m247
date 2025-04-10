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

namespace Magezon\LookBook\Block\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\LookBook\Block\ListProfile;
use Magezon\LookBook\Helper\Data;
use Magezon\LookBook\Ui\Component\Form\Field\CategoryLayoutType;

class View extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var CategoryLayoutType
     */
    protected $categoryLayoutType;

    /**
     * @param Context            $context
     * @param Registry           $registry
     * @param Data               $dataHelper
     * @param CategoryLayoutType $categoryLayoutType
     * @param array              $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        CategoryLayoutType $categoryLayoutType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->dataHelper   = $dataHelper;
        $this->categoryLayoutType = $categoryLayoutType;
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $category = $this->getCurrentCategory();
        $this->pageConfig->getTitle()->set($category->getMetaTitle() ? $category->getMetaTitle() : $category->getTitle());
        $this->pageConfig->setKeywords($category->getMetaKeywords());
        $this->pageConfig->setDescription($category->getMetaDescription());
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(__('Category: %1', $category->getTitle()));
        }
        $this->pageConfig->addRemotePageAsset(
            $category->getCanonicalUrl() ?: $category->getUrl(),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );
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
                    'link'  => $this->dataHelper->getLookBookUrl()
                ]
            );
            $category = $this->getCurrentCategory();
            $breadcrumbsBlock->addCrumb(
                'category', 
                [
                    'label' => __('Category: %1', $category->getTitle()),
                    'title' => $category->getTitle()
                ]
            );
        }
    }

    /**
     * Retrieve current category model object
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * @return string
     */
    public function getProfileListHtml()
    {
        $layoutType = $this->getLayoutType() ?: $this->dataHelper->getCategoryLayoutType();
        $numberOfColumn = $this->dataHelper->getCategoryNumberColumn();
        $numberProfilesPerPage = $this->dataHelper->getCategoryProfilesPerPage();
        $numberOfProfilesCatPageCarousel = $this->dataHelper->getCategoryPageCarouselNumberProfile();
        $category = $this->getCurrentCategory();
        $collection = $category->getProfileCollection();
        $collection->prepareCollection();

        if ($layoutType == 'carousel') {
            $collection->setPageSize($numberOfProfilesCatPageCarousel);
        }

        $block = $this->getLayout()->createBlock(ListProfile::class);
        $block->setCollection($collection);
        $block->setLayoutType($layoutType);

        if ($layoutType == 'grid') {
            $block->setNumberOfCollumn($numberOfColumn);
            $block->setShowPager(true);
            $block->setProfilesPerPage($numberProfilesPerPage);
        }

        return $block->toHtml();
    }

    /**
     * @return string
     */ 
    public function getLayoutType()
    {
        $layoutType = $this->dataHelper->getCategoryLayoutType();
        if ($_layoutType = $this->getRequest()->getParam('layout_type')) {
            if (isset($this->categoryLayoutType->toOptionHash()[$_layoutType])) {
                $layoutType = $_layoutType;
            }
        }
        return $layoutType;
    }
}