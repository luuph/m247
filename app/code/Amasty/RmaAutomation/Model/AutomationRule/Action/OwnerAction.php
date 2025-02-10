<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaAutomation\Model\AutomationRule\Action;

use Amasty\RmaAutomation\Api\PerformActionInterface;

class OwnerAction implements PerformActionInterface
{
    /**
     * @var \Amasty\Rma\Model\OptionSource\Manager
     */
    private $managers;

    /**
     * Action value
     *
     * @var string
     */
    private $value;

    /**
     * Action additional data
     *
     * @var array
     */
    private $additionalData;

    /**
     * @param \Amasty\Rma\Model\OptionSource\Manager $managers
     * @param string $value
     * @param array $additionalData
     */
    public function __construct(
        \Amasty\Rma\Model\OptionSource\Manager $managers,
        $value,
        $additionalData = []
    ) {
        $this->managers = $managers;
        $this->value = $value;
        $this->additionalData = $additionalData;
    }

    /**
     * @inheritdoc
     */
    public function perform(\Amasty\Rma\Api\Data\RequestInterface $request)
    {
        $managers = $this->managers->toArray();

        if (isset($managers[$this->value])) {
            $request->setManagerId($this->value);
        }
    }
}
