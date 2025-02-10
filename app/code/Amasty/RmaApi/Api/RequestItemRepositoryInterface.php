<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api;

interface RequestItemRepositoryInterface
{
    /**
     * @param int $itemId
     *
     * @return \Amasty\Rma\Api\Data\RequestItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $itemId): \Amasty\Rma\Api\Data\RequestItemInterface;

    /**
     * @param int $itemId
     *
     * @return bool
     */
    public function deleteById(int $itemId): bool;

    /**
     * @param \Amasty\Rma\Api\Data\RequestItemInterface $requestItem
     *
     * @return bool
     */
    public function delete(\Amasty\Rma\Api\Data\RequestItemInterface $requestItem): bool;

    /**
     * @param \Amasty\Rma\Api\Data\RequestItemInterface $item
     *
     * @return \Amasty\Rma\Api\Data\RequestItemInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Rma\Api\Data\RequestItemInterface $item): \Amasty\Rma\Api\Data\RequestItemInterface;
}
