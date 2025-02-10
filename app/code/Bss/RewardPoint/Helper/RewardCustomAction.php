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
namespace Bss\RewardPoint\Helper;

use Bss\RewardPoint\Model\Config\Source\ReceivePoint;
use Bss\RewardPoint\Model\Config\Source\RuleType;
use Bss\RewardPoint\Model\Config\Source\TransactionActions;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Reward CustomAction
 *
 * Bss\RewardPoint\Helper
 */
class RewardCustomAction extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Bss\RewardPoint\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Bss\RewardPoint\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Bss\RewardPoint\Model\RateFactory
     */
    protected $rateFactory;

    /**
     * RewardCustomAction constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Bss\RewardPoint\Helper\Data $helper
     * @param \Bss\RewardPoint\Model\RateFactory $rateFactory
     * @param \Bss\RewardPoint\Model\RuleFactory $ruleFactory
     * @param \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Bss\RewardPoint\Helper\Data $helper,
        \Bss\RewardPoint\Model\RateFactory $rateFactory,
        \Bss\RewardPoint\Model\RuleFactory $ruleFactory,
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
    ) {
        $this->productFactory               = $productFactory;
        $this->customerFactory              = $customerFactory;
        $this->storeManager                 = $storeManager;
        $this->messageManager               = $messageManager;
        $this->helper                       = $helper;
        $this->ruleFactory                  = $ruleFactory;
        $this->transactionFactory           = $transactionFactory;
        $this->rateFactory = $rateFactory;
        parent::__construct($context);
    }

    /**
     * Process custom rule
     *
     * @param string $action
     * @param array $options
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processCustomRule($action, $options = [])
    {
        $customer = $this->getCustomer($options['customerId']);
        $product = isset($options['productId']) ? $this->getLoadProduct($options['productId']) : false;
        $action_id = isset($options['action_id']) ? $options['action_id'] : null;

        if ($this->validate($action, $action_id, $customer, $product) && $this->helper->isActive()) {
            $store = $this->storeManager->getStore($options['storeId']);
            $websiteId = $store->getWebsite()->getId();
            $customerId = $customer->getId();
            $customerGroupId = $customer->getGroupId();
            $collection_rules = $this->ruleFactory->create()->getCollection();
            $rules = $collection_rules->addWebsiteGroupDateFilter($websiteId, $customerGroupId)
                ->addFieldToFilter('type', RuleType::RULE_TYE_CUSTOM)->setOrder('priority', 'DESC');
            $object = ($action == TransactionActions::REVIEW) ? $product : $customer;
            $data = [];
            $points = 0;
            foreach ($rules as $rule) {
                $rule->afterLoad();
                $object->setAction($action);
                $object->setEmail($customer->getEmail());

                if (!$rule->validate($object)) {
                    continue;
                }
                $points = $rule->getPoint();
                $maximum_point_for_review = $this->helper->getMaximumEarnReview($websiteId);
                if ($action == TransactionActions::REVIEW
                    && !empty($maximum_point_for_review)
                    && $maximum_point_for_review
                ) {
                    $points = $this->getRewardPointReview($maximum_point_for_review, $customerId, $websiteId, $points);
                }
                $data = [
                    'website_id' => $websiteId,
                    'customer_id' => $customerId,
                    'point' => $points,
                    'action' => $action,
                    'action_id' => $action_id,
                    'created_at' => $this->helper->getCreateAt(),
                    'note' => $rule->getStoreNote($store->getId()),
                    'created_by' => $object->getEmail(),
                    'is_expired' => (bool)$this->helper->getExpireDay($websiteId),
                    'expires_at' => $this->helper->getExpireDay($websiteId)
                ];
                break;
            }

            if (!empty($data)) {
                try {
                    $reward = $this->transactionFactory->create();
                    $reward->setData($data);
                    $reward->save();
                    $this->addSuccessMessage($points, $action);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while saving the transaction reward points.')
                    );
                }
            }
        }
    }

    /**
     * Get registration rule
     *
     * @return string|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegistrationRulePoint()
    {
        if ($this->helper->isActive()) {
            $store = $this->storeManager->getStore();
            $websiteId = $store->getWebsite()->getId();
            $customerGroupId = $this->helper->getValueConfig(
                'customer/create_account/default_group',
                ScopeInterface::SCOPE_STORE
            );
            $rulesCollection = $this->ruleFactory->create()->getCollection();
            $rulesCollection->addWebsiteGroupDateFilter($websiteId, $customerGroupId);
            $rulesCollection->addFieldToFilter('type', RuleType::RULE_TYE_CUSTOM)->setOrder('priority', 'DESC');
            $object = $this->customerFactory->create();
            foreach ($rulesCollection as $rule) {
                $rule->afterLoad();
                $object->setAction(TransactionActions::REGISTRATION);
                if (!$rule->validate($object)) {
                    continue;
                }
                $points = $rule->getPoint();
                return $points;
            }
        }
        return 0;
    }

    /**
     * Get subcribe rule point
     *
     * @param int $customerGroupId
     * @return string|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubcriberRulePoint($customerGroupId)
    {
        if ($this->helper->isActive()) {
            $store = $this->storeManager->getStore();
            $websiteId = $store->getWebsite()->getId();
            if ($customerGroupId === null) {
                $customerGroupId = $this->helper->getValueConfig(
                    'customer/create_account/default_group',
                    ScopeInterface::SCOPE_STORE
                );
            }
            $object = $this->customerFactory->create();
            $rulesCollection = $this->ruleFactory->create()->getCollection();
            $rulesCollection->addWebsiteGroupDateFilter($websiteId, $customerGroupId);
            $rulesCollection->addFieldToFilter('type', RuleType::RULE_TYE_CUSTOM)->setOrder('priority', 'DESC');

            foreach ($rulesCollection as $rule) {
                $rule->afterLoad();
                $object->setAction(TransactionActions::SUBSCRIBLE_NEWSLETTERS);

                if (!$rule->validate($object)) {
                    continue;
                }

                $points = $rule->getPoint();
                return $points;
            }
        }
        return 0;
    }

    /**
     * Get review rule point
     *
     * @param int|string $customerGroupId
     * @param \Magento\Catalog\Model\Product $product
     * @return string|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReviewRulePoint($customerGroupId, $product)
    {
        if ($this->helper->isActive()) {
            $store = $this->storeManager->getStore();
            $websiteId = $store->getWebsite()->getId();
            if ($customerGroupId === null) {
                $customerGroupId = $this->helper->getValueConfig(
                    'customer/create_account/default_group',
                    ScopeInterface::SCOPE_STORE
                );
            }
            $rulesCollection = $this->ruleFactory->create()->getCollection();
            $rulesCollection->addWebsiteGroupDateFilter($websiteId, $customerGroupId);
            $rulesCollection->addFieldToFilter('type', RuleType::RULE_TYE_CUSTOM)->setOrder('priority', 'DESC');
            $object = $product->getId() ? $product : $this->productFactory->create();
            foreach ($rulesCollection as $rule) {
                $rule->afterLoad();
                $object->setAction(TransactionActions::REVIEW);
                if (!$rule->validate($object)) {
                    continue;
                }
                $points = $rule->getPoint();
                return $points;
            }
        }
        return 0;
    }

    /**
     * Get reward point review
     *
     * @param array $config
     * @param int $customerId
     * @param int $websiteId
     * @param int $points
     * @return $this|int
     */
    public function getRewardPointReview($config, $customerId, $websiteId, $points)
    {
        $maximum_point = $config['maximum_point'];
        $bind = [
            'customer_id'=> $customerId,
            'website_id'=> $websiteId,
            'action'=> TransactionActions::REVIEW,
            'from'=> $config['from'],
            'to'=> $config['to']
        ];
        $transaction =  $this->transactionFactory->create();
        $point_balance_review = $transaction->getPointBalanceReview($bind);

        if ($maximum_point <= $point_balance_review) {
            return 0;
        }
        $maximum_point_allow = $maximum_point - $point_balance_review;
        $reward_point = ($maximum_point_allow < $points) ? $maximum_point_allow : $points;
        return $reward_point;
    }

    /**
     * Get exchange rate
     *
     * @param int $customerGroupId
     * @param int $websiteId
     * @return \Bss\RewardPoint\Model\Rate
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExchangeRate($customerGroupId, $websiteId)
    {
        $rate = $this->rateFactory->create()->fetch(
            $customerGroupId,
            $websiteId
        );
        return $rate;
    }

    /**
     * Get point by product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $customerGroupId
     * @return float|int|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPointByProduct($product, $customerGroupId)
    {
        $rate = $this->getExchangeRate(
            $customerGroupId,
            $this->storeManager->getWebsite()->getId()
        );
        $point = 0;
        switch ($product->getTypeId()) {
            case 'configurable':
                $children = $product->getTypeInstance()->getUsedProducts($product);
                $points = $this->getPointForConfigurable($children, $rate);
                $point = empty($points) ? 0 : min($points);
                break;
            case 'grouped':
                $children = $product->getTypeInstance()->getAssociatedProducts($product);
                $points = $this->getPointForGrouped($children, $rate);
                $point = empty($points) ? 0 : min($points);
                break;
            default:
                $assignBy = $this->getRawAttributeByProduct($product, 'assign_by');
                $receivedPoint = $this->getRawAttributeByProduct($product, 'receive_point');
                if ($assignBy == ReceivePoint::EXCHANGE_RATE) {
                    $baseCurrency = $rate->getBasecurrencyToPointRate() ?? 1;
                    $point = $baseCurrency * $product->getFinalPrice();
                }
                if ($assignBy == ReceivePoint::FIX_AMOUNT) {
                    $point = $receivedPoint;
                }
        }
        return $point;
    }

    /**
     * Get point for grouped
     *
     * @param \Magento\Catalog\Model\Product $children
     * @param \Bss\RewardPoint\Model\Rate $rate
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getPointForGrouped($children, $rate)
    {
        $points = [];
        foreach ($children as $child) {
            $childPoint = 0;
            $assignBy = $this->getRawAttributeByProduct($child, 'assign_by');
            $receivedPoint = $this->getRawAttributeByProduct($child, 'receive_point');
            if ($assignBy == ReceivePoint::EXCHANGE_RATE) {
                $baseCurrency = $rate->getBasecurrencyToPointRate() ?? 1;
                $childPoint = $baseCurrency * $child->getFinalPrice();
            }

            if ($assignBy == ReceivePoint::FIX_AMOUNT) {
                $childPoint = $receivedPoint;
            }
            if ($childPoint > 0) {
                $points[] = $childPoint;
            }
        }
        return $points;
    }

    /**
     * Get point for configurable
     *
     * @param \Magento\Catalog\Model\Product $children
     * @param \Bss\RewardPoint\Model\Rate $rate
     * @return array
     */
    protected function getPointForConfigurable($children, $rate)
    {
        $points = [];
        foreach ($children as $child) {
            $childPoint = 0;
            if ($child->getAssignBy() == ReceivePoint::EXCHANGE_RATE) {
                $baseCurrency = $rate->getBasecurrencyToPointRate() ?? 1;
                $childPoint = $baseCurrency * $child->getFinalPrice();
            }

            if ($child->getAssignBy() == ReceivePoint::FIX_AMOUNT) {
                $childPoint = $child->getReceivePoint();
            }
            if ($childPoint > 0) {
                $points[] = $childPoint;
            }
        }
        return $points;
    }

    /**
     * Get raw attribute by product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $attributeCode
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getRawAttributeByProduct($product, $attributeCode)
    {
        return $product->getResource()->getAttributeRawValue(
            $product->getId(),
            $attributeCode,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * Validate
     *
     * @param string $action
     * @param int $action_id
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function validate($action, $action_id, $customer, $product)
    {
        if (!$customer || ($action == TransactionActions::REVIEW && !$product)) {
            return false;
        } else {
            $customerId = $customer->getId();
            return $this->checkFirstAction($action, $action_id, $customerId);
        }
    }

    /**
     * Check first action
     *
     * @param string $action
     * @param int $action_id
     * @param int $customerId
     * @return bool
     */
    protected function checkFirstAction($action, $action_id, $customerId)
    {
        $collection = $this->transactionFactory->create()->getCollection();
        $collection->addFieldToFilter('action', $action)->addFieldToFilter('customer_id', $customerId);

        if ($action_id && $action == TransactionActions::REVIEW) {
            $collection->addFieldToFilter('action_id', $action_id);
        }

        $isAllow = $collection->getSize() == 0;

        if ($isAllow && $action == TransactionActions::BIRTHDAY) {
            $year_now = date("Y");
            foreach ($collection as $item) {
                $year_rwp = date("Y", strtotime($item->getCreatedAt()));
                if ($year_rwp < $year_now) {
                    $isAllow = false;
                }
            }
        }
        return $isAllow;
    }

    /**
     * Get customer
     *
     * @param int $customerId
     * @return \Magento\Customer\Model\Customer|null
     */
    public function getCustomer($customerId)
    {
        if ($customerId) {
            $customer = $this->customerFactory->create()->load($customerId);
            return $customer;
        }
        return null;
    }

    /**
     * Get load product
     *
     * @param int $id
     * @return \Magento\Catalog\Model\Product
     */
    public function getLoadProduct($id)
    {
        return $this->productFactory->create()->load($id);
    }

    /**
     * Add success message
     *
     * @param int $points
     * @param string $ruleType
     */
    protected function addSuccessMessage($points, $ruleType)
    {
        $messages = [
            TransactionActions::REGISTRATION => __('You have earned %1 points!'),
            TransactionActions::FIRST_ORDER => __('%1 points for first order. Congrats! '),
            TransactionActions::SUBSCRIBLE_NEWSLETTERS => __('You received %1 for sign up for newsletter')
        ];

        if (isset($messages[$ruleType])) {
            $notification = __($messages[$ruleType], (int)$points);
            $this->messageManager->addSuccess($notification);
        }
    }
}
