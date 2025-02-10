<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Api;

/**
 * Interface PerformActionInterface
 */
interface PerformActionInterface
{
    /**
     * Performs rule action
     *
     * @param \Amasty\Rma\Api\Data\RequestInterface $request
     */
    public function perform(\Amasty\Rma\Api\Data\RequestInterface $request);
}
