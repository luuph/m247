<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Model\AutomationRule\ResourceModel;

use Amasty\RmaAutomation\Api\Data\RuleActionInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RuleAction extends AbstractDb
{
    public const TABLE_NAME = 'amasty_rma_automation_rule_action';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, RuleActionInterface::ACTION_ID);
    }
}
