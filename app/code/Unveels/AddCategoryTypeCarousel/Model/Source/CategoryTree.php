<?php

namespace Unveels\AddCategoryTypeCarousel\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Category as CategoryModel;

/**
 * Category Tree
 */
class CategoryTree implements OptionSourceInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $categoriesTree;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param RequestInterface $request
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        RequestInterface $request
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getCategoriesTree();
    }

    /**
     * Get categories tree
     *
     * @return array
     */
    protected function getCategoriesTree()
    {
        if ($this->categoriesTree === null) {
            $storeId = $this->request->getParam('store');

            /* @var $categoryCollection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $categoryCollection = $this->categoryCollectionFactory->create();

            // Load necessary category attributes
            $categoryCollection->addAttributeToSelect(['name', 'is_active', 'parent_id', 'path'])
                ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
                ->setStoreId($storeId);

            // Initialize category by ID array to build tree
            $categoryById = [
                CategoryModel::TREE_ROOT_ID => [
                    'value' => CategoryModel::TREE_ROOT_ID,
                    'parent_id' => 0, // Root category has no parent
                    'label' => __('Root Category'),
                    'optgroup' => [] // Initialize empty optgroup for root
                ],
            ];

            // Create category tree structure
            foreach ($categoryCollection as $category) {
                $categoryById[$category->getId()] = [
                    'value' => $category->getId(),
                    'label' => $category->getName(),
                    'is_active' => $category->getIsActive(),
                    'parent_id' => $category->getParentId() ?? CategoryModel::TREE_ROOT_ID, // Default to root if not set
                    'path' => $category->getPath(), // Ensure 'path' is set
                    'optgroup' => [] // Initialize empty optgroup for each category
                ];
            }

            // Build the tree structure recursively
            foreach ($categoryById as $categoryId => &$category) {
                if ($categoryId != CategoryModel::TREE_ROOT_ID) { // Skip root category
                    $parentId = $category['parent_id'];
                    if (isset($categoryById[$parentId])) {
                        $categoryById[$parentId]['optgroup'][] = &$category; // Add to parent's optgroup
                    }
                }
            }

            // Remove categories without children from the optgroup
            foreach ($categoryById as &$category) {
                // Only add categories with children to the dropdown
                if (empty($category['optgroup'])) {
                    unset($category['optgroup']); // Remove optgroup key if empty
                }
            }

            // Assign the built tree to the root's optgroup
            $this->categoriesTree = $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
        }

        return $this->categoriesTree;
    }
}
