<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Condition;

use Amasty\Rma\Api\Data\ConditionInterface;
use Amasty\Rma\Api\Data\ConditionInterfaceFactory;
use Amasty\Rma\Model\Condition\Condition;
use Amasty\Rma\Model\Condition\ResourceModel;
use Amasty\RmaApi\Api\ConditionFinderInterface;
use Amasty\RmaApi\Api\Data\ConditionSearchResultsInterface;
use Amasty\RmaApi\Api\Data\ConditionSearchResultsInterfaceFactory;
use Amasty\RmaApi\Model\CriteriaApplierTrait;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;

class Finder implements ConditionFinderInterface
{
    use CriteriaApplierTrait;

    /**
     * @var ConditionInterfaceFactory
     */
    private $conditionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ConditionSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        ConditionInterfaceFactory $conditionFactory,
        ResourceModel\CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        ConditionSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->conditionFactory = $conditionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): ConditionSearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $conditionCollection = $this->collectionFactory->create();
        $this->applyCriteria($searchCriteria, $conditionCollection);
        $searchResults->setTotalCount($conditionCollection->getSize());
        $conditions = [];

        /** @var Condition $conditionModel */
        foreach ($conditionCollection as $conditionModel) {
            $condition = $this->conditionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $condition,
                $conditionModel->getData(),
                ConditionInterface::class
            );
            $conditions[] = $condition;
        }
        $searchResults->setItems($conditions);

        return $searchResults;
    }
}
