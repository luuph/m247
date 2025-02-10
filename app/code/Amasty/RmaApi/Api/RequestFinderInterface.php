<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Api;

use Amasty\RmaApi\Api\Data\RequestSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface RequestFinderInterface
{
    /**
     * Retrieve RMA requests.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\RmaApi\Api\Data\RequestSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): RequestSearchResultsInterface;
}
