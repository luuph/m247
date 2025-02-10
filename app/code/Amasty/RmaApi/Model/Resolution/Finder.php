<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Resolution;

use Amasty\Rma\Api\Data\ResolutionInterface;
use Amasty\Rma\Api\Data\ResolutionInterfaceFactory;
use Amasty\Rma\Model\Resolution\Resolution;
use Amasty\Rma\Model\Resolution\ResourceModel;
use Amasty\RmaApi\Api\Data\ResolutionSearchResultsInterface;
use Amasty\RmaApi\Api\Data\ResolutionSearchResultsInterfaceFactory;
use Amasty\RmaApi\Api\ResolutionFinderInterface;
use Amasty\RmaApi\Model\CriteriaApplierTrait;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;

class Finder implements ResolutionFinderInterface
{
    use CriteriaApplierTrait;

    /**
     * @var ResolutionInterfaceFactory
     */
    private $resolutionFactory;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResolutionSearchResultsInterface
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        ResolutionInterfaceFactory $resolutionFactory,
        ResourceModel\CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        ResolutionSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resolutionFactory = $resolutionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ResolutionSearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $resolutionCollection = $this->collectionFactory->create();
        $this->applyCriteria($searchCriteria, $resolutionCollection);
        $searchResults->setTotalCount($resolutionCollection->getSize());
        $resolutions = [];

        /** @var Resolution $resolutionModel */
        foreach ($resolutionCollection as $resolutionModel) {
            $resolution = $this->resolutionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $resolution,
                $resolutionModel->getData(),
                ResolutionInterface::class
            );
            $resolutions[] = $resolution;
        }
        $searchResults->setItems($resolutions);

        return $searchResults;
    }
}
