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
use Amasty\Rma\Model\Request\ResourceModel;
use Amasty\RmaApi\Api\RequestItemRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements RequestItemRepositoryInterface
{
    /**
     * @var ResourceModel\RequestItem
     */
    private $requestItemResource;

    /**
     * @var RequestItemInterfaceFactory
     */
    private $requestItemFactory;

    public function __construct(
        ResourceModel\RequestItem $requestItemResource,
        RequestItemInterfaceFactory $requestItemFactory
    ) {
        $this->requestItemResource = $requestItemResource;
        $this->requestItemFactory = $requestItemFactory;
    }

    public function getById(int $itemId): RequestItemInterface
    {
        $requestItem = $this->requestItemFactory->create();
        $this->requestItemResource->load($requestItem, $itemId);

        if (!$requestItem->getRequestItemId()) {
            throw new NoSuchEntityException(__('Request Item with specified ID "%1" not found.', $itemId));
        }

        return $requestItem;
    }

    public function deleteById(int $itemId): bool
    {
        $requestItem = $this->getById($itemId);

        return $this->delete($requestItem);
    }

    public function delete(RequestItemInterface $requestItem): bool
    {
        $this->requestItemResource->delete($requestItem);

        return true;
    }

    public function save(RequestItemInterface $item): RequestItemInterface
    {
        try {
            if ($item->getRequestItemId()) {
                $item = $this->getById($item->getRequestItemId())->addData($item->getData());
            }

            $this->requestItemResource->save($item);
        } catch (\Exception $e) {
            if ($item->getRequestItemId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Request Item with ID %1. Error: %2',
                        [$item->getRequestId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Request Item. Error: %1', $e->getMessage()));
        }

        return $item;
    }
}
