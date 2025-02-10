<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface StatusSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RMA statuses list.
     *
     * @return \Amasty\Rma\Api\Data\StatusInterface[]
     */
    public function getItems();

    /**
     * Set RMA statuses list
     *
     * @param \Amasty\Rma\Api\Data\StatusInterface[] $items
     * @return \Amasty\RmaApi\Api\Data\StatusSearchResultsInterface
     */
    public function setItems(array $items);
}
