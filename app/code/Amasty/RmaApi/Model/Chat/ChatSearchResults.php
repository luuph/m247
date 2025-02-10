<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package RMA API for Magento 2 (System)
 */

namespace Amasty\RmaApi\Model\Chat;

use Amasty\RmaApi\Api\Data\ChatSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class ChatSearchResults extends SearchResults implements ChatSearchResultsInterface
{
}
