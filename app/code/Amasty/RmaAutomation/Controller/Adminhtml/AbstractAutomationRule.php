<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Controller\Adminhtml;

/**
 * Class AbstractAutomationRule
 */
abstract class AbstractAutomationRule extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_RmaAutomation::automation_rules';
}
