<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

use Amasty\SocialLogin\Api\Data\SocialInterface;
use Amasty\SocialLogin\Model\ResourceModel\Social;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Social $columnFactory */
$resource = Bootstrap::getObjectManager()->get(Social::class);

$resource->getConnection()->delete($resource->getMainTable(), [SocialInterface::SOCIAL_ID . ' = ?' => '123']);
