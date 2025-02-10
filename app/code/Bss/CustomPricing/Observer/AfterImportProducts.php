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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2024 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Bss\CustomPricing\Api\Data\ProductPriceInterface;

class AfterImportProducts implements ObserverInterface
{
    /**
     *
     * @var \Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor
     */
    protected $ruleProcessor;

    /**
     * @var ImportProduct
     */
    protected $import;

    /**
     * @var \Bss\CustomPricing\Helper\ProductSave
     */
    protected $helperSave;

    /**
     * @var ProductPriceRepositoryInterface
     */
    protected $productPriceRepository;

    /**
     * @var \Bss\CustomPricing\Helper\IndexHelper
     */
    protected $reindexHelper;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexer;

    /**
     * Construct
     *
     * @param \Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor $ruleProcessor
     * @param \Bss\CustomPricing\Helper\ProductSave $helperSave
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param \Bss\CustomPricing\Helper\IndexHelper $reindexHelper
     * @param ResourceConnection $resourceConnection
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\CustomPricing\Helper\Data $helper
     * @param \Magento\Framework\Indexer\IndexerInterface $indexer
     */
    public function __construct(
        \Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor $ruleProcessor,
        \Bss\CustomPricing\Helper\ProductSave $helperSave,
        ProductPriceRepositoryInterface $productPriceRepository,
        \Bss\CustomPricing\Helper\IndexHelper $reindexHelper,
        ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger,
        \Bss\CustomPricing\Helper\Data $helper,
        \Magento\Framework\Indexer\IndexerInterface $indexer
    ) {
        $this->ruleProcessor = $ruleProcessor;
        $this->helperSave = $helperSave;
        $this->productPriceRepository = $productPriceRepository;
        $this->reindexHelper = $reindexHelper;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->indexer = $indexer;
    }

    /**
     * Update bss price table apter import product
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->import = $observer->getEvent()->getAdapter();
        $bunch = $observer->getEvent()->getBunch();
        $priceRules = $this->helperSave->getPriceRules();
        if (!$bunch) {
            return;
        }
        $appliedIds = [];
        $conditionClean = [];
        $data = [];
        $productIds = [];
        foreach ($bunch as $product) {
            $newSku = $this->import->getNewSku($product[ImportProduct::COL_SKU]);
            $productId = $newSku['entity_id'];
            $productIds[] = (int)$productId;
        }
        $parentIdsMap = $this->getParentIds($productIds);
        foreach ($bunch as $product) {
            $newSku = $this->import->getNewSku($product[ImportProduct::COL_SKU]);
            $productId = $newSku['entity_id'];
            $parentIds = isset($parentIdsMap[$productId]) ? explode(',', $parentIdsMap[$productId]['parent_ids'] ?? '') : [];
            foreach ($priceRules->getItems() as $rule) {
                $isValidated = $rule->getConditions()->validateByEntityId($productId);
                $isValidated = $this->validateParentProduct($isValidated, $parentIds, $rule);
                if (!$isValidated) {
                    $conditionClean[] = sprintf(
                        '(rule_id = %d AND product_id = %d)',
                        (int)$rule->getId(),
                        (int)$productId
                    );
                }
                if ($isValidated) {
                        $customPrice = $this->helper->prepareCustomPrice(
                            $rule->getDefaultPriceMethod(),
                            $product['price'],
                            $rule->getDefaultPriceValue()
                        );
        
                        $data[] = [
                            ProductPriceInterface::RULE_ID => $rule->getId(),
                            ProductPriceInterface::PRODUCT_ID => $productId,
                            ProductPriceInterface::NAME => $product['name'],
                            ProductPriceInterface::TYPE_ID => $product['product_type'],
                            ProductPriceInterface::ORIGIN_PRICE => $product['price'],
                            ProductPriceInterface::PRODUCT_SKU => $product['sku'],
                            ProductPriceInterface::PRICE_METHOD => $rule->getDefaultPriceMethod(),
                            ProductPriceInterface::PRICE_VALUE => $rule->getDefaultPriceValue(),
                            ProductPriceInterface::CUSTOM_PRICE => $customPrice,
                        ];
                }
            }
        }
        if(!empty($conditionClean)) {
            $this->cleanProductPriceById($conditionClean);
            $this->cleanIndex($conditionClean);
        }
        $this->insertOrUpdateProductPrices($data);
    }

    /**
     * Clean bss_custom_pricing_index
     *
     * @param array $conditionCleanIndex
     * @return void
     */
    public function cleanIndex(array $conditionCleanIndex = [])
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName(
                \Bss\CustomPricing\Model\Indexer\IndexerAction::BSS_INDEX_TABLE_NAME
            );

            if (!empty($conditionCleanIndex)) {
                $whereClause = implode(' OR ', $conditionCleanIndex);
                $connection->delete($tableName, $whereClause);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Clean bss_product_price
     *
     * @param array|null $conditionClean
     * @return void
     */
    public function cleanProductPriceById($conditionClean = [])
    {
        if (empty($conditionClean)) {
            return [];
        }
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('bss_product_price');

            if ($conditionClean !== null) {
                if (!is_array($conditionClean)) {
                    $conditionClean = [$conditionClean];
                }
                $whereCondition = implode(' OR ', $conditionClean);
                $select = $connection->select()
                ->from($tableName, ['rule_id', 'product_id'])
                ->where($whereCondition);
                $matchedPairs = $connection->fetchAll($select);
                if (!empty($matchedPairs)) {
                    $connection->delete($tableName, $whereCondition);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Update new record into bss_product_price
     *
     * @param array $data
     * @return integer
     */
    public function insertOrUpdateProductPrices(array $data): int
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('bss_product_price');

            if (empty($data)) {
                return 0;
            }

            $columns = array_keys($data[0]);
            $connection->insertOnDuplicate($tableName, $data, $columns);

            $this->indexer->load(\Bss\CustomPricing\Model\Indexer\PriceRule::INDEX_ID);
            $this->indexer->invalidate();
            return count($data);

        } catch (\Exception $e) {
            $this->logger->critical($e);
            return -1;
        }
    }

    /**
     * Get parent ids of provided product id
     *
     * @param int $childId
     * @return array
     */
    protected function getParentIds(array $childIds): array
    {
        try {
            $conn = $this->resourceConnection->getConnection();
            $select = $conn->select()->from(
                ["rela" => $this->resourceConnection->getTableName("catalog_product_relation")],
                [
                    "child_id",
                    "parent_ids" => new \Zend_Db_Expr("GROUP_CONCAT(rela.parent_id)")
                ]
            )->joinInner(
                ['product' => $this->resourceConnection->getTableName("catalog_product_entity")],
                "rela.parent_id = product.entity_id",
                []
            )->where("rela.child_id IN (?)", $childIds)
              ->group("rela.child_id");
            
            $result = $conn->fetchAssoc($select);
    
            if (empty($result)) {
                return [];
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Is parent product validate
     *
     * To confirm this saved child product is validated by parent and keep in product price table
     *
     * @param bool $isValidated
     * @param array $parentIds
     * @param \Bss\CustomPricing\Api\Data\PriceRuleInterface $rule
     * @return bool
     */
    private function validateParentProduct(
        bool $isValidated,
        array $parentIds,
        \Bss\CustomPricing\Api\Data\PriceRuleInterface $rule
    ): bool {
        if (!$isValidated && !empty($parentIds)) {
            foreach ($parentIds as $parentId) {
                try {
                    $isValidated = $rule->getConditions()->validateByEntityId($parentId);
                    if ($isValidated) {
                        return true;
                    }
                } catch (\Exception $e) {
                    $isValidated = false;
                    $this->logger->critical($e);
                }
            }
        }
        return $isValidated;
    }
}
