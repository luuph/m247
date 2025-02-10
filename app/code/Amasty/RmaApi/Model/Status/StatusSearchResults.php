<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Status;

use Amasty\RmaApi\Api\Data\StatusSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class StatusSearchResults extends SearchResults implements StatusSearchResultsInterface
{
}
