<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Chat;

use Amasty\Rma\Api\Data\MessageInterface;
use Amasty\Rma\Api\Data\MessageInterfaceFactory;
use Amasty\Rma\Model\Chat\Message;
use Amasty\Rma\Model\Chat\ResourceModel;
use Amasty\RmaApi\Api\ChatMessageFinderInterface;
use Amasty\RmaApi\Api\Data\ChatSearchResultsInterface;
use Amasty\RmaApi\Api\Data\ChatSearchResultsInterfaceFactory;
use Amasty\RmaApi\Model\CriteriaApplierTrait;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;

class MessageFinder implements ChatMessageFinderInterface
{
    use CriteriaApplierTrait;

    /**
     * @var MessageInterfaceFactory
     */
    private $messageFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $messageCollectionFactory;

    /**
     * @var ChatSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        MessageInterfaceFactory $messageFactory,
        ResourceModel\CollectionFactory $messageCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        ChatSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->messageFactory = $messageFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->messageCollectionFactory = $messageCollectionFactory;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): ChatSearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $messageCollection = $this->messageCollectionFactory->create();
        $this->applyCriteria($searchCriteria, $messageCollection);
        $searchResults->setTotalCount($messageCollection->getSize());
        $messages = [];

        /** @var Message $messageModel */
        foreach ($messageCollection as $messageModel) {
            $message = $this->messageFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $message,
                $messageModel->getData(),
                MessageInterface::class
            );
            $messages[] = $message;
        }
        $searchResults->setItems($messages);

        return $searchResults;
    }
}
