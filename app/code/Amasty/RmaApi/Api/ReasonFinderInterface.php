<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api;

use Amasty\RmaApi\Api\Data\ReasonSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ReasonFinderInterface
{
    /**
     * Retrieve RMA items conditions.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\RmaApi\Api\Data\ReasonSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ReasonSearchResultsInterface;
}
