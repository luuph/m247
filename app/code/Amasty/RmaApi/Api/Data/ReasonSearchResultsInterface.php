<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ReasonSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RMA return reasons list.
     *
     * @return \Amasty\Rma\Api\Data\ReasonInterface[]
     */
    public function getItems();

    /**
     * Set RMA return reasons list.
     *
     * @param \Amasty\Rma\Api\Data\ReasonInterface[] $items
     * @return \Amasty\RmaApi\Api\Data\ReasonSearchResultsInterface
     */
    public function setItems(array $items);
}
