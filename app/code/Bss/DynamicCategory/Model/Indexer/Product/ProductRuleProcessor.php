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

namespace Bss\DynamicCategory\Model\Indexer\Product;

use Magento\Framework\Indexer\AbstractProcessor;

class ProductRuleProcessor extends AbstractProcessor
{
    public const INDEXER_ID = 'bss_dynamic_category';

    /**
     * Run Row reindex
     *
     * @param int $id
     * @param bool $forceReindex
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function reindexRow($id, $forceReindex = false)
    {
        if (!$forceReindex && $this->isIndexerScheduled()) {
            $this->getIndexer()->invalidate();
            return;
        }
        parent::reindexRow($id, $forceReindex);
    }

    /**
     * Run List reindex
     *
     * @param int[] $ids
     * @param bool $forceReindex
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function reindexList($ids, $forceReindex = false)
    {
        if (!$forceReindex && $this->isIndexerScheduled()) {
            $this->getIndexer()->invalidate();
        }
        parent::reindexList($ids, $forceReindex);
    }
}
