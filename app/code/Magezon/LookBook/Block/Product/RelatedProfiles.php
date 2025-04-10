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

namespace Magezon\LookBook\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magezon\LookBook\Helper\Data;
use Magezon\LookBook\Model\ResourceModel\Profile\Collection;
use Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory;

class RelatedProfiles extends Template
{
    /**
     * @var string
     */
    protected $_template = "Magezon_LookBook::product/related_profiles.phtml";

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * RelatedProfiles constructor.
     * @param Context $context
     * @param Data $dataHelper
     * @param CollectionFactory $collectionFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $collectionFactory,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry           = $registry;
        $this->collectionFactory  = $collectionFactory;
        $this->dataHelper         = $dataHelper;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->dataHelper->getConfig('profile_product/enabled') || !$this->getCollection()->count()) {
            return;
        }
        return parent::toHtml();
    }

    /**
     * @return Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $numberOfProfiles = (int)$this->dataHelper->getProductPageCarouselNumberProfile();
            $product = $this->getCurrentProduct();

            $collection = $this->collectionFactory->create();
            $collection->getSelect()->joinLeft(
                ['miap' => $collection->getResource()->getTable('mgz_lookbook_profile_product')],
                'main_table.profile_id = miap.profile_id'
            )->where('miap.sku = ?', $product->getSku())->group('main_table.profile_id');
            $collection->setPageSize($numberOfProfiles);
            $this->collection = $collection;
        }
        return $this->collection;
    }

    /**
     * @return string
     */
    public function getProfilesHtml()
    {
        $block = $this->getLayout()->createBlock(Template::class);
        $block->setItems($this->getCollection()->getItems());
        $block->setTemplate("Magezon_LookBook::product/profiles.phtml");
        return $block->toHtml();
    }
}
