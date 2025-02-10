<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Api\Data;

/**
 * Interface RuleActionInterface
 */
interface RuleActionInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ACTION_ID = 'action_id';
    public const RULE_ID = 'rule_id';
    public const TYPE = 'type';
    public const VALUE = 'value';
    public const ADDITIONAL_DATA = 'additional_data';
    /**#@-*/

    /**
     * @param int $id
     *
     * @return \Amasty\RmaAutomation\Api\Data\RuleActionInterface
     */
    public function setActionId($id);

    /**
     * @return int
     */
    public function getActionId();

    /**
     * @param int $id
     *
     * @return \Amasty\RmaAutomation\Api\Data\RuleActionInterface
     */
    public function setRuleId($id);

    /**
     * @return int
     */
    public function getRuleId();

    /**
     * @param string $type
     *
     * @return \Amasty\RmaAutomation\Api\Data\RuleActionInterface
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param int $value
     *
     * @return \Amasty\RmaAutomation\Api\Data\RuleActionInterface
     */
    public function setValue($value);

    /**
     * @return int
     */
    public function getValue();

    /**
     * @param string $data
     *
     * @return \Amasty\RmaAutomation\Api\Data\RuleActionInterface
     */
    public function setAdditionalData($data);

    /**
     * @return string
     */
    public function getAdditionalData();
}
