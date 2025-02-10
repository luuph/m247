<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ChatSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RMA messages list.
     *
     * @return \Amasty\Rma\Api\Data\MessageInterface[]
     */
    public function getItems();

    /**
     * Set RMA messages list.
     *
     * @param \Amasty\Rma\Api\Data\MessageInterface[] $items
     * @return \Amasty\RmaApi\Api\Data\ChatSearchResultsInterface
     */
    public function setItems(array $items);
}
