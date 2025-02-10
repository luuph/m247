<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Reason;

use Amasty\Rma\Api\Data\ReasonInterface;
use Amasty\Rma\Api\Data\ReasonInterfaceFactory;
use Amasty\Rma\Model\Reason\Reason;
use Amasty\Rma\Model\Reason\ResourceModel;
use Amasty\RmaApi\Api\Data\ReasonSearchResultsInterface;
use Amasty\RmaApi\Api\Data\ReasonSearchResultsInterfaceFactory;
use Amasty\RmaApi\Api\ReasonFinderInterface;
use Amasty\RmaApi\Model\CriteriaApplierTrait;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;

class Finder implements ReasonFinderInterface
{
    use CriteriaApplierTrait;

    /**
     * @var ReasonInterfaceFactory
     */
    private $reasonFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ReasonSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        ReasonInterfaceFactory $reasonFactory,
        ResourceModel\CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        ReasonSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->reasonFactory = $reasonFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): ReasonSearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $reasonCollection = $this->collectionFactory->create();
        $this->applyCriteria($searchCriteria, $reasonCollection);
        $searchResults->setTotalCount($reasonCollection->getSize());
        $reasons = [];

        /** @var Reason $reasonModel */
        foreach ($reasonCollection as $reasonModel) {
            $reason = $this->reasonFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $reason,
                $reasonModel->getData(),
                ReasonInterface::class
            );
            $reasons[] = $reason;
        }
        $searchResults->setItems($reasons);

        return $searchResults;
    }
}
