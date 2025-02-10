<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_DynamicCategory
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Bss\DynamicCategory\Cron;

use Bss\DynamicCategory\Model\Config as DynamicCategoryConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class ReindexLogging
{
    /**
     * @var DynamicCategoryConfig
     */
    protected $dynamicCategoryConfig;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param DynamicCategoryConfig $dynamicCategoryConfig
     * @param AdapterInterface $connection
     * @param ResourceConnection $resource
     */
    public function __construct(
        DynamicCategoryConfig $dynamicCategoryConfig,
        AdapterInterface $connection,
        ResourceConnection $resource
    ) {
        $this->dynamicCategoryConfig = $dynamicCategoryConfig;
        $this->connection = $connection;
        $this->resource = $resource;
    }

    /**
     * Check time and reindex rule
     *
     * @return void
     */
    public function execute()
    {
        if ($this->dynamicCategoryConfig->isEnable() && $this->dynamicCategoryConfig->isEnableReindexLogging()) {
            $this->connection->truncateTable($this->getTable('bss_dynamic_category_logging'));
        }
    }

    /**
     * Retrieve table name
     *
     * @param string $tableName
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName);
    }
}
