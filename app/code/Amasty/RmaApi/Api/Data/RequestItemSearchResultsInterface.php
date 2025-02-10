<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface RequestItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RMA requests list.
     *
     * @return \Amasty\Rma\Api\Data\RequestItemInterface[]
     */
    public function getItems();

    /**
     * Set RMA requests list.
     *
     * @param \Amasty\Rma\Api\Data\RequestItemInterface[] $items
     * @return \Amasty\RmaApi\Api\Data\RequestItemSearchResultsInterface
     */
    public function setItems(array $items);
}
