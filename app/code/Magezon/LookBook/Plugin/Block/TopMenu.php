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

namespace Magezon\LookBook\Plugin\Block;

use Magento\Framework\Data\Tree\NodeFactory;

class TopMenu
{
    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Category\Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $categories;

    /**
     * @var array
     */
    protected $profileCatgoryList;

    /**
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magezon\LookBook\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    protected $_menu, $_items;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                    $context
     * @param NodeFactory                                                         $nodeFactory
     * @param \Magento\Framework\App\ResourceConnection                           $resource
     * @param \Magezon\LookBook\Helper\Data                                       $dataHelper
     * @param \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory    $collectionFactory
     * @param \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory     $profileCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        NodeFactory $nodeFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magezon\LookBook\Helper\Data $dataHelper,
        \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magezon\LookBook\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        array $data = []
    ) {
        $this->nodeFactory               = $nodeFactory;
        $this->resource                  = $resource;
        $this->dataHelper                = $dataHelper;
        $this->collectionFactory         = $collectionFactory;
        $this->profileCollectionFactory  = $profileCollectionFactory;
    }

    /**
     * @return \Magezon\LookBook\Model\ResourceModel\Category\Collection
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $collection = $this->collectionFactory->create();
            $collection->prepareCollection()
                ->addFieldToFilter('include_in_menu', 1)
                ->setOrder('position', 'ASC');
            $collection->addTotalProfiles();
            $this->collection = $collection;
        }
        return $this->collection;
    }

    /**
     * @return array
     */
    public function getProfileCategoryList()
    {
        if ($this->profileCatgoryList === null) {
            $ids        = $this->getCollection()->getAllIds();
            $connection = $this->resource->getConnection();
            $select     = $connection->select()->from($this->resource->getTableName('mgz_lookbook_profile_category'))->where('category_id IN (?)', $ids);
            $result     = $connection->fetchAll($select);

            $collection = $this->profileCollectionFactory->create();
            $collection->getSelect()->joinLeft(
                ['mlpc' => $this->resource->getTableName('mgz_lookbook_profile_category')],
                'main_table.profile_id = mlpc.profile_id',
                []
            )->group('main_table.profile_id');

            $collection->prepareCollection();

            foreach ($result as $k => $row) {
                if (!$collection->getItemById($row['profile_id'])) {
                    unset($result[$k]);
                }
            }
            $this->profileCatgoryList = array_values($result);
        }
        return $this->profileCatgoryList;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        if ($this->categories === null) {
            $showProductCount = $this->showProfileCount();
            $categories       = [];
            $ids              = $this->getCollection()->getAllIds();

            $items = $this->getCollection()->getItems();
            foreach ($items as $k => $_category) {
                if (!$_category->getParentId()) {
                    $categories[] = $_category;
                    unset($items[$k]);
                }
            }
            $this->_items = $items;
            foreach ($categories as &$_category) {
                $children = $this->prepareList($_category);
                if ($children) {
                    $_category->setChildren($children);
                }
            }
            $this->categories = $categories;
        }
        return $this->categories;
    }

    /**
     * @param  \Magezon\LookBook\Model\Category $category
     * @return array
     */
    private function prepareList(\Magezon\LookBook\Model\Category $category)
    {
        $childrens = [];
        foreach ($this->_items as $k => $_category) {
            if ($_category->getParentId() == $category->getId()) {
                $hasChildren = false;
                $children = $_category;
                foreach ($this->_items as $_category2) {
                    if ($_category2->getParentId() == $_category->getId()) {
                        $hasChildren = true;
                        break;
                    }
                }
                if ($hasChildren && ($_children = $this->prepareList($children))) {
                    $children->setChildren($_children);
                }
                $childrens[] = $children;
            }
        }
        return $childrens;
    }

    public function prepareMenu($node)
    {
        $html = '';
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            $html .= $this->prepareItem($category, $node);
        }
        return $html;
    }

    public function prepareItem($category, $node, $level = 1)
    {
        $title = $category->getTitle();
        if ($this->showProfileCount()) {
            $title .= ' (' . $category->getProfileCount() . ')';
        }
        $item = $this->nodeFactory->create(
            [
                'data'    => [
                    'name' => $title,
                    'id'   => 'lookbook-note' . $category->getId(),
                    'url'  => $category->getUrl()
                ],
                'idField' => 'id',
                'tree'    => $this->_menu->getTree()
            ]
        );
        $node->addChild($item);
    }

    /**
     * @return boolean
     */
    public function showProfileCount()
    {
        return $this->dataHelper->getConfig('top_navigation/show_profile_count');
    }

    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        if ($this->dataHelper->isEnabled()) {
            if ($this->dataHelper->getNavigationMenu()) {
                $this->_menu = $subject->getMenu();
                $hasActive   = false;
                $title       = $this->dataHelper->getConfig('top_navigation/title');
                $lookbook         = $this->nodeFactory->create(
                    [
                        'data'    => [
                            'name' => $title,
                            'id'   => 'lookbook-note',
                            'url'  => $this->dataHelper->getLookBookUrl(),
                        ],
                        'idField' => 'id',
                        'tree'    => $subject->getMenu()->getTree()
                    ]
                );
                if ($this->dataHelper->getConfig('top_navigation/include_profiles')) {
                    $this->prepareMenu($lookbook);
                }
                $subject->getMenu()->addChild($lookbook);
            }
        }
    }
}
