<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Cron;

use Amasty\RmaAutomation\Model\AutomationRule\AutomationRuleProcessor;

class RmaUpdate
{
    /**
     * @var AutomationRuleProcessor
     */
    private $ruleProcessor;

    /**
     * @param AutomationRuleProcessor $ruleProcessor
     */
    public function __construct(
        AutomationRuleProcessor $ruleProcessor
    ) {
        $this->ruleProcessor = $ruleProcessor;
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $this->ruleProcessor->processAllRma();
    }
}
