<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Observer\Rma;

use Amasty\RmaAutomation\Model\AutomationRule\AutomationRuleProcessor;
use Magento\Framework\Event\ObserverInterface;

class RmaChanged implements ObserverInterface
{
    /**
     * @var AutomationRuleProcessor
     */
    private $ruleProcessor;

    public function __construct(
        AutomationRuleProcessor $ruleProcessor
    ) {
        $this->ruleProcessor = $ruleProcessor;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Amasty\Rma\Api\Data\RequestInterface $request */
        $request = $observer->getRequest();

        if ($request) {
            $this->ruleProcessor->processRma($request, AutomationRuleProcessor::PROCESS_EXISTING);
        }
    }
}
