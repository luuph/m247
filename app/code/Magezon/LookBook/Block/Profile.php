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

use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magezon\LookBook\Helper\Data;

class Profile extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Magezon_LookBook::profile.phtml';

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Context                                         $context
     * @param \Magento\Catalog\Block\Product\Context          $productContext 
     * @param Registry                                        $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Data                                            $dataHelper
     * @param array                                           $data
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Block\Product\Context $productContext,
        Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productContext  = $productContext;
        $this->imageBuilder    = $productContext->getImageBuilder();
        $this->productRepository = $productRepository;
        $this->coreRegistry = $registry;
        $this->dataHelper   = $dataHelper;
    }

    /**
     * Retrieve product image 
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
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
}