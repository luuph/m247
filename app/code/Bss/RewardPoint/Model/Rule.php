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

namespace Bss\RewardPoint\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

class Rule extends \Magento\SalesRule\Model\Rule
{
    /**
     * @var Rule\Condition\CombineFactory
     */
    protected $bsscondCombineFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Rule constructor.
     * @param Rule\Condition\CombineFactory $bsscondCombineFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\RewardPoint\Model\Rule\Condition\CombineFactory           $bsscondCombineFactory,
        \Magento\Framework\Model\Context                               $context,
        \Magento\Framework\Registry                                    $registry,
        \Magento\Framework\Data\FormFactory                            $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $localeDate,
        \Magento\SalesRule\Model\CouponFactory                         $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory           $codegenFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory         $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Collection       $couponCollection,
        \Magento\Store\Model\StoreManagerInterface                     $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource        $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb                  $resourceCollection = null,
        array                                                          $data = [],
        ExtensionAttributesFactory                                     $extensionFactory = null,
        AttributeValueFactory                                          $customAttributeFactory = null,
        \Magento\Framework\Serialize\Serializer\Json                   $serializer = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $couponFactory,
            $codegenFactory,
            $condCombineFactory,
            $condProdCombineF,
            $couponCollection,
            $storeManager,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
        $this->bsscondCombineFactory = $bsscondCombineFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Rule Methods
     *
     * @return Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->bsscondCombineFactory->create();
    }

    /**
     * Get Rule note by specified store
     *
     * @param \Magento\Store\Model\Store|int|bool|null $store
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreNote($store = null)
    {
        $storeId = $this->storeManager->getStore($store)->getId();
        $label = $this->_getResource()->getStoreNote($this->getId(), $storeId);
        return $label;
    }

    /**
     * Set if not yet and retrieve rule store labels
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreNotes()
    {
        if (!$this->hasStoreNotes()) {
            $labels = $this->_getResource()->getStoreNotes($this->getId());
            $this->setStoreNotes($labels);
        }

        return $this->_getData('store_notes');
    }

    /**
     * Validate rule data
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string[] - return true if validation passed successfully. Array with errors description otherwise
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateData(DataObject $dataObject)
    {
        $result = [];
        $fromDate = $toDate = null;

        if ($dataObject->hasFromDate() && $dataObject->hasToDate()) {
            $fromDate = $dataObject->getFromDate();
            $toDate = $dataObject->getToDate();
        }

        if (!$this->validateDate($fromDate) || !$this->validateDate($toDate)) {
            $result[] = __('Invalid Date format.');
        } elseif ($this->validateDate($fromDate) && $this->validateDate($toDate)) {
            if ($fromDate && $toDate) {
                $fromDate = strtotime($fromDate);
                $toDate = strtotime($toDate);
                if ($fromDate > $toDate) {
                    $result[] = __('End Date must follow Start Date.');
                }
            }
        }

        if ($dataObject->hasWebsiteIds()) {
            $websiteIds = $dataObject->getWebsiteIds();
            if (empty($websiteIds)) {
                $result[] = __('Please specify a website.');
            }
        }
        if ($dataObject->hasCustomerGroupIds()) {
            $customerGroupIds = $dataObject->getCustomerGroupIds();
            if (empty($customerGroupIds)) {
                $result[] = __('Please specify Customer Groups.');
            }
        }

        return !empty($result) ? $result : true;
    }

    /**
     * Validate date field
     *
     * @param string $date
     * @return bool
     */
    protected function validateDate($date)
    {
        if (empty($date)) {
            return true;
        }
        $dateToArr = explode('/', $date);
        if (isset($dateToArr[0]) && isset($dateToArr[1]) && isset($dateToArr[2])) {
            return checkdate($dateToArr[0], $dateToArr[1], $dateToArr[2]);
        }
        return false;
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\RewardPoint\Model\ResourceModel\Rule::class);
        $this->setIdFieldName('rule_id');
    }
}
