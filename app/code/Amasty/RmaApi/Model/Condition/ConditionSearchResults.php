<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Condition;

use Amasty\RmaApi\Api\Data\ConditionSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class ConditionSearchResults extends SearchResults implements ConditionSearchResultsInterface
{
}
