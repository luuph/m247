<?php
/**
 * FME-Restrict-Payment-Method PaymentMethod ResourceModel.
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @author    Adeel Anjum
 * @copyright Copyright (c) 2018 United Sol Private Limited (https://unitedsol.net)
 */
namespace FME\RestrictPaymentMethod\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Json\Helper\Data;

/**
 * FME RestrictPaymentMethod mysql resource.
 */
class PaymentMethod extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    protected $jsonHelper;

    /**
     * Construct.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime       $date
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        Context $context,
        DateTime $date,
        Data $jsonHelper,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
        $this->jsonHelper = $jsonHelper;
    }



    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('fme_paymentmethod', 'rule_id');
    }
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            if(!empty( $object->getApplyCouponId())) {
                $object->setData('apply_coupon_id', explode(",", $object->getApplyCouponId()));
            }
            if(!empty($object->getData('noapply_coupon_id'))) {
                $object->setData('noapply_coupon_id', explode(",", $object->getData('noapply_coupon_id')));
            }
            if(!empty($object->getApplyCatalogRule())) {
                $object->setData('apply_catalog_rule', explode(",", $object->getApplyCatalogRule()));
            }
            if(!empty($object->getData('noapply_catalog_rule'))) {
                $object->setData('noapply_catalog_rule', explode(",", $object->getData('noapply_catalog_rule')));
            }
            $customerids = $this->lookupCustomerIds($object->getId());
            $customerids=$this->prepareCustomerJson($customerids);
            $object->setData('customers', $customerids);
            $customerGroups = $this->lookupCustomerGroupIds($object->getId());
            //echo'<pre>';print_r($customerGroups);

            $object->setData('customer_group_ids', $customerGroups);
            $object=$this->prepareDays($object);
            $PaymentIds = $this->lookupPaymentIds($object->getId());
            //print_r( $PaymentIds);
            $object->setPayment($PaymentIds);
            $countryIds = $this->lookupCountryIds($object->getId());
            if(!empty($countryIds)){
                $object->setData('restrictoptions',0);
                $object->setCountry($countryIds);
            }
            $regionIds = (array)$this->lookupRegionIds($object->getId());
             if(!empty($regionIds)){
                $object->setData('restrictoptions',1);
                $object->setData('region_id',$regionIds);
            }
        }
        return parent::_afterLoad($object);
    }
    protected function _afterSave(
        \Magento\Framework\Model\AbstractModel $object
    ) {
        $this->saveStores($object);
        $this->saveCustomerGroups($object);
        $this->saveCustomers($object);
        $this->savePaymentMethods($object);
        $this->savePaymentDays($object);
        $this->saveCountry($object);
        $this->saveRegion($object);
        return parent::_afterSave($object);
    }

    protected function saveStores($object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table = $this->getTable('fme_paymentmethod_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = ['rule_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['rule_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
    }
    protected function saveCustomerGroups($object)
    {
        $oldCustomerGroups = $this->lookupCustomerGroupIds($object->getId());
        $newCustomerGroups = (array)$object->getCustomerGroupIds();
        if (empty($newCustomerGroups)) {
            $newCustomerGroups = (array)$object->getCustomerGroupIds();
        }
        $table = $this->getTable('fme_paymentmethod_customer_group');
        $insert = array_diff($newCustomerGroups, $oldCustomerGroups);
        $delete = array_diff($oldCustomerGroups, $newCustomerGroups);
        if ($delete) {
            $where = ['rule_id = ?' => (int)$object->getId(), 'customer_group IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['rule_id' => (int)$object->getId(), 'customer_group' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
    }
    protected function saveCustomers($object)
    {
        $oldCustomer = $this->lookupCustomerIds($object->getId());
        $newcustomerIdInsert=[];
        $newCustomer = $object->getCustomers();
        $tableCustId = $this->getTable('fme_paymentmethod_customer');
        if (!empty($newCustomer) && is_array($newCustomer)) {
            $newcustomerIdInsert=$newCustomer;
            if (!empty($oldCustomer)) {
                $newcustomerIdInsert = array_diff($newCustomer, $oldCustomer);
                $oldCustomerIdDelete = array_diff($oldCustomer, $newCustomer);
                if ($oldCustomerIdDelete) {
                    $where = ['rule_id = ?' => (int) $object->getId(), 'entity_id IN (?)' => $oldCustomerIdDelete];
                    $this->getConnection()->delete($tableCustId, $where);
                }
            }
            if ($newcustomerIdInsert) {
                $data = [];
                foreach ($newcustomerIdInsert as $customerId) {
                    $data[] = ['rule_id' => (int) $object->getId(), 'entity_id' => (int) $customerId];
                }
                $this->getConnection()->insertMultiple($tableCustId, $data);
            }
        } else {
            $where = ['rule_id = ?' => (int) $object->getId()];
            $this->getConnection()->delete($tableCustId, $where);
        }
    }
    protected function savePaymentMethods($object)
    {
        $oldPaymentIds = $this->lookupPaymentIds($object->getId());
        $newPaymentIds = (array)$object->getPayment();
        if (empty($newPaymentIds)) {
            $newPaymentIds = (array)$object->getPayment();
        }
      
        $table = $this->getTable('fme_paymentmethod_restriction');
        $insert = array_diff($newPaymentIds, $oldPaymentIds);
        $delete = array_diff($oldPaymentIds, $newPaymentIds);
        if ($delete) {
            $where = ['rule_id = ?' => (int)$object->getId(), 'payment_code IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $paymentId) {
                $data[] = ['rule_id' => (int)$object->getId(), 'payment_code' => $paymentId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
    }
    protected function saveCountry($object)
    {
        $oldPaymentIds = $this->lookupCountryIds($object->getId());
        $newPaymentIds = (array)$object->getCountry();
        if (empty($newPaymentIds)) {
            $newPaymentIds = (array)$object->getCountry();
        }
      
        $table = $this->getTable('fme_paymentmethod_country');
        $insert = array_diff($newPaymentIds, $oldPaymentIds);
        $delete = array_diff($oldPaymentIds, $newPaymentIds);
        if ($delete) {
            $where = ['rule_id = ?' => (int)$object->getId(), 'country_code IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $paymentId) {
                $data[] = ['rule_id' => (int)$object->getId(), 'country_code' => $paymentId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
    }
    
    protected function savePaymentDays($object)
    {
        $oldPaymentdays = $this->lookupPaymentDays($object->getId());
        $table = $this->getTable('fme_paymentmethod_days');
        $newPaymentdays = (array)$object->getTiming();
        if (!empty($newPaymentdays)) {
            $newPaymentdays = (array)$object->getTiming();
        }
        $newdayIds = array_column($newPaymentdays, 'day_id');
        if ($oldPaymentdays) {
            $where = ['rule_id = ?' => (int)$object->getId(), 'day_id IN (?)' => $oldPaymentdays];
            $this->getConnection()->delete($table, $where);
        }
        if ($newdayIds) {
            $i=0;
            $data = [];
            foreach ($newdayIds as $paymentId) {
                $data[] = [
                    'rule_id' => (int)$object->getId(),
                    'day_id' => $newPaymentdays[$i]['day_id'],
                    'open_at'=>$newPaymentdays[$i]['open_at'],
                    'close_at'=>$newPaymentdays[$i]['close_at']
                ];
                $i++;
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        
    }
    protected function saveRegion($object)
    {
        $oldStores = $this->lookupRegionIds($object->getId());
        $newStores = (array)$object->getRegionId();
        if (empty($newStores)) {
            $newStores = (array)$object->getRegionId();
        }
        $table = $this->getTable('fme_paymentmethod_region');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = ['rule_id = ?' => (int)$object->getId(), 'region_id IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['rule_id' => (int)$object->getId(), 'region_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
    }
    public function lookupRegionIds($ruleid)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
        ->from($this->getTable('fme_paymentmethod_region'), 'region_id')
        ->where('rule_id = ?', (int)$ruleid);
        return $connection->fetchCol($select);
    }
    public function lookupStoreIds($ruleid)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
        ->from($this->getTable('fme_paymentmethod_store'), 'store_id')
        ->where('rule_id = ?', (int)$ruleid);
        return $connection->fetchCol($select);
    }
    public function lookupCustomerIds($ruleid)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
        ->from($this->getTable('fme_paymentmethod_customer'), 'entity_id')
        ->where('rule_id = ?', (int)$ruleid);
        return $connection->fetchCol($select);
    }
    public function lookupCustomerGroupIds($ruleid)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
        ->from($this->getTable('fme_paymentmethod_customer_group'), 'customer_group')
        ->where('rule_id = ?', (int)$ruleid);
        return $connection->fetchCol($select);
    }
    public function lookupPaymentIds($ruleid)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
        ->from($this->getTable('fme_paymentmethod_restriction'), 'payment_code')
        ->where('rule_id = ?', (int)$ruleid);
        return $connection->fetchCol($select);
    }
        public function lookupCountryIds($ruleid)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
        ->from($this->getTable('fme_paymentmethod_country'), 'country_code')
        ->where('rule_id = ?', (int)$ruleid);
        return $connection->fetchCol($select);
    }
    public function lookupPaymentDays($ruleid)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
        ->from($this->getTable('fme_paymentmethod_days'), 'day_id')
        ->where('rule_id = ?', (int)$ruleid);
        return $connection->fetchCol($select);
    }
    public function getPaymentDays($ruleid)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
        ->from($this->getTable('fme_paymentmethod_days'), ['day_id','open_at','close_at'])
        ->where('rule_id = ?', (int)$ruleid);
        return $connection->fetchAll($select);
    }
    public function prepareCustomerJson($customerId)
    {
        $data=[];
        foreach ($customerId as $value) {
            $data[$value]=$value;
        }
        return $this->jsonHelper->jsonEncode($data);
    }
    public function prepareDays($object)
    {
        $days=$this->getPaymentDays($object->getId());
        $data=[];
        $count=0;
        foreach ($days as $value) {
            $open=str_split($value['open_at'], 2);
            $close=str_split($value['close_at'], 2);
            $data[$count]['day']=$value['day_id'];
            $data[$count]['hopen']=$open[0];
            $data[$count]['mopen']=$open[1];
            $data[$count]['hclose']=$close[0];
            $data[$count]['mclose']=$close[1];
            $count++;
        }
        if (!empty($data)) {
            $object->setData('assign_timing', $data);
        }
        return $object;
    }
}
