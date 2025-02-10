<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Controller\Adminhtml\AutomationRule;

use Amasty\RmaAutomation\Controller\Adminhtml\AbstractAutomationRule;
use Magento\Framework\Controller\ResultFactory;

class Create extends AbstractAutomationRule
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $result->forward('edit');
    }
}
