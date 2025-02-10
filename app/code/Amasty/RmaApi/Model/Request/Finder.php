<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Request;

use Amasty\Rma\Api\RequestRepositoryInterface;
use Amasty\Rma\Model\Request\ResourceModel\CollectionFactory;
use Amasty\RmaApi\Api\Data\RequestSearchResultsInterface;
use Amasty\RmaApi\Api\Data\RequestSearchResultsInterfaceFactory;
use Amasty\RmaApi\Api\RequestFinderInterface;
use Amasty\RmaApi\Model\CriteriaApplierTrait;
use Magento\Framework\Api\SearchCriteriaInterface;

class Finder implements RequestFinderInterface
{
    use CriteriaApplierTrait;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RequestSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    public function __construct(
        CollectionFactory $collectionFactory,
        RequestSearchResultsInterfaceFactory $searchResultsFactory,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->requestRepository = $requestRepository;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): RequestSearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $requestCollection = $this->collectionFactory->create();
        $this->applyCriteria($searchCriteria, $requestCollection);
        $searchResults->setTotalCount($requestCollection->getSize());
        $requests = array_map(function ($request) {
            return $this->requestRepository->getById($request->getRequestId());
        }, $requestCollection->getItems());
        $searchResults->setItems($requests);

        return $searchResults;
    }
}
