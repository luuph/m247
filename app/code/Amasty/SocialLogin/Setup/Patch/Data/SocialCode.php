<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Social Login Base for Magento 2
 */

namespace Amasty\SocialLogin\Setup\Patch\Data;

use Amasty\SocialLogin\Model\ResourceModel\SocialList as SocialListResource;
use Amasty\SocialLogin\Model\SocialList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SocialCode implements DataPatchInterface
{
    /**
     * @var SocialList
     */
    private $socialList;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resource, SocialList $socialList)
    {
        $this->socialList = $socialList;
        $this->resource = $resource;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): void
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName(SocialListResource::MAIN_TABLE);
        $connection->insertArray(
            $tableName,
            [SocialListResource::KEY_CODE],
            array_keys($this->socialList->getList())
        );
    }
}
