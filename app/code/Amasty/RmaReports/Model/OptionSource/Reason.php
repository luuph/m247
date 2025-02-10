<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\OptionSource;

use Amasty\Rma\Api\Data\ReasonInterface;
use Amasty\Rma\Model\OptionSource\Status;
use Amasty\Rma\Model\Reason\ResourceModel\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class Reason implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
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

        $reasonCollection = $this->collectionFactory->create()
            ->addNotDeletedFilter()
            ->addFieldToFilter(ReasonInterface::STATUS, Status::ENABLED)
            ->addFieldToSelect([ReasonInterface::REASON_ID, ReasonInterface::TITLE])
            ->setOrder(ReasonInterface::POSITION, \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($reasonCollection->getData() as $reason) {
            $result[$reason[ReasonInterface::REASON_ID]] = $reason[ReasonInterface::TITLE];
        }

        return $result;
    }
}
