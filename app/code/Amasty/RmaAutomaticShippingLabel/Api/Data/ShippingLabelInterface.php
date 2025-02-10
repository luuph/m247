<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Api\Data;

/**
 * @api
 */
interface ShippingLabelInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public const LABEL_ID = 'label_id';
    public const REQUEST_ID = 'request_id';
    public const SHIPPING_LABEL = 'shipping_label';
    public const PACKAGES = 'packages';
    public const CARRIER_TITLE = 'carrier_title';
    public const CARRIER_CODE = 'carrier_code';
    public const CARRIER_METHOD = 'carrier_method';
    public const PRICE = 'price';

    /**
     * @return int
     */
    public function getLabelId(): int;

    /**
     * @param int $id
     * @return \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface
     */
    public function setLabelId(int $id): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    /**
     * @return int
     */
    public function getRequestId(): int;

    /**
     * @param int $id
     * @return \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface
     */
    public function setRequestId(int $id): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    /**
     * @return string
     */
    public function getShippingLabel(): string;

    /**
     * @param string $label
     * @return \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface
     */
    public function setShippingLabel(string $label): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    /**
     * @return mixed[]
     */
    public function getPackages(): array;

    /**
     * @param mixed[] $packages
     * @return ShippingLabelInterface
     */
    public function setPackages(array $packages): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    /**
     * @return string|null
     */
    public function getCarrierTitle();

    /**
     * @param string $title
     * @return \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface
     */
    public function setCarrierTitle(string $title): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    /**
     * @return string|null
     */
    public function getCarrierCode();

    /**
     * @param string $code
     * @return \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface
     */
    public function setCarrierCode(string $code): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    /**
     * @return string|null
     */
    public function getCarrierMethod();

    /**
     * @param string $method
     * @return \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface
     */
    public function setCarrierMethod(string $method): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    /**
     * @return float
     */
    public function getPrice(): float;

    /**
     * @param float $price
     * @return \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface
     */
    public function setPrice(float $price): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;

    /**
     * @return \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * @param \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelExtensionInterface $extensionAttributes
     * @return \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface
     */
    public function setExtensionAttributes(
        \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelExtensionInterface $extensionAttributes
    ): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;
}
