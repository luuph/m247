<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel;

use Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;
use Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterfaceFactory;
use Amasty\RmaAutomaticShippingLabel\Api\ShippingLabelRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements ShippingLabelRepositoryInterface
{
    /**
     * @var ShippingLabelInterfaceFactory
     */
    private $shippingLabelFactory;

    /**
     * @var ResourceModel\ShippingLabel
     */
    private $resource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Model storage
     *
     * @var ShippingLabelInterface[]
     */
    private $shippingLabels;

    public function __construct(
        ShippingLabelInterfaceFactory $shippingLabelFactory,
        ResourceModel\ShippingLabel $resource,
        ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->shippingLabelFactory = $shippingLabelFactory;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
    }

    public function getById(int $id): ShippingLabelInterface
    {
        if (!isset($this->shippingLabels[$id])) {
            /** @var ShippingLabelInterface $shippingLabel */
            $shippingLabel = $this->shippingLabelFactory->create();
            $this->resource->load($shippingLabel, $id);

            if (!$shippingLabel->getLabelId()) {
                throw new NoSuchEntityException(__('Shipping label with specified ID "%1" not found.', $id));
            }
            $this->shippingLabels[$id] = $shippingLabel;
        }

        return $this->shippingLabels[$id];
    }

    public function getByRequestId(int $id): ShippingLabelInterface
    {
        /** @var ResourceModel\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect(ShippingLabelInterface::LABEL_ID)
            ->addFieldToFilter(ShippingLabelInterface::REQUEST_ID, $id);

        if (!($labelId = (int)$collection->getFirstItem()->getLabelId())) {
            throw new NoSuchEntityException(__('Shipping label for request with ID "%1" not found', $id));
        }

        return $this->getById($labelId);
    }

    public function save(ShippingLabelInterface $shippingLabel): ShippingLabelInterface
    {
        try {
            $this->resource->save($shippingLabel);
            unset($this->shippingLabels[$shippingLabel->getId()]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save new shipping label. Error: %1', $e->getMessage()));
        }

        return $shippingLabel;
    }

    public function delete(ShippingLabelInterface $shippingLabel): bool
    {
        try {
            $this->resource->delete($shippingLabel);
            unset($this->shippingLabels[$shippingLabel->getLabelId()]);
        } catch (\Exception $e) {
            if ($shippingLabel->getLabelId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove shipping label with ID %1. Error: %2',
                        [$shippingLabel->getRuleId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove shippinh label. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        $shippingLabel = $this->getById($id);

        return $this->delete($shippingLabel);
    }

    public function getEmptyShippingLabelModel(): ShippingLabelInterface
    {
        return $this->shippingLabelFactory->create();
    }

    public function isLabelExistsForRequest(int $requestId): bool
    {
        /** @var ResourceModel\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect(ShippingLabelInterface::LABEL_ID)
            ->addFieldToFilter(ShippingLabelInterface::REQUEST_ID, $requestId);

        return (bool)$collection->getFirstItem()->getLabelId();
    }
}
