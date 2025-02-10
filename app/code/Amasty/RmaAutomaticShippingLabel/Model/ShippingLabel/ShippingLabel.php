<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel;

use Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;
use Magento\Framework\Model\AbstractModel;

class ShippingLabel extends AbstractModel implements ShippingLabelInterface
{
    public function _construct()
    {
        $this->_init(ResourceModel\ShippingLabel::class);
        $this->setIdFieldName(ShippingLabelInterface::LABEL_ID);
    }

    public function getLabelId(): int
    {
        return (int)$this->_getData(ShippingLabelInterface::LABEL_ID);
    }

    public function setLabelId(int $id): ShippingLabelInterface
    {
        return $this->setData(ShippingLabelInterface::LABEL_ID, $id);
    }

    public function getRequestId(): int
    {
        return (int)$this->_getData(ShippingLabelInterface::REQUEST_ID);
    }

    public function setRequestId(int $id): ShippingLabelInterface
    {
        return $this->setData(ShippingLabelInterface::REQUEST_ID, $id);
    }

    public function getShippingLabel(): string
    {
        return (string)$this->_getData(ShippingLabelInterface::SHIPPING_LABEL);
    }

    public function setShippingLabel(string $label): ShippingLabelInterface
    {
        return $this->setData(ShippingLabelInterface::SHIPPING_LABEL, $label);
    }

    public function getPackages(): array
    {
        return $this->_getData(ShippingLabelInterface::PACKAGES);
    }

    public function setPackages(array $packages): ShippingLabelInterface
    {
        return $this->setData(ShippingLabelInterface::PACKAGES, $packages);
    }

    public function getCarrierTitle()
    {
        return (string)$this->_getData(ShippingLabelInterface::CARRIER_TITLE);
    }

    public function setCarrierTitle(string $title): ShippingLabelInterface
    {
        return $this->setData(ShippingLabelInterface::CARRIER_TITLE, $title);
    }

    public function getCarrierCode()
    {
        return (string)$this->_getData(ShippingLabelInterface::CARRIER_CODE);
    }

    public function setCarrierCode(string $code): ShippingLabelInterface
    {
        return $this->setData(ShippingLabelInterface::CARRIER_CODE, $code);
    }

    public function getCarrierMethod()
    {
        return (string)$this->_getData(ShippingLabelInterface::CARRIER_METHOD);
    }

    public function setCarrierMethod(string $method): \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface
    {
        return $this->setData(ShippingLabelInterface::CARRIER_METHOD, $method);
    }

    public function getPrice(): float
    {
        return (float)$this->_getData(ShippingLabelInterface::PRICE);
    }

    public function setPrice(float $price): ShippingLabelInterface
    {
        return $this->setData(ShippingLabelInterface::PRICE, $price);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        \Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelExtensionInterface $extensionAttributes
    ): ShippingLabelInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
