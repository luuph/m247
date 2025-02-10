<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api;

use Amasty\RmaApi\Api\Data\ChatSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ChatMessageFinderInterface
{
    /**
     * Find RMA chat messages.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\RmaApi\Api\Data\ChatSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ChatSearchResultsInterface;
}
