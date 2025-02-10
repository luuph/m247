<?php
/**
 * FME Restrict Payment Method PaymentMethod Collection.
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @author    Adeel Anjum
 * @copyright Copyright (c) 2018 United Sol Private Limited (https://unitedsol.net)
 */
namespace FME\RestrictPaymentMethod\Model\ResourceModel\PaymentMethod;

class Collection extends \FME\RestrictPaymentMethod\Model\ResourceModel\AbstractCollection
{
    protected $_previewFlag;
    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'FME\RestrictPaymentMethod\Model\PaymentMethod',
            'FME\RestrictPaymentMethod\Model\ResourceModel\PaymentMethod'
        );
    }
    public function addStore()
    {
           $this->getSelect()
                ->join(
                    ['store_table' => $this->getTable('fme_paymentmethod_store')],
                    'main_table.rule_id = store_table.rule_id',
                    ['store_id' => new \Zend_Db_Expr('group_concat(DISTINCT `store_table`.store_id ORDER BY `store_table`.store_id ASC SEPARATOR ",")')]
                );

        return $this;
    }
    public function addCustomerGroups()
    {
        $this->getSelect()
            ->join(
                ['customergroup_table' => $this->getTable('fme_paymentmethod_customer_group')],
                'main_table.rule_id = customergroup_table.rule_id',
                ['customer_group_id' => new \Zend_Db_Expr('group_concat(DISTINCT `customergroup_table`.customer_group ORDER BY `customergroup_table`.customer_group ASC SEPARATOR ",")')]
            );

        return $this;
    }
    public function addCustomer()
    {
        $this->getSelect()
            ->join(
                ['customer_table' => $this->getTable('fme_paymentmethod_customer')],
                'main_table.rule_id = customer_table.rule_id',
                ['entity_id' => new \Zend_Db_Expr('group_concat(DISTINCT `customer_table`.entity_id ORDER BY `customer_table`.entity_id ASC SEPARATOR ",")')]
            );

        return $this;
    }
    public function addPaymentMethods()
    {
        $this->getSelect()
            ->join(
                ['payment_table' => $this->getTable('fme_paymentmethod_restriction')],
                'main_table.rule_id = payment_table.rule_id',
                ['payment_code' => new \Zend_Db_Expr('group_concat(DISTINCT `payment_table`.payment_code ORDER BY `payment_table`.payment_id ASC SEPARATOR ",")')]
            );


        return $this;
    }


    public function addPaymentMethodsFilter($paymentCode)
    {
        $this->getSelect()
               ->join(
                   ['payment_table' => $this->getTable('fme_paymentmethod_restriction')],
                   'main_table.rule_id = payment_table.rule_id',
                   []
               )->where('payment_table.payment_code = (?)', $paymentCode);

        return $this;
    }
    public function addStatusFilter($isActive = 1)
    {

        $this->getSelect()
                ->where('main_table.status = ? ', $isActive);

        return $this;
    }
    public function addCustomerGroupFilter($customerGroupId)
    {
        $this->getSelect()
            ->join(
                ['fme_customer_group' => $this->getTable('fme_paymentmethod_customer_group')],
                'main_table.rule_id = fme_customer_group.rule_id',
                []
            )->where('fme_customer_group.customer_group = (?)', $customerGroupId);

        return $this;
    }
    public function addCustomerIdFilter($customerId)
    {
        $this->getSelect()
        ->join(
            ['fme_customer_table' => $this->getTable('fme_paymentmethod_customer')],
            'main_table. rule_id = fme_customer_table.rule_id',
            []
        )->where('fme_customer_table.entity_id = (?)', $customerId);

        return $this;
    }

    public function addPriorityFilter()
    {

        $this->getSelect()
                ->order(new \Zend_Db_Expr("CASE WHEN `priority` = '0' THEN 9999 ELSE `priority` END"));

        return $this;
    }
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    protected function _afterLoad()
    {
        $this->performAfterLoad('fme_paymentmethod_store', 'rule_id');
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('fme_paymentmethod_store', 'rule_id');
    }
}
