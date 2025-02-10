<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ConditionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RMA item conditions list.
     *
     * @return \Amasty\Rma\Api\Data\ConditionInterface[]
     */
    public function getItems();

    /**
     * Set RMA item conditions list
     *
     * @param \Amasty\Rma\Api\Data\ConditionInterface[] $items
     * @return \Amasty\RmaApi\Api\Data\ConditionSearchResultsInterface
     */
    public function setItems(array $items);
}
