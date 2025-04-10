<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_LookBook
 * @copyright Copyright (C) 2021 Magezon (https://magezon.com)
 */

namespace Magezon\LookBook\Model\Category\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CategoryList implements OptionSourceInterface
{
    /**
     * @var \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magezon\LookBook\Model\ResourceModel\Category\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get OptionSourceInterface
     *
     * @return array
     */
    public function toOptionArray($addEmptyField = true)
    {
        $options = [];
        $collection = $this->collectionFactory->create();
        $collection->setOrder('title', 'ASC');
        foreach ($collection as $k => $_category) {
            $options[] = [
                'label' => $_category->getTitle(),
                'value' => $_category->getId()
            ];
        }
        return $options;
    }
}
