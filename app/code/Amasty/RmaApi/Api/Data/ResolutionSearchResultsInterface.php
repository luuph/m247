<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ResolutionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RMA resolutions list.
     *
     * @return \Amasty\Rma\Api\Data\ResolutionInterface[]
     */
    public function getItems();

    /**
     * Set RMA resolutions list
     *
     * @param \Amasty\Rma\Api\Data\ResolutionInterface[] $items
     * @return \Amasty\RmaApi\Api\Data\ResolutionSearchResultsInterface
     */
    public function setItems(array $items);
}
