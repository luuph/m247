<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Api\Data;

interface RuleInterface
{
    public const RULE_ID = 'rule_id';
    public const RULE_CONDITION = 'conditions_serialized';

    /**
     * Get rule_id
     *
     * @return int|null
     */
    public function getRuleId();

    /**
     * Set rule_id
     *
     * @param int $id
     * @return \Bss\DynamicCategory\Api\Data\RuleInterface
     */
    public function setRuleId($id);

    /**
     * Get rule conditions
     *
     * @return string|null
     */
    public function getRuleCondition();

    /**
     * Set rule conditions
     *
     * @param string $ruleCondition
     * @return \Bss\DynamicCategory\Api\Data\RuleInterface
     */
    public function setRuleCondition($ruleCondition);
}
