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
namespace Bss\RewardPoint\Model\Total\Quote;

use Bss\RewardPoint\Helper\Data;
use Bss\RewardPoint\Model\Config\Source\ReceivePoint;
use Bss\RewardPoint\Model\Config\Source\RuleType;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Earn Point
 *
 * Bss\RewardPoint\Model\Total\Quote
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EarnPoint extends AbstractTotal
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Bss\RewardPoint\Model\RateFactory
     */
    protected $rateFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory
     */
    protected $configurableFactory;

    /**
     * @var \Bss\RewardPoint\Model\RuleFactory
     */
    protected $ruleFactory;
    /**
     * EarnPoint constructor.
     *
     * @param Data $helper
     * @param \Bss\RewardPoint\Model\RateFactory $rateFactory
     * @param \Bss\RewardPoint\Model\RuleFactory $ruleFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helper,
        \Bss\RewardPoint\Model\RateFactory $rateFactory,
        \Bss\RewardPoint\Model\RuleFactory $ruleFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper          = $helper;
        $this->rateFactory     = $rateFactory;
        $this->ruleFactory     = $ruleFactory;
        $this->productFactory  = $productFactory;
        $this->configurableFactory  = $configurableFactory;
        $this->storeManager    = $storeManager;
        $this->setCode('earn_point');
    }

    /**
     * Collect
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $store = $this->storeManager->getStore($quote->getStoreId());
        $websiteId = $store->getWebsiteId();
        $customerGroupId = $quote->getCustomerGroupId();

        if (!$quote->getCustomer()->getId() || $quote->getIsMultiShipping() || !$this->helper->isActive($websiteId)) {
            return $this;
        }

        if (!$this->helper->isEarnOrderPaidbyPoint($websiteId) && $quote->getSpendPoints() > 0) {
            $total->setEarnPoints(0);
            $quote->setEarnPoints(0);
            return $this;
        }

        $items = $shippingAssignment->getItems();
        $number_item = count($items);
        if (!$number_item) {
            return $this;
        }
        $points = 0;
        $note = '';
        $data = $this->getCartRule($quote, $websiteId, $customerGroupId);
        if (!empty($data)) {
            $baseGrandTotal = $total->getBaseGrandTotal();
            if (!$this->helper->isEarnPointforTax($websiteId)) {
                $baseTaxAmountUsed = $total->getBaseTaxAmount();
                $baseGrandTotal -= $baseTaxAmountUsed;
            }
            if (!$this->helper->isEarnPointforShip($websiteId)) {
                $baseShippingAmountUsed = $total->getBaseShippingAmount();
                $baseGrandTotal -= $baseShippingAmountUsed;
            }
            foreach ($data as $rule) {
                if ($rule['simple_action'] == 'fixed') {
                    $points += $rule['point'];
                } else {
                    if ($baseGrandTotal != null &&
                        $rule['purchase_point'] != null &&
                        $rule['spent_amount'] != null &&
                        $rule['spent_amount'] != 0) {
                        $points += round($baseGrandTotal*$rule['purchase_point']/$rule['spent_amount']);
                    }
                }
                $note .= $rule['note'];
                if (next($data) == true) {
                    $note .= ' + ';
                }
            }
            $quote->setRwpNote($note);
        }
        if ($this->getProductRewardPoints($items, $store, $customerGroupId) != null) {
            $points += round($this->getProductRewardPoints($items, $store, $customerGroupId));
        }
        // check maximum points can earn per order
        $points = (int)$this->getMaximumEarnPerOrder($points);

        $total->setEarnPoints($points);
        $quote->setEarnPoints($points);
        return $this;
    }

    /**
     * Get maximum earn per order
     *
     * @param int $points
     * @return int
     */
    public function getMaximumEarnPerOrder($points)
    {
        if ($this->helper->getMaximumEarnPerOrder() > 0 && $this->helper->getMaximumEarnPerOrder() < $points) {
            $points = (int)$this->helper->getMaximumEarnPerOrder();
        }
        return $points;
    }

    /**
     * Fetch
     *
     * @param Quote $quote
     * @param Total $total
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetch(Quote $quote, Total $total)
    {
        $isShowPointInCart = $this->helper->getFlagConfig(
            Data::XML_PATH_CART_ORDER_SUMMARY,
            ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );

        if ($total->getEarnPoints() && $isShowPointInCart) {
            return [
                'code' => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $total->getEarnPoints()
            ];
        }
        return null;
    }

    /**
     * Get label
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('Earn point');
    }

    /**
     * Get product reward point
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $items
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param int $customerGroupId
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductRewardPoints($items, $store, $customerGroupId)
    {
        $websiteId = $store->getWebsiteId();
        $rate = $this->rateFactory->create()->fetch(
            $customerGroupId,
            $websiteId
        );

        $qtys_configurable = $qtys = $productIds = [];
        foreach ($items as $item) {
            $product_cf = $this->getConfigurableProductIds($item->getProductId());
            if ($item->getProductType() == 'configurable') {
                $qtys_configurable[$item->getSku()] = $item->getQty();
            }
            if (isset($product_cf[0]) || (!$item->getParentItem() && $item->getProductType() != 'configurable')) {
                $productIds[] = $item->getProductId();
                $qtys[$item->getProductId()] = isset($product_cf[0]) && isset($qtys_configurable[$item->getSku()])
                    ? $qtys_configurable[$item->getSku()] : $item->getQty();
            }
        }
        $points = $this->_getProductRewardPoints($rate, $qtys, $productIds);
        return $points;
    }

    /**
     * Get product reward point
     *
     * @param \Bss\RewardPoint\Model\Rate $rate
     * @param array $qtys
     * @param array $productIds
     * @return float|int
     */
    protected function _getProductRewardPoints($rate, $qtys, $productIds = [])
    {
        $points = 0;
        if (!empty($productIds)) {
            $collection = $this->productFactory->create()->getCollection();
            $collection->addAttributeToSelect('*')->addFieldToFilter('entity_id', ['in'=> $productIds])->load();
            foreach ($collection as $product) {
                $point = 0;
                if ($product->getAssignBy() == ReceivePoint::EXCHANGE_RATE) {
                    $baseCurrency = $rate->getBasecurrencyToPointRate() ?? 1;
                    $point = $baseCurrency * $product->getFinalPrice();
                } elseif ($product->getAssignBy() == ReceivePoint::FIX_AMOUNT) {
                    $point = $product->getReceivePoint();
                }

                $point = $product->getDependentQty() ? $point*$qtys[$product->getId()] : $point;
                $points+= ($point == null) ? 0 : round($point);
            }
        }
        return $points;
    }

    /**
     * Get configurable product id
     *
     * @param int $productId
     * @return string[]
     */
    public function getConfigurableProductIds($productId)
    {
        return $this->configurableFactory->create()->getParentIdsByChild($productId);
    }

    /**
     * Get cart rule
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $websiteId
     * @param int $customerGroupId
     * @return array
     */
    public function getCartRule($quote, $websiteId, $customerGroupId)
    {
        $collection_rules = $this->ruleFactory->create()->getCollection();
        $rules = $collection_rules->addWebsiteGroupDateFilter($websiteId, $customerGroupId)
            ->addFieldToFilter('type', RuleType::RULE_TYE_CART);

        $data = [];
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        foreach ($rules as $rule) {
            $rule->afterLoad();
            if (!$rule->validate($address)) {
                continue;
            }
            $data[] = [
                'simple_action' => $rule->getSimpleAction(),
                'point' => $rule->getPoint(),
                'purchase_point' => $rule->getPurchasePoint(),
                'spent_amount' => $rule->getSpentAmount(),
                'note' => $rule->getStoreNote($quote->getStore())
            ];
        }
        return $data;
    }
}
