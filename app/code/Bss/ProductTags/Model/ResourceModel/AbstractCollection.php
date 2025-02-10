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
 * @category  BSS
 * @package   Bss_ProductTags
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductTags\Model\ResourceModel;

abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AbstractCollection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param string $columnSet
     */
    public function performAfterLoad($tableName, $columnName, $columnSet)
    {
        $vallues = $this->getColumnValues($columnName);
        if (!empty($vallues)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['tag_entity_store' => $this->getTable($tableName)])
                ->where('tag_entity_store.' . $columnName . ' IN (?)', $vallues);
            $results = $connection->fetchAssoc($select);

            if ($results) {
                foreach ($this->_items as $item) {
                    $entityId = $item->getData($columnName);
                    $storesData = [];
                    foreach ($results as $result) {
                        if (isset($result[$columnName]) && $entityId == $result[$columnName]) {
                            $storesData[] = $result[$columnSet];
                        }
                    }
                    $item->setData($columnSet, $storesData);
                }
            }
        }
    }

    /**
     * @param string $tableName
     * @param string $columnName
     */
    public function joinStoreRelationTable($tableName, $columnName)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()
                ->joinLeft(
                    ['store_table' => $this->getTable($tableName)],
                    'main_table.' . $columnName . ' = store_table.' . $columnName,
                    []
                );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

        return $countSelect;
    }
}
