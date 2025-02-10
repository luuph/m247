<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\OptionSource;

use Amasty\Rma\Api\Data\ConditionInterface;
use Amasty\Rma\Model\OptionSource\Status;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ItemsConditions
 */
class Condition implements OptionSourceInterface
{
    /**
     * @var \Amasty\Rma\Model\Reason\ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\Rma\Model\Condition\ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];

        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => __($label)];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];

        $conditionCollection = $this->collectionFactory->create()
            ->addNotDeletedFilter()
            ->addFieldToFilter(ConditionInterface::STATUS, Status::ENABLED)
            ->addFieldToSelect([ConditionInterface::CONDITION_ID, ConditionInterface::TITLE])
            ->setOrder(ConditionInterface::POSITION, \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($conditionCollection->getData() as $condition) {
            $result[$condition[ConditionInterface::CONDITION_ID]] = $condition[ConditionInterface::TITLE];
        }

        return $result;
    }
}
