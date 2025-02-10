<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automation Rules for RMA (Add-On) for Magento 2
 */


namespace Amasty\RmaAutomation\Model\OptionSource;

use Magento\Email\Model\ResourceModel\Template\Collection;
use Magento\Framework\Data\OptionSourceInterface;

class EmailTemplates implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $templateCollection;

    public function __construct(
        Collection $templateCollection
    ) {
        $this->templateCollection = $templateCollection;
    }

    public function toOptionArray()
    {
        $items = $this->templateCollection->toOptionArray();

        return $items;
    }
}
