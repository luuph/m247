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

namespace Mageplaza\Shopbybrand\Block\Cart;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class BrandInfo
 * @package Mageplaza\Shopbybrand\Block\Cart
 */
class BrandInfo extends Template
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * BrandInfo constructor.
     *
     * @param Data $helperData
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Data $helperData,
        Context $context,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @param $item
     *
     * @return false|DataObject
     */
    public function getBrand($item) {
        return $this->helperData->getProductBrand($item);
    }

    /**
     * @param $brand
     *
     * @return mixed|string
     */
    public function getBrandDescription($brand)
    {
        return $this->helperData->getBrandDescription($brand, true);
    }

    /**
     * @param $brand
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBrandUrl($brand) {
        return $this->helperData->getBrandUrl($brand);
    }

    /**
     * @param $brand
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBrandImageUrl($brand) {
        return $this->helperData->getBrandImageUrl($brand);
    }

    /**
     * @return array
     */
    public function showBrandInfoCheckout()
    {
        return $this->helperData->showBrandInfoCheckout();
    }

    /**
     * @return array|mixed
     */
    public function getLogoWidth()
    {
        return $this->helperData->getConfigGeneral('logo_width_on_product_page');
    }

    /**
     * @return array|mixed
     */
    public function getLogoHeight()
    {
        return $this->helperData->getConfigGeneral('logo_height_on_product_page');
    }

    /**
     * @param $description
     *
     * @return mixed|string
     */
    public function shortenDesc($description) {
        return $this->helperData->shortenDesc($description);
    }

    public function getProductUrl($item)
    {
        return $item->getProduct()->getUrlInStore();
    }
}