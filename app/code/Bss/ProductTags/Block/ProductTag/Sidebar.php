<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductTags\Block\ProductTag;

use Bss\ProductTags\Model\ResourceModel\ProtagIndex\Collection;

/**
 * Class Sidebar
 *
 * @package Bss\ProductTags\Block\ProductTag
 */
class Sidebar extends \Magento\Framework\View\Element\Template
{
    /**
     * Tags enable
     */
    const TAG_ENABLE = 1;

    /**
     * @var \Bss\ProductTags\Helper\Data
     */
    protected $helper;

    /**
     * @var Collection
     */
    protected $collectionFactory;
    /**
     * @var \Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory
     */
    protected $collectionTags;

    /**
     * Sidebar constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionFactory
     * @param \Bss\ProductTags\Helper\Data $helper
     * @param \Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory $collectionTags
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\ProductTags\Model\ResourceModel\ProtagIndex\CollectionFactory $collectionFactory,
        \Bss\ProductTags\Helper\Data $helper,
        \Bss\ProductTags\Model\ResourceModel\ProTags\CollectionFactory $collectionTags,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->collectionTags = $collectionTags;
    }

    /**
     * Get Collection
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        if ($this->helper->getConfig('general/enable') && $this->helper->getConfig('general/tag_in_productpage')) {
            $order = $this->helper->getConfig('general/tag_sort_by');
            $storeId = $this->_storeManager->getStore(true)->getId();
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('store_id', ['in' => [0, $storeId]])
                ->addFieldToFilter('status', self::TAG_ENABLE);
            $data = [];
            $key = [];
            $routerTags = $this->collectionTags->create()->addFieldToSelect('*')->getData();
            if ($order == 'ASC') {
                $collection->setOrder('tag', 'asc');
                foreach ($collection as $col) {
                    if (!in_array($col->getTag(), $key) && !empty($routerTags)) {
                        foreach ($routerTags as $routerTag) {
                            if ($routerTag['tag_key'] == $col->getTagKey()) {
                                $key[] = $col->getTag();
                                $data[] = ['tag_name' => $col->getTag(),
                                    'tag_key' => $col->getTagKey(),
                                    'router_tag' => $routerTag['router_tag']
                                ];
                            }
                        }
                    }
                }
            } elseif ($order == 'DESC') {
                $collection->setOrder('tag', 'DESC');
                foreach ($collection as $col) {
                    if (!in_array($col->getTag(), $key) && !empty($routerTags)) {
                        foreach ($routerTags as $routerTag) {
                            if ($routerTag['tag_key'] == $col->getTagKey()) {
                                $key[] = $col->getTag();
                                $data[] = ['tag_name' => $col->getTag(),
                                    'tag_key' => $col->getTagKey(),
                                    'router_tag' => $routerTag['router_tag']
                                ];
                            }
                        }
                    }
                }
            } else {
                return $this->setOrderByNumberProduct($collection, $storeId);
            }
            return $data;
        }
        return [];
    }

    /**
     * Sort order function
     *
     * @param \Bss\ProductTags\Model\ResourceModel\ProtagIndex\Collection $collection
     * @param int $storeId
     * @return array
     */
    private function setOrderByNumberProduct($collection, $storeId)
    {
        $routerTags = $this->collectionTags->create()->addFieldToSelect('*')->getData();
        $tag = [];
        $key = [];
        foreach ($collection as $value) {

            if (!in_array($value->getTag(), $key) && !empty($routerTags)) {
                foreach ($routerTags as $routerTag) {
                    if ($routerTag['tag_key'] == $value->getTagKey()) {
                        $key[] = $value->getTag();
                        $tag[$value->getTag()] = ['tag_name' => $value->getTag(), 'tag_key' => $value->getTagKey(), 'router_tag' => $routerTag['router_tag']];
                    }
                }
            }
        }
        if (empty($tag)) {
            return [];
        }
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('store_id', ['in' => [0, $storeId]])
            ->addFieldToFilter('tag', ['in' => $tag])
            ->addFieldToFilter('status', '1');
        $collection->getSelect()
            ->columns('COUNT(*) as count')
            ->group('tag')
            ->order('count DESC');
        $dataTag = [];
        foreach ($collection as $value) {
            if (isset($tag[$value->getTag()])) {
                $dataTag[] = $tag[$value->getTag()];
            }
        }
        return $dataTag;
    }
}
