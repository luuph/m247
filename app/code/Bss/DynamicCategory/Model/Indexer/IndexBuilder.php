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

namespace Bss\DynamicCategory\Model\Indexer;

use Bss\DynamicCategory\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Bss\DynamicCategory\Model\Rule;
use Bss\DynamicCategory\Model\Config as DynamicCategoryConfig;
use Bss\DynamicCategory\Model\RuleRepository;
use Magento\Framework\DataObject;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

class IndexBuilder
{
    public const INDEXER_ID = 'bss_dynamic_category';

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var array
     */
    protected $loadedProducts;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var DynamicCategoryConfig
     */
    protected $dynamicCategoryConfig;

    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * Initialize builder
     *
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     * @param ProductFactory $productFactory
     * @param IndexerRegistry $indexerRegistry
     * @param DynamicCategoryConfig $dynamicCategoryConfig
     * @param RuleRepository $ruleRepository
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        ResourceConnection $resource,
        LoggerInterface $logger,
        ProductFactory $productFactory,
        IndexerRegistry $indexerRegistry,
        DynamicCategoryConfig $dynamicCategoryConfig,
        RuleRepository $ruleRepository
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->dynamicCategoryConfig = $dynamicCategoryConfig;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * Reindex by id
     *
     * @param int $id
     * @return void
     * @throws LocalizedException
     */
    public function reindexById($id)
    {
        $this->reindexByIds([$id]);
    }

    /**
     * Reindex by ids
     *
     * @param int[] $ids
     * @throws LocalizedException
     * @return void
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->doReindexByIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new LocalizedException(
                __("Bss dynamic category rule indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * Reindex by ids
     *
     * @param int[] $ids
     * @return void
     */
    protected function doReindexByIds($ids)
    {
        foreach ($this->getAllRules() as $rule) {
            foreach ($ids as $productId) {
                $this->applyRule($rule, $this->getProduct($productId));
                $this->productCategoryReindexRow($productId);
                if ($this->dynamicCategoryConfig->isEnableReindexLogging()) {
                    $this->insertReindexLogging($rule);
                }
            }
        }
    }

    /**
     * Reindex product categories by productId
     *
     * @param int $productId
     * @return void
     */
    protected function productCategoryReindexRow($productId)
    {
        $productCategoryIndexer = $this->indexerRegistry->get(ProductCategoryIndexer::INDEXER_ID);
        $productCategoryIndexer->reindexRow($productId);
    }

    /**
     * Reindex category products by productId
     *
     * @param int $categoryId
     * @return void
     */
    protected function categoryProductReindexRow($categoryId)
    {
        $categoryProductIndexer = $this->indexerRegistry->get(CategoryProductIndexer::INDEXER_ID);
        $categoryProductIndexer->reindexRow($categoryId);
    }

    /**
     * Full reindex
     *
     * @throws LocalizedException
     * @return void
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            $this->critical($e);
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    protected function doReindexFull()
    {
        foreach ($this->getAllRules() as $rule) {
            $this->updateRuleProductData($rule);
            $this->categoryProductReindexRow($rule->getId());
            if ($this->dynamicCategoryConfig->isEnableReindexLogging()) {
                $this->insertReindexLogging($rule);
            }
        }
    }

    /**
     * Clean by product ids
     *
     * @param integer $categoryId
     * @param array $productIds
     * @return void
     */
    protected function cleanByIds($categoryId, $productIds)
    {
        $this->connection->delete(
            $this->getTable('catalog_category_product'),
            ['category_id = ?' => $categoryId, 'product_id IN (?)' => $productIds]
        );
    }

    /**
     * Insert products
     *
     * @param integer $categoryId
     * @param array $productIds
     * @return void
     */
    protected function insertMultiple($categoryId, $productIds)
    {
        $data = [];
        foreach ($productIds as $productId => $position) {
            $data[] = [
                'category_id' => $categoryId,
                'product_id' => $productId,
                'position' => $position
            ];
        }
        $this->connection->insertMultiple(
            $this->getTable('catalog_category_product'),
            $data
        );
    }

    /**
     * Apply rule
     *
     * @param Rule $rule
     * @param DataObject $product
     * @return $this
     */
    protected function applyRule(Rule $rule, $product)
    {
        $ruleId = $rule->getId();
        $productId = $product->getId();

        if ($rule->validate($product)) {
            if (!$this->checkPostedProduct($ruleId, $productId)) {
                $this->insertMultiple($ruleId, [$productId => '1']);
            }
            return $this;
        }
        $this->cleanByIds($ruleId, [$productId]);
        return $this;
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

    /**
     * Check posted product
     *
     * @param string $categoryId
     * @param string $productId
     * @return bool
     */
    protected function checkPostedProduct($categoryId, $productId)
    {
        $select = $this->connection
            ->select()
            ->from($this->getTable('catalog_category_product'), [new Zend_Db_Expr('COUNT(*)')])
            ->where('category_id = ?', $categoryId)
            ->where('product_id = ?', $productId);

        return 0 < $this->connection->fetchOne($select);
    }

    /**
     * Retrieve posted products
     *
     * @param string $categoryId
     * @return array
     */
    protected function getPostedProductData($categoryId)
    {
        $select = $this->connection
            ->select()
            ->from($this->getTable('catalog_category_product'), ['product_id', 'position'])
            ->where('category_id = ?', $categoryId);

        return $this->connection->fetchPairs($select);
    }

    /**
     * Update posted products
     *
     * @param Rule $rule
     * @return $this
     */
    protected function updateRuleProductData(Rule $rule)
    {
        $postedProducts = $this->getPostedProductData($rule->getId()) ?: [];
        $matchingProducts = $rule->getMatchingProductIds();

        $deleteIds = array_diff_key($postedProducts, $matchingProducts);
        $insertIds = array_diff_key($matchingProducts, $postedProducts);

        if (0 < count($deleteIds)) {
            $this->cleanByIds($rule->getId(), array_keys($deleteIds));
        }

        if (0 < count($insertIds)) {
            $this->insertMultiple($rule->getId(), $insertIds);
        }
        return $this;
    }

    /**
     * Retrieve active rules
     *
     * @return \Bss\DynamicCategory\Model\ResourceModel\Rule\Collection
     */
    protected function getAllRules()
    {
        return $this->ruleCollectionFactory->create();
    }

    /**
     * Retrieve product
     *
     * @param int $productId
     * @return DataObject
     */
    protected function getProduct($productId)
    {
        if (!isset($this->loadedProducts[$productId])) {
            $this->loadedProducts[$productId] = $this->productFactory->create()
                ->load($productId);
        }
        return $this->loadedProducts[$productId];
    }

    /**
     * Add critical message
     *
     * @param \Exception $e
     * @return void
     */
    protected function critical($e)
    {
        $this->logger->critical($e);
    }

    /**
     * Add notice message
     *
     * @param \Exception $e
     * @return void
     */
    protected function notice($e)
    {
        $this->logger->notice($e);
    }

    /**
     * Insert data to reindex logging
     *
     * @param Rule $rule
     * @return void
     */
    public function insertReindexLogging($rule)
    {
        $matchingProducts = array_keys($rule->getMatchingProductIds());
        $productIds = implode(', ', $matchingProducts);
        $data = [];
        try {
            $ruleData = $this->ruleRepository->get($rule->getRuleId());
            $data[] = [
                'cat_id' => $rule->getRuleId(),
                'conditions_serialized' => $ruleData->getRuleCondition(),
                'product_ids' => $productIds
            ];
        } catch (\Exception $e) {
            $this->notice($e);
        }
        $this->connection->insertMultiple(
            $this->getTable('bss_dynamic_category_logging'),
            $data
        );
    }
}
