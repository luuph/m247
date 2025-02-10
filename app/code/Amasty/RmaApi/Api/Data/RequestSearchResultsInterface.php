<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface RequestSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RMA requests list.
     *
     * @return \Amasty\Rma\Api\Data\RequestInterface[]
     */
    public function getItems();

    /**
     * Set RMA requests list.
     *
     * @param \Amasty\Rma\Api\Data\RequestInterface[] $items
     * @return \Amasty\RmaApi\Api\Data\RequestSearchResultsInterface
     */
    public function setItems(array $items);
}
