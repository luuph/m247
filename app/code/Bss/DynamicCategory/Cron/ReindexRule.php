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
use Bss\DynamicCategory\Model\Indexer\IndexBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Indexer\Model\Indexer\DependencyDecorator;

class ReindexRule
{
    /**
     * @var DynamicCategoryConfig
     */
    protected $dynamicCategoryConfig;

    /**
     * @var DependencyDecorator
     */
    protected $indexer;

    /**
     * @var IndexBuilder
     */
    protected $dynamicCategoryIndexer;

    /**
     * Constructor
     *
     * @param DynamicCategoryConfig $dynamicCategoryConfig
     * @param DependencyDecorator $indexer
     * @param IndexBuilder $dynamicCategoryIndexer
     */
    public function __construct(
        DynamicCategoryConfig $dynamicCategoryConfig,
        DependencyDecorator $indexer,
        IndexBuilder $dynamicCategoryIndexer
    ) {
        $this->dynamicCategoryConfig = $dynamicCategoryConfig;
        $this->indexer = $indexer;
        $this->dynamicCategoryIndexer = $dynamicCategoryIndexer;
    }

    /**
     * Check time and reindex rule
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute()
    {
        if ($this->dynamicCategoryConfig->isEnable() && $this->dynamicCategoryConfig->isAutoReindexProduct()) {
            if ($this->indexer->load(\Bss\DynamicCategory\Model\Indexer\IndexBuilder::INDEXER_ID)->isScheduled()) {
                $this->dynamicCategoryIndexer->reindexFull();
            }
        }
    }
}
