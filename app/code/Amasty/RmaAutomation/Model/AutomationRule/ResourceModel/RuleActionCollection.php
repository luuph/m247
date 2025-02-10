<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Model\AutomationRule\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class RuleActionCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\RmaAutomation\Model\AutomationRule\RuleAction::class,
            \Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\RuleAction::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
