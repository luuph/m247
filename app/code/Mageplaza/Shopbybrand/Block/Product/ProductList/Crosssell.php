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

namespace Mageplaza\Shopbybrand\Block\Product\ProductList;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\Checkout\Block\Cart\Crosssell as CatalogCrosssell;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote\Item\RelatedProducts;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class Crosssell
 * @package Mageplaza\Shopbybrand\Block\Product\ProductList
 */
class Crosssell extends CatalogCrosssell
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Crosssell constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param Visibility $productVisibility
     * @param LinkFactory $productLinkFactory
     * @param RelatedProducts $itemRelationsList
     * @param StockHelper $stockHelper
     * @param Data $helperData
     * @param array $data
     * @param CollectionFactory|null $productCollectionFactory
     * @param ProductRepositoryInterface|null $productRepository
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Visibility $productVisibility,
        LinkFactory $productLinkFactory,
        RelatedProducts $itemRelationsList,
        StockHelper $stockHelper,
        Data $helperData,
        array $data = [],
        ?CollectionFactory $productCollectionFactory = null,
        ?ProductRepositoryInterface $productRepository = null
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $productVisibility,
            $productLinkFactory,
            $itemRelationsList,
            $stockHelper,
            $data,
            $productCollectionFactory,
            $productRepository
        );

        $this->helperData               = $helperData;
    }

    /**
     * @return Data
     */
    public function getHelper()
    {
        return $this->helperData;
    }
}
