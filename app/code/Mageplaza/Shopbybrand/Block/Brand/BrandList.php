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

namespace Mageplaza\Shopbybrand\Block\Brand;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Shopbybrand\Block\Brand;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class BrandList
 * @package Mageplaza\Shopbybrand\Block\Brand
 */
class BrandList extends Brand
{
    protected $optionIds = [];

    /**
     * @var Collection
     */
    protected $brandCollection;

    /**
     * @param Collection $collection
     *
     * @return BrandList
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function _prepareLayout($collection = null)
    {
        parent::_prepareLayout();

        if ($this->isEnablePagination()) {
            if (!$collection) {
                $collection = $this->getBrandPagination();
            }

            $brands = $this->_brandFactory->create()->getBrandCollection();
            $countBrand = 0;
            if (!$this->helper->isShowBrandsWithoutProducts()) {
                foreach ($brands as $item) {
                    if ($this->getProductQuantity($item['option_id'])) {
                        $countBrand++;
                    }
                }
            } else {
                $countBrand = count($brands);
            }

            if ($countBrand > $this->helper->getBrandConfig('brand_per_page')) {
                $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class)
                    ->setShowPerPage(true)
                    ->setCollection($collection);
                $this->setChild('pager', $pager);
                $this->setPage($collection);
            }
        }

        return $this;
    }

    /**
     * Get ALL Brand List by char or category
     *
     * @param $char
     *
     * @return Collection|mixed
     */
    public function getAllCollectionByChar($char = null)
    {
        if (!$char) {
            $char = $this->getRequest()->getParam('char');
        }

        $collection = $this->getCollection();
        if ($this->isEnablePagination()) {
            $this->setPage($collection);
        }
        if ($char) {
            if (str_contains($char, 'cat')) {
                $idCategory = strstr($char, 'cat', true);
                $collection = $this->helper->getBrandList('category', $this->getOptionIds($idCategory));
                if ($this->isEnablePagination()) {
                    $this->setPage($collection);
                }
                return $collection;
            }

            if ($char !== 'all') {
                $sqlString = $this->helper->checkCharacter($char);
                $collection->getSelect()->where($sqlString);
                $this->optionIds[$char] = $this->getOptionIdsToFilter($collection);
            }
        }

        return $collection;
    }

    /**
     * Get ALL Brand List by char alphabet view
     *
     * @param $char
     * @param $idCategory
     *
     * @return Collection|mixed
     */
    public function getBrandByCharAlphabet($char, $idCategory)
    {
        if ($idCategory) {
            $collection = $this->helper->getBrandList('category', $this->getOptionIds($idCategory));
            $sqlString = $this->helper->checkCharacter($char);
            $collection->getSelect()->where($sqlString);
            $this->optionIds[$char] = $this->getOptionIdsToFilter($collection);
        } else {
            $collection = $this->getAllCollectionByChar($char);
        }

        return $collection;
    }

    /**
     * Get Brand List Per First Character Show
     *
     * @param $char
     * @param $idCategory
     *
     * @return Collection|mixed
     */
    public function getCollectionPerChar($char = null, $idCategory = null)
    {
        if (!$this->brandCollection) {
            if ($idCategory) {
                $this->brandCollection = $this->helper->getBrandList('category', $this->getOptionIds($idCategory));
            } else {
                $this->brandCollection = $this->getCollection(Data::BRAND_FIRST_CHAR);
            }
        }

        if (!$char) {
            $char = $this->getRequest()->getParam('char');
        }

        $countBrandByChar = $this->getRequest()->getParam('count');
        $collection = clone $this->brandCollection;
        $sqlString = $this->helper->checkCharacter($char);

        if ($char && $countBrandByChar) {
            $brandLoadMore = $this->helper->getBrandConfig('brand_load_more');
            $collection->setPageSize($countBrandByChar + $brandLoadMore);
        } else {
            $brandPerChar = $this->helper->getBrandConfig('brand_per_char');
            $collection->setPageSize($brandPerChar);
        }
        $collection->getSelect()->where($sqlString);
        $this->optionIds[$char] = $this->getOptionIdsToFilter($collection);

        return $collection;
    }

    /**
     * Get Category Filter Class for Mixitup
     *
     * @param $optionId
     *
     * @return string
     */
    public function getCatFilterClass($optionId)
    {
        return $this->helper->getCatFilterClass($optionId);
    }

    /**
     * @param $catName
     *
     * @return mixed
     */
    public function getCatNameFilter($catName)
    {
        return str_replace([' ', '*', '/', '\\'], '_', $catName);
    }

    /**
     * @param string $char
     *
     * @return mixed
     */
    public function getOptionIdByChar($char)
    {
        return $this->optionIds[$char];
    }

    /**
     * @param Collection $collection
     *
     * @return string
     */
    public function getOptionIdsToFilter($collection)
    {
        $optionIds = [];

        foreach ($collection as $brand) {
            $optionIds [] = $brand->getId();
        }
        $result = implode(',', $optionIds);
        unset($optionIds);

        return $result;
    }
}
