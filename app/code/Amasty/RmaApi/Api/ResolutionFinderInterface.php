<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api;

use Amasty\RmaApi\Api\Data\ResolutionSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ResolutionFinderInterface
{
    /**
     * Retrieve RMA statuses.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\RmaApi\Api\Data\ResolutionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ResolutionSearchResultsInterface;
}
