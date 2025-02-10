<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\ResourceModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\ShippingLabel::class,
            \Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\ResourceModel\ShippingLabel::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
