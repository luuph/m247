<?php
/**
 * FME Extensions
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the fmeextensions.com license that is
 * available through the world-wide-web at this URL:
 * https://www.fmeextensions.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  FME
 * @package   FME_RestrictPaymentMethod
 * @copyright Copyright (c) 2019 FME (http://fmeextensions.com/)
 * @license   https://fmeextensions.com/LICENSE.txt
 */

namespace FME\RestrictPaymentMethod\Model;

class PaymentMethod extends \Magento\Rule\Model\AbstractModel
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'fme_paymentmethod';
    const TYPE_ID = 'rule_id';
    const NAME = 'name';

    /**
     * @var string
     */
    protected $_cacheTag = 'fme_paymentmethod';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'fme_paymentmethod';

    protected $_eventObject = 'rule';
 
    /** @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory */
    protected $condCombineFactory;
 
    /** @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory */
    protected $condProdCombineF;
 
    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    protected $validatedAddresses = [];
 
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->condCombineFactory = $condCombineFactory;
        $this->condProdCombineF = $condProdCombineF;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }
    
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('FME\RestrictPaymentMethod\Model\ResourceModel\PaymentMethod');
    }

    public function getRelatedCustomers($ruleId)
    {

        $blockTable = $this->getResource()->getTable('fme_paymentmethod_customer');

        $collection = $this->getResourceCollection()

                ->addFieldToFilter('main_table.rule_id', $ruleId);

        $collection->getSelect()
                ->joinLeft(['related' => $blockTable], 'main_table.rule_id = related.rule_id')
                ->order('main_table.rule_id');
        return $collection->getData();
    }
    public function getRelatedDays($ruleId)
    {
        $blockTable = $this->getResource()->getTable('fme_paymentmethod_days');
        $collection = $this->getResourceCollection()->addFieldToFilter('main_table.rule_id', $ruleId);
        $collection->getSelect()
        ->joinLeft(['related' => $blockTable], 'main_table.rule_id = related.rule_id')
        ->where('related.rule_id = (?)', $ruleId);
        return $collection->getData();
    }

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }
 
    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->condProdCombineF->create();
    }
 
    /**
     * Check cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? true : false;
    }
 
    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     * @return $this
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->validatedAddresses[$addressId] = $validationResult;
        return $this;
    }
 
    /**
     * Get cached validation result for specific address
     *
     * @param Address $address
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->validatedAddresses[$addressId]) ? $this->validatedAddresses[$addressId] : false;
    }
 
    /**
     * Return id for address
     *
     * @param Address $address
     * @return string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }
        return $address;
    }


    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getRuleId()
    {
        return $this->getData(self::TYPE_ID);
    }

    /**
     * Set EntityId.
     */
    public function setRuleId($typeId)
    {
        return $this->setData(self::TYPE_ID, $typeId);
    }

    /**
     * Get Title.
     *
     * @return varchar
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set Title.
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get IsActive.
     *
     * @return varchar
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set IsActive.
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Get UpdateTime.
     *
     * @return varchar
     */
    public function getUpdateAt()
    {
        return $this->getData(self::UPDATE_AT);
    }

    /**
     * Set UpdateTime.
     */
    public function setUpdateAt($updateTime)
    {
        return $this->setData(self::UPDATE_AT, $updateAt);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
