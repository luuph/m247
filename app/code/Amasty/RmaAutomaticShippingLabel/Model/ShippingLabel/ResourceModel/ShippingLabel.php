<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Shipping Labels for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomaticShippingLabel\Model\ShippingLabel\ResourceModel;

use Amasty\RmaAutomaticShippingLabel\Api\Data\ShippingLabelInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ShippingLabel extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_shipping_label';

    /**
     * @var array[]
     */
    protected $_serializableFields = [
        ShippingLabelInterface::PACKAGES => ['[]', []]
    ];

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ShippingLabelInterface::LABEL_ID);
    }
}
