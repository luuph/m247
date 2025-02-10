<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Model\AutomationRule\ResourceModel;

use Amasty\RmaAutomation\Api\Data\AutomationRuleInterface;
use Magento\Rule\Model\ResourceModel\AbstractResource;

class AutomationRule extends AbstractResource
{
    public const TABLE_NAME = 'amasty_rma_automation_rules';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, AutomationRuleInterface::RULE_ID);
    }
}
