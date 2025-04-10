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

namespace Magezon\LookBook\Model\ResourceModel\Category\Relation\Store;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magezon\LookBook\Model\ResourceModel\Category;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var Category
     */
    protected $resourceCategory;

    /**
     * @param Category $resourceCategory
     */
    public function __construct(
        Category $resourceCategory
    ) {
        $this->resourceCategory = $resourceCategory;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedCategoryalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceCategory->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
            $entity->setData('stores', $stores);
        }
        return $entity;
    }
}
