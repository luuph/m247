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

namespace Magezon\LookBook\Model\Category\Source;

use Magento\Framework\Registry;
use Magento\Framework\Data\OptionSourceInterface;
use Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory;

class CategoriesTree implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $_items;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * CategoriesTree constructor.
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Registry $registry,
        CollectionFactory $collectionFactory
    ) {
        $this->registry          = $registry;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $_option
     * @return array
     */
    protected function prepareOptions($_option)
    {
        $currentCategory = $this->getCurrentCategory();
        $childrens = [];
        foreach ($this->_items as $k => $_category) {
            if ($_category->getParentId() == $_option['value'] && (!$currentCategory || ($currentCategory->getId() !== $_category->getId()))) {
                $hasChildren = false;
                $children = [
                    'label' => $_category->getTitle(),
                    'value' => $_category->getId()
                ];
                foreach ($this->_items as $_category2) {
                    if ($_category2->getParentId() == $_category->getId()) {
                        $hasChildren = true;
                        break;
                    }
                }
                if ($hasChildren && ($_children = $this->prepareOptions($children))) {
                    $children['optgroup'] = $_children;
                }
                $childrens[] = $children;
            }
        }
        return $childrens;
    }

    /**
     * Get OptionSourceInterface
     *
     * @return array
     */
    public function toOptionArray($addEmptyField = true)
    {
        $_options = [];
        $currentCategory = $this->getCurrentCategory();
        $collection = $this->collectionFactory->create();
        $collection->setOrder('position', 'ASC');
        $collection->addIsActiveFilter();
        $_items = $collection->getItems();
        if ($addEmptyField) {
            $_options[] = [
                'label' => __('None'),
                'value' => 0
            ];
        }
        foreach ($_items as $k => $_category) {
            if (!$_category->getParentId() && (!$currentCategory || ($currentCategory->getId() !== $_category->getId()))) {
                $_options[] = [
                    'label' => $_category->getTitle(),
                    'value' => $_category->getId()
                ];
                unset($_items[$k]);
            }
        }
        $this->_items = $_items;
        $this->_options = $_options;


        foreach ($_options as &$_option) {
            $children = $this->prepareOptions($_option);
            if ($children) {
                $_option['optgroup'] = $children;
            }
        }
        return $_options;
    }

    /**
     * Retrive current category instance
     *
     * @return \Magezon\LookBook\Model\Category
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Filter collection to only active or inactive rules
     *
     * @param int $isActive
     * @return \Magezon\LookBook\Model\ResourceModel\Profile\Collection
     */
    public function addIsActiveFilter($isActive = 1)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter('main_table.is_active', 1);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }
}
