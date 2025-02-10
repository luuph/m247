<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api;

use Amasty\RmaApi\Api\Data\ConditionSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ConditionFinderInterface
{
    /**
     * Retrieve RMA items conditions.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\RmaApi\Api\Data\ConditionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ConditionSearchResultsInterface;
}
