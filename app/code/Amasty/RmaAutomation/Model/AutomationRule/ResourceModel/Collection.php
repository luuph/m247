<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Model\AutomationRule\ResourceModel;

use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public const APPLY_FOR_FIELD = 'apply_for';

    protected function _construct()
    {
        $this->_init(
            \Amasty\RmaAutomation\Model\AutomationRule\AutomationRule::class,
            \Amasty\RmaAutomation\Model\AutomationRule\ResourceModel\AutomationRule::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @return \Zend_Db_Expr
     */
    public function getApplyToExpression()
    {
        return new \Zend_Db_Expr(
            "CASE WHEN apply_for_new = 0 AND apply_for_existing = 0 THEN 0"
            . " WHEN apply_for_new = 1 AND apply_for_existing = 0 THEN 1"
            . " WHEN apply_for_new = 0 AND apply_for_existing = 1 THEN 2"
            . " WHEN apply_for_new = 1 AND apply_for_existing = 1 THEN 3 END"
        );
    }

    /**
     * @return $this
     */
    public function addApplyToColumn()
    {
        $this->getSelect()->columns(['apply_for' => $this->getApplyToExpression()]);

        return $this;
    }
}
