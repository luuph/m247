<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Status;

use Amasty\Rma\Api\Data\StatusInterface;
use Amasty\Rma\Api\Data\StatusInterfaceFactory;
use Amasty\Rma\Api\Data\StatusStoreInterfaceFactory;
use Amasty\Rma\Model\OptionSource\State;
use Amasty\Rma\Model\Status\ResourceModel;
use Amasty\Rma\Model\Status\Status;
use Amasty\RmaApi\Api\Data\StatusSearchResultsInterface;
use Amasty\RmaApi\Api\Data\StatusSearchResultsInterfaceFactory;
use Amasty\RmaApi\Api\StatusFinderInterface;
use Amasty\RmaApi\Model\CriteriaApplierTrait;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;

class Finder implements StatusFinderInterface
{
    use CriteriaApplierTrait;

    /**
     * @var StatusInterfaceFactory
     */
    private $statusFactory;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StatusSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        StatusInterfaceFactory $statusFactory,
        ResourceModel\CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        StatusSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StatusSearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $statusCollection = $this->collectionFactory->create();
        $this->applyCriteria($searchCriteria, $statusCollection);
        $searchResults->setTotalCount($statusCollection->getSize());
        $statuses = [];

        /** @var Status $statusModel */
        foreach ($statusCollection as $statusModel) {
            $status = $this->statusFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $status,
                $statusModel->getData(),
                StatusInterface::class
            );
            $statuses[] = $status;
        }
        $searchResults->setItems($statuses);

        return $searchResults;
    }
}
