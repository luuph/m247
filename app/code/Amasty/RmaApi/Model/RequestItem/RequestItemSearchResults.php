<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\RequestItem;

use Amasty\RmaApi\Api\Data\RequestItemSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class RequestItemSearchResults extends SearchResults implements RequestItemSearchResultsInterface
{
}
