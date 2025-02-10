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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rule\Model\ResourceModel\AbstractResource;

class Rule extends AbstractResource
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [];

    /**
     * @var array
     */
    protected $customerGroupIds = [];

    /**
     * @var array
     */
    protected $websiteIds = [];

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param string $connectionName
     * @param \Magento\Framework\DataObject|null $associatedEntityMapInstance
     * @param Json $serializer Optional parameter for backward compatibility
     * @param MetadataPool $metadataPool Optional parameter for backward compatibility
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        $connectionName = null,
        \Magento\Framework\DataObject $associatedEntityMapInstance = null,
        Json $serializer = null,
        MetadataPool $metadataPool = null
    ) {
        $this->string = $string;
        $associatedEntitiesMapInstance = $associatedEntityMapInstance ?: ObjectManager::getInstance()->get(
            \Magento\SalesRule\Model\ResourceModel\Rule\AssociatedEntityMap::class
        );
        $this->_associatedEntitiesMap = $associatedEntitiesMapInstance->getData();
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_reward_point_rule', 'rule_id');
    }

    /**
     * Load customer group id
     *
     * @param AbstractModel $object
     * @return AbstractModel
     */
    protected function loadCustomerGroupIds(AbstractModel $object)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('bss_reward_point_rule_customer_group'))
            ->where('rule_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['customer_group_id'];
            }
            $object->setData('customer_group_ids', $array);
        }

        return $object;
    }

    /**
     * Load website id
     *
     * @param AbstractModel $object
     * @return AbstractModel
     */
    protected function loadWebsiteIds(AbstractModel $object)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('bss_reward_point_rule_website'))
            ->where('rule_id = ?', $object->getId());
        if ($data = $this->getConnection()->fetchAll($select)) {
            $array = [];
            foreach ($data as $row) {
                $array[] = $row['website_id'];
            }
            $object->setData('website_ids', $array);
        }

        return $object;
    }

    /**
     * Save website id
     *
     * @param AbstractModel $object
     * @return void
     */
    protected function saveWebsiteIds($object)
    {
        $condition = $this->getConnection()->quoteInto('rule_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('bss_reward_point_rule_website'), $condition);
        foreach ((array) $object->getData('website_ids') as $id) {
            $objArray = [
                'rule_id' => $object->getId(),
                'website_id' => $id,
            ];
            $this->getConnection()->insert($this->getTable('bss_reward_point_rule_website'), $objArray);
        }
    }

    /**
     * Save customer group id
     *
     * @param \Bss\RewardPoint\Model\Rule $object
     * @return void
     */
    protected function saveCustomerGroupIds($object)
    {
        if (is_string($object->getData('customer_group_ids'))) {
            if ($object->getData('customer_group_ids') == null) {
                $object->setData('customer_group_ids', []);
            } else {
                $object->setData('customer_group_ids', explode(',', $object->getData('customer_group_ids')));
            }
        }
        $condition = $this->getConnection()->quoteInto('rule_id = ?', $object->getId());
        $this->getConnection()->delete($this->getTable('bss_reward_point_rule_customer_group'), $condition);
        foreach ((array) $object->getData('customer_group_ids') as $id) {
            $objArray = [
                'rule_id' => $object->getId(),
                'customer_group_id' => $id,
            ];
            $this->getConnection()->insert(
                $this->getTable('bss_reward_point_rule_customer_group'),
                $objArray
            );
        }
    }
    /**
     * Save rule labels for different store views
     *
     * @param int $ruleId
     * @param array $notes
     * @return $this
     * @throws \Exception
     */
    public function saveStoreNotes($ruleId, $notes)
    {
        $deleteByStoreIds = [];
        $table = $this->getTable('bss_reward_point_rule_note');
        $connection = $this->getConnection();

        $data = [];
        foreach ($notes as $storeId => $note) {
            $strlen_note = $this->string->strlen($note);
            if ($strlen_note) {
                $data[] = ['rule_id' => $ruleId, 'store_id' => $storeId, 'note' => $note];
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $connection->beginTransaction();
        try {
            if (!empty($data)) {
                $connection->insertOnDuplicate($table, $data, ['note']);
            }

            if (!empty($deleteByStoreIds)) {
                $connection->delete($table, ['rule_id=?' => $ruleId, 'store_id IN (?)' => $deleteByStoreIds]);
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        $connection->commit();

        return $this;
    }

    /**
     * Get all existing rule labels
     *
     * @param int $ruleId
     * @return array
     */
    public function getStoreNotes($ruleId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('bss_reward_point_rule_note'),
            ['store_id', 'note']
        )->where(
            'rule_id = :rule_id'
        );
        return $this->getConnection()->fetchPairs($select, [':rule_id' => $ruleId]);
    }

    /**
     * Get rule label by specific store id
     *
     * @param int $ruleId
     * @param int $storeId
     * @return string
     */
    public function getStoreNote($ruleId, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('bss_reward_point_rule_note'),
            'note'
        )->where(
            'rule_id = :rule_id'
        )->where(
            'store_id IN(0, :store_id)'
        )->order(
            'store_id DESC'
        );
        return $this->getConnection()->fetchOne($select, [':rule_id' => $ruleId, ':store_id' => $storeId]);
    }

    /**
     * Load
     *
     * @param AbstractModel $object
     * @return AbstractResource
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getIsMassDelete()) {
            $this->loadWebsiteIds($object);
            $this->loadCustomerGroupIds($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Save
     *
     * @param AbstractModel $object
     * @return AbstractResource
     * @throws \Exception
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getIsMassStatus()) {
            $this->saveWebsiteIds($object);
            $this->saveCustomerGroupIds($object);
            $this->saveStoreNotes($object->getId(), $object->getStoreNotes());
        }

        return parent::_afterSave($object);
    }
}
