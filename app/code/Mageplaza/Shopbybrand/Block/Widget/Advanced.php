<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Block\Widget;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Model\CategoryFactory;
use Zend_Db_Expr;

/**
 * Class Advanced
 * @package Mageplaza\Shopbybrand\Block\Widget
 */
class Advanced extends AbstractBrand
{
    /**
     * @var string
     */
    protected $_template = "widget/advanced/brand_list.phtml";

    /**
     * @var BrandFactory
     */
    protected $brandFactory;

    /**
     * @var CategoryFactory
     */
    protected $brandCategoryFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Advanced constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param BrandFactory $brandFactory
     * @param CollectionFactory $productCollectionFactory
     * @param CategoryFactory $brandCategoryFactory
     */
    public function __construct(
        Context $context,
        Data $helper,
        BrandFactory $brandFactory,
        CollectionFactory $productCollectionFactory,
        CategoryFactory $brandCategoryFactory
    ) {
        parent::__construct($context, $helper);

        $this->brandFactory             = $brandFactory;
        $this->brandCategoryFactory     = $brandCategoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param $widgetBrandIds
     * @param $limit
     *
     * @return Collection
     * @throws LocalizedException
     */
    protected function getBrandCollection($widgetBrandIds, $limit)
    {
        $storeId         = $this->_storeManager->getStore()->getId();
        $brandCollection = $this->brandFactory->create()->getBrandCollection($storeId)
            ->addFieldToFilter('main_table.option_id', ['in' => $widgetBrandIds]);
        if ($limit) {
            $brandCollection->getSelect()->limit($limit, 0);
        }

        return $brandCollection;
    }

    /**
     * @return Collection|Select
     * @throws LocalizedException
     */
    public function getWidgetBrandCollection()
    {
        $brandIds        = explode(',', $this->getData('option_id'));
        $brandCategoryId = $this->getData('brand_category');

        $categoryBrandIds = $brandCategoryId ? $this->getCategoryOptionId($brandCategoryId) : [];
        $widgetBrandIds   = count($categoryBrandIds) ? array_intersect($brandIds, $categoryBrandIds) : $brandIds;

        $limit       = $this->getData('limit_brands');
        $sortBrandBy = $this->getData('sort_brand_by');
        $sortDir     = $this->getData('sort_dir') ?: 'asc';
        switch ($sortBrandBy) {
            case 'alphabet':
                $collection = $this->getBrandCollection(
                    $widgetBrandIds,
                    $limit
                );
                $collection->getSelect()->reset('order')->order('value ' . $sortDir);

                return $collection;
            case 'option_id':
                $collection = $this->getBrandCollection(
                    $widgetBrandIds,
                    $limit
                );
                $collection->getSelect()->reset('order')->order('option_id ' . $sortDir);

                return $collection;
            case 'num_products':
                $brandCollection = $this->getBrandCollection($widgetBrandIds, $limit);
                $data            = $this->sortByNumberProducts($brandCollection);
                if (count($data)) {
                    $brandCollection = $this->getBrandCollection($widgetBrandIds, $limit);
                    $brandCollection->getSelect()->reset('order')->order(new Zend_Db_Expr(
                        'FIELD(main_table.option_id, "' . implode('","', $data) . '") ' . $sortDir
                    ));
                }

                return $brandCollection;
            case 'random':
                $brandCollection = $this->getBrandCollection($widgetBrandIds, $limit);
                $brandCollection->getSelect()->reset('order')->order(new Zend_Db_Expr('RAND()'));

                return $brandCollection;
        }

        return $this->getBrandCollection($widgetBrandIds, $limit);
    }

    /**
     * @param $brandCollection
     *
     * @return array
     */
    protected function sortByNumberProducts($brandCollection)
    {
        $data = [];
        foreach ($brandCollection as $brand) {
            $optionId        = $brand->getOptionId();
            $numberProducts  = $this->getNumberProducts($optionId);
            $data[$optionId] = $numberProducts;
        }
        asort($data);

        return array_keys($data);
    }

    /**
     * Get number products of brand
     *
     * @param $optionId
     *
     * @return int
     */
    protected function getNumberProducts($optionId)
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->setVisibility(
                [
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_BOTH
                ]
            )
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter($this->helper->getAttributeCode(), $optionId);

        return $productCollection->count() ?: 0;
    }

    /**
     * Get all option_id of category
     *
     * @param $categoryIds
     *
     * @return array
     */
    protected function getCategoryOptionId($categoryIds)
    {
        $categoryIds    = explode(',', $categoryIds);
        $categoryFilter = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->brandCategoryFactory->create()->load($categoryId);
            if ($category->getStatus()) {
                $categoryFilter[] = $categoryId;
            }
        }
        $categoryFilter = implode(',', $categoryFilter);

        if (strlen($categoryFilter) == 0) {
            return [];
        }

        $sql    = 'main_table.cat_id IN (' . $categoryFilter . ')';
        $result = [];
        $brands = $this->brandCategoryFactory->create()->getCategoryCollection($sql, null)->getData();
        foreach ($brands as $brand => $item) {
            $result[] = $item['option_id'];
        }

        return $result;
    }

    /**
     * Get logo width
     *
     * @return array|mixed
     */
    public function getLogoWidth()
    {
        return $this->helper->getModuleConfig('brandpage/brand_logo_width');
    }

    /**
     * Get logo height
     *
     * @return mixed
     */
    public function getLogoHeight()
    {
        return $this->helper->getModuleConfig('brandpage/brand_logo_height');
    }

    /**
     * Get slider design config
     *
     * @return false|string
     */
    public function getJsSliderConfig()
    {
        $data = $this->getData();

        if (array_key_exists('display_style', $data) && $data['display_style'] != '0') {
            return false;
        }

        return Data::jsonEncode([
            'nextPrevButton' => $data['next_prev_button'],
            'showDotsNav'    => $data['show_dots_nav'],
            'autoPlay'       => $data['auto_play'],
            'autoTimeout'    => isset($data['auto_timeout']) ? $data['auto_timeout'] : null
        ]);
    }

    /**
     * Check type display of widget
     *
     * @return bool
     */
    public function isSlider()
    {
        return $this->getData('display_style') == '0';
    }

    /**
     * Get slider height
     *
     * @return array|mixed|string
     */
    public function getSliderHeight()
    {
        return $this->getData('slider_height') ?: '100%';
    }

    /**
     * Get slider width
     *
     * @return array|mixed|string
     */
    public function getSliderWidth()
    {
        return $this->getData('slider_width') ?: '100%';
    }

    /**
     * Get widget title
     *
     * @return array|Phrase|mixed
     */
    public function getWidgetTitle()
    {
        return $this->getData('title') ?: __('Brands');
    }
}
