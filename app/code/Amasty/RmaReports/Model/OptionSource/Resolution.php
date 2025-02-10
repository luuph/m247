<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for RMA (Add-On) for Magento 2
 */

namespace Amasty\RmaReports\Model\OptionSource;

use Amasty\Rma\Api\Data\ResolutionInterface;
use Amasty\Rma\Model\OptionSource\Status;
use Amasty\Rma\Model\Resolution\ResourceModel\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class Resolution implements OptionSourceInterface
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

        $resolutionCollection = $this->collectionFactory->create()
            ->addNotDeletedFilter()
            ->addFieldToFilter(ResolutionInterface::STATUS, Status::ENABLED)
            ->addFieldToSelect([ResolutionInterface::RESOLUTION_ID, ResolutionInterface::TITLE])
            ->setOrder(ResolutionInterface::POSITION, \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        foreach ($resolutionCollection->getData() as $resolution) {
            $result[$resolution[ResolutionInterface::RESOLUTION_ID]] = $resolution[ResolutionInterface::TITLE];
        }

        return $result;
    }
}
