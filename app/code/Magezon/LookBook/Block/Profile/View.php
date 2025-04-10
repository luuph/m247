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

namespace Magezon\LookBook\Block\Profile;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magezon\LookBook\Helper\Data;
use Magezon\LookBook\Ui\Component\Form\Field\ProfileLayoutType;

class View extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Magezon_LookBook::profile/view.phtml';

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var ProfileLayoutType
     */
    protected $profileLayoutType;


    /**
     * @param Context                                         $context
     * @param Registry                                        $registry
     * @param Data                                            $dataHelper
     * @param ProfileLayoutType                               $profileLayoutType
     * @param array                                           $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        ProfileLayoutType $profileLayoutType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->dataHelper   = $dataHelper;
        $this->profileLayoutType = $profileLayoutType;
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbs();
        $profile = $this->getCurrentProfile();
        $this->pageConfig->getTitle()->set($profile->getMetaTitle() ?: $profile->getTitle());
        $this->pageConfig->setKeywords($profile->getMetaKeywords());
        $this->pageConfig->setDescription($profile->getMetaDescription());
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
            $profile = $this->getCurrentProfile();
            $breadcrumbsBlock->addCrumb(
                'profile',
                [
                    'label' => $profile->getTitle(),
                    'title' => $profile->getTitle()
                ]
            );
        }
    }

    /**
     * Retrieve current profile model object
     *
     * @return \Magezon\LookBook\Model\Profile
     */
    public function getCurrentProfile()
    {
        return $this->coreRegistry->registry('current_profile');
    }

    /**
     * @param $profiles
     * @return string
     */
    public function getProfileHtml()
    { 
        $profile = $this->getCurrentProfile();
        $block = $this->getLayout()->createBlock(\Magezon\LookBook\Block\Profile::class);
        $block->setProfile($profile);
        $block->setLayoutType($this->getLayoutType());
        return $block->toHtml();
    }

    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return $this->getProductPriceHtml(
            $product,
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
            \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
        );
    }

    /**
     * Return HTML block with tier price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;
        $arguments['use_link_for_as_low_as'] = isset($arguments['use_link_for_as_low_as'])
            ? $arguments['use_link_for_as_low_as']
            : true;

            /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                [
                    'data' => [
                        'price_render_handle'    => 'catalog_product_prices',
                        'use_link_for_as_low_as' => true,
                        'display_minimal_price'  => true
                    ]
                ]
            );
        }
        $price = $priceRender->render(
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
            $product,
            $arguments
        );
        return $price;
    }

    /**
     * @return string
     */ 
    public function getLayoutType()
    {
        $profile = $this->getCurrentProfile();
        $layoutType = $profile->getLayoutType();
        if ($_layoutType = $this->getRequest()->getParam('layout_type')) {
            if (isset($this->profileLayoutType->toOptionHash()[$_layoutType])) {
                $layoutType = $_layoutType;
            }
        }
        return $layoutType;
    }
}