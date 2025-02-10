<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Api;

interface ShippingLabelRepositoryInterface
{
    public function getById(int $id): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    public function getByRequestId(int $id): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    public function save(
        \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface $shippingLabel
    ): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    public function delete(\Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface $shippingLabel): bool;

    public function deleteById(int $id): bool;
}
