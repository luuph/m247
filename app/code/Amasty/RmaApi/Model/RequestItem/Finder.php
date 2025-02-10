<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\RequestItem;

use Amasty\Rma\Api\Data\RequestItemInterface;
use Amasty\Rma\Api\Data\RequestItemInterfaceFactory;
use Amasty\Rma\Api\Data\ResolutionInterface;
use Amasty\Rma\Model\Request\RequestItem;
use Amasty\Rma\Model\Request\ResourceModel\RequestItemCollectionFactory;
use Amasty\RmaApi\Api\Data\RequestItemSearchResultsInterface;
use Amasty\RmaApi\Api\Data\RequestItemSearchResultsInterfaceFactory;
use Amasty\RmaApi\Api\RequestItemFinderInterface;
use Amasty\RmaApi\Model\CriteriaApplierTrait;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;

class Finder implements RequestItemFinderInterface
{
    use CriteriaApplierTrait;

    /**
     * @var RequestItemInterfaceFactory
     */
    private $requestItemFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RequestSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        RequestItemInterfaceFactory $requestItemFactory,
        RequestItemCollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        RequestItemSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->requestItemFactory = $requestItemFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): RequestItemSearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $requestItemCollection = $this->collectionFactory->create();
        $this->applyCriteria($searchCriteria, $requestItemCollection);
        $searchResults->setTotalCount($requestItemCollection->getSize());
        $items = [];

        /** @var RequestItem $requestItemModel */
        foreach ($requestItemCollection as $requestItemModel) {
            $item = $this->requestItemFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $item,
                $requestItemModel->getData(),
                RequestItemInterface::class
            );
            $items[] = $item;
        }
        $searchResults->setItems($items);

        return $searchResults;
    }
}
