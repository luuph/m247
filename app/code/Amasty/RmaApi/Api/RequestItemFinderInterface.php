<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api;

use Amasty\RmaApi\Api\Data\RequestItemSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface RequestItemFinderInterface
{
    /**
     * Find RMA chat messages.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\RmaApi\Api\Data\RequestItemSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): RequestItemSearchResultsInterface;
}
