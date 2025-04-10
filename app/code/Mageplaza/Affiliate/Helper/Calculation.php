<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Affiliate\Helper;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Cms\Model\BlockFactory;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Calculator;
use Magento\Framework\Math\CalculatorFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Affiliate\Model\AccountFactory;
use Mageplaza\Affiliate\Model\Campaign;
use Mageplaza\Affiliate\Model\CampaignFactory;
use Mageplaza\Affiliate\Model\TransactionFactory;

/**
 * Class Calculation
 *
 * @package Mageplaza\Affiliate\Helper
 */
class Calculation extends Data
{
    /**
     * @var DataObject
     */
    protected $_address;

    /**
     * Calculator instances for delta rounding of prices
     *
     * @var Calculator[]
     */
    protected $_calculators = [];

    /**
     * @var array
     */
    protected $_total = [];

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var CalculatorFactory
     */
    protected $_calculatorFactory;

    /**
     * @var Collection
     */
    protected $productCollection;

    /**
     * Calculation constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param AccountFactory $accountFactory
     * @param CampaignFactory $campaignFactory
     * @param TransactionFactory $transactionFactory
     * @param BlockFactory $blockFactory
     * @param CustomerFactory $customerFactory
     * @param CookieManagerInterface $cookieManagerInterface
     * @param CustomerSession $customerSession
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param CustomerViewHelper $customerViewHelper
     * @param LayoutInterface $layout
     * @param Registry $registry
     * @param CalculatorFactory $calculatorFactory ,
     * @param Collection $productCollection
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        AccountFactory $accountFactory,
        CampaignFactory $campaignFactory,
        TransactionFactory $transactionFactory,
        BlockFactory $blockFactory,
        CustomerFactory $customerFactory,
        CookieManagerInterface $cookieManagerInterface,
        CustomerSession $customerSession,
        CookieMetadataFactory $cookieMetadataFactory,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        CustomerViewHelper $customerViewHelper,
        LayoutInterface $layout,
        Registry $registry,
        CalculatorFactory $calculatorFactory,
        Collection $productCollection,
        ManagerInterface $messageManager
    ) {
        $this->_calculatorFactory = $calculatorFactory;
        $this->productCollection  = $productCollection;
        $this->_messageManager    = $messageManager;

        parent::__construct(
            $context,
            $objectManager,
            $accountFactory,
            $campaignFactory,
            $transactionFactory,
            $blockFactory,
            $customerFactory,
            $cookieManagerInterface,
            $customerSession,
            $cookieMetadataFactory,
            $priceCurrency,
            $storeManager,
            $transportBuilder,
            $customerViewHelper,
            $layout,
            $registry
        );
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $this->_address = $shippingAssignment->getShipping()->getAddress();
        $this->_total   = $total;

        return $this;
    }

    /**
     * @param string|int  $storeId
     * @param array $items
     * @param false $isDiscount
     *
     * @return bool|int|void
     * @throws FailureToSendException
     * @throws InputException
     * @throws LocalizedException
     */
    public function canCalculate($storeId, $items = [], $isDiscount = false)
    {
        // - only calculate discount if has key and do not has first order
        // - commission always calculated if has first order

        $key              = $this->getAffiliateKey(); // if no cookie then first order key
        $affSource        = $this->getAffiliateSourceFromCookie(self::AFFILIATE_COOKIE_SOURCE_NAME);
        $couponWithPreFix = explode('-', $key ?? '');

        if ($affSource === 'coupon' && count($couponWithPreFix) !== 2) {
            if ($this->getAffiliateSourceFromCookie(self::AFFILIATE_COOKIE_SOURCE_NAME)) {
                $this->deleteAffiliateKeyFromCookie(self::AFFILIATE_COOKIE_SOURCE_NAME);
            }
            return false;
        }
        if (count($couponWithPreFix) === 2) {
            [$key] = $couponWithPreFix;
        }

        $account   = $this->getCurrentAffiliate(); // get aff acc base on current customer id
        $campaigns = [];

        if (!$this->registry->registry('mp_affiliate_account')) {
            $this->registry->register('mp_affiliate_account', $this->getAffiliateByKeyOrCode($key));
        }
        $refAcc = $this->registry->registry('mp_affiliate_account');
        if ($key && !$account->getId() && $refAcc->getId() && $refAcc->isActive()) {
            $campaigns = $this->getAvailableCampaign($refAcc, $items);
        }
        if ($isDiscount) {
            if ($this->getAffiliateKeyFromCookie(self::AFFILIATE_COOKIE_SOURCE_NAME) === 'coupon') {
                if (!$this->isUseCodeAsCoupon($storeId)) {
                    $this->deleteAffiliateCookieSourceName();

                    return false;
                }
            } else {
                return count($campaigns) && !$this->hasFirstOrder();
            }
        }

        return count($campaigns);// && $this->hasFirstOrder();
    }

    /**
     * @param null $account
     * @param array $items
     *
     * @return mixed|null
     * @throws LocalizedException
     */
    public function getAvailableCampaign($account = null, $items = [])
    {
        if ($account === null) {
            $account = $this->getCurrentAffiliate();
        }
        $cacheKey = 'affiliate_available_campaign_' . $account->getId();
        if (!self::hasCache($cacheKey)) {
            $campaignResult = [];
            if ($this->getAffiliateSourceFromCookie() === 'banner') {
                $campaign = $this->getCampaignRelatedToBanner();
                if ($campaign->validate($this->_address)) {
                    $campaignResult[] = $campaign;
                }
                self::saveCache($cacheKey, $campaignResult);

                return self::getCache($cacheKey);
            }

            $campaigns   = $this->campaignFactory->create()->getCollection()
                ->getAvailableCampaign(
                    $account->getGroupId(),
                    $this->storeManager->getWebsite()->getId(),
                );
            $product_ids = [];
            foreach ($items as $item) {
                if (!$item->getParentItemId()) {
                    $product_ids[] = $item->getProductId();
                }
            }
            $products = $this->productCollection->addFieldToFilter('entity_id',
                ['in' => $product_ids])->load()->getItems();
            /** @var Campaign $campaign */
            foreach ($campaigns as $campaign) {
                $campaign->setCommission($this->unserialize($campaign->getCommission()));
                $discountAffiliate = 0;
                $campaignItems     = [];
                if ($campaign->validate($this->_address)) {
                    foreach ($items as $key => $item) {
                        if ($this->_request->getActionName() === 'add' && !method_exists($item, 'getParentItemId')
                            && $item->getProduct()->getParentProductId()) {
                            continue;
                        }
                        if (!$item->getParentItemId() &&
                            $campaign->validateActionsRule($products[$item->getProductId()])) {
                            $discount = $this->calculateDiscount($campaign, $item);
                            if ($discount > 0) {
                                $discountAffiliate += $discount;
                                $item->setCampaignId($campaign->getCampaignId());
                                $campaignItems[] = $item;
                                unset($items[$key]);
                            }
                        }
                    }
                    if ($discountAffiliate > 0) {
                        $campaign->setdiscountAffiliate($discountAffiliate);
                        $campaign->setCampaignItems($campaignItems);
                        $campaignResult[] = $campaign;
                    }
                }
            }
            self::saveCache($cacheKey, $campaignResult);
        }

        return self::getCache($cacheKey);
    }

    /**
     * @param $account
     * @param $items
     * @param $address
     *
     * @return bool
     * @throws LocalizedException
     */
    public function checkCoupon($account = null, $items = [], $address = null)
    {
        if ($account === null) {
            $account = $this->getCurrentAffiliate();
        }
        $campaigns         = $this->campaignFactory->create()->getCollection()
            ->getAvailableCampaign(
                $account->getGroupId(),
                $this->storeManager->getWebsite()->getId(),
            );
        $discountAffiliate = 0;
        $product_ids       = [];
        foreach ($items as $key => $item) {
            $product_ids[] = $item->getProductId();
        }
        $products = $this->productCollection->addFieldToFilter('entity_id', ['in' => $product_ids])->load()->getItems();
        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $campaignItems = [];
            if ($campaign->validate($address)) {
                foreach ($items as $key => $item) {
                    if ($campaign->validateActionsRule($products[$item->getProductId()]) &&
                        !$item->getParentItemId()) {
                        $discount = $this->calculateDiscount($campaign, $item);
                        if ($discount > 0) {
                            $discountAffiliate += $discount;
                            $campaignItems[]   = $item;
                            unset($items[$key]);
                        }
                    }
                }
            }
        }
        if ($discountAffiliate < 0.0001) {
            return true;
        }

        return false;
    }

    /**
     * @param $campaign
     * @param $item
     *
     * @return float
     */
    public function calculateDiscount($campaign, $item)
    {
        $discount_action = $campaign->getDiscountAction();
        $isCalculateTax  = $campaign->getApplyDiscountOnTax();
        $discount = $this->calculateItemDiscountAffiliate(
            $item,
            $campaign->getDiscountAmount(),
            $discount_action,
            $isCalculateTax
        );

        return $discount;
    }

    /**
     * @param $item
     * @param $discountValue
     * @param $discount_action
     * @param $isCalculateTax
     *
     * @return float
     */
    public function calculateItemDiscountAffiliate(
        $item,
        $discountValue,
        $discount_action,
        $isCalculateTax
    ) {
        if ($discount_action === 'by_percent') {
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $itemBaseDiscount = 0;
                foreach ($item->getChildren() as $child) {
                    $itemBaseDiscount += $this->getItemTotalForDiscount($child, $isCalculateTax,
                            false) * $discountValue / 100;
                }
                $itemBaseDiscount = $this->round($itemBaseDiscount, 'base');
            } else {
                $itemBaseDiscount = $this->getItemTotalForDiscount($item, $isCalculateTax,
                        false) * $discountValue / 100;
                $itemBaseDiscount = $this->round($itemBaseDiscount, 'base');
            }
        } else {
            $itemBaseDiscount = $discountValue;
        }
        $itemDiscount = $this->convertPrice($itemBaseDiscount, false, false, $item->getStoreId());
        $itemDiscount = $this->round($itemDiscount);
        $item->setBaseAffiliateDiscountAmount($itemBaseDiscount)
            ->setAffiliateDiscountAmount($itemDiscount);

        return $itemDiscount;
    }

    /**
     * Retrieve the campaign related to the banner via cookie value
     *
     * @return Campaign
     */
    public function getCampaignRelatedToBanner()
    {
        $bannerId   = $this->getAffiliateSourceValueFromCookie();
        $campaignId = $this->objectManager->create(
            'Mageplaza\AffiliatePro\Model\Banner'
        )->load($bannerId)->getCampaignId();

        return $this->campaignFactory->create()->load($campaignId);
    }

    /**
     * @param       $items
     * @param       $quote
     * @param array $quoteFields
     * @param array $itemFields
     */
    public function resetAffiliateData($items, $quote, $quoteFields = [], $itemFields = [])
    {
        $this->resetFields($quote, $quoteFields);
        foreach ($items as $item) {
            $this->resetFields($item, $itemFields);
        }
    }

    /**
     * @param $object ($quote || item)
     * @param $fields
     */
    public function resetFields($object, $fields)
    {
        foreach ($fields as $field) {
            $object->setData($field, 0);
        }
    }

    /**
     * @param      $quote
     * @param bool $isCalculateAffDiscount
     *
     * @return int
     */
    public function getShippingTotalForDiscount($quote, $isCalculateAffDiscount = true)
    {
        $total = 0;
        if (!$quote->getIsVirtual()) {
            $total = $this->_total->getBaseShippingInclTax();
            $total -= $this->_total->getBaseShippingDiscountAmount();
            if ($isCalculateAffDiscount) {
                $total -= $quote->getBaseAffiliateDiscountShippingAmount();
            }
        }

        return $total;
    }

    /**
     * @param      $item
     * @param bool $isCalculateTax
     * @param bool $isCalculateAffDiscount
     *
     * @return mixed
     */
    public function getItemTotalForDiscount($item, $isCalculateTax = false, $isCalculateAffDiscount = true)
    {
        $total = 0;

        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            $itemTotal = 0;
            foreach ($item->getChildren() as $child) {
                $itemTotal = $child->getBaseRowTotal() - $child->getBaseDiscountAmount();

                if ($isCalculateAffDiscount) {
                    $itemTotal -= $child->getBaseAffiliateDiscountAmount();
                }
                if ($isCalculateTax) {
                    $itemTotal += ($child->getBaseTaxAmount() + $child->getData('base_discount_tax_compensation_amount'));
                }

                $total += $itemTotal;
            }
        } else {
            $total = $item->getBaseRowTotal() - $item->getBaseDiscountAmount();
            if ($isCalculateAffDiscount) {
                $total -= $item->getBaseAffiliateDiscountAmount();
            }
            if ($isCalculateTax) {
                $total += ($item->getBaseTaxAmount() + $item->getData('base_discount_tax_compensation_amount'));
            }
        }

        return $total;
    }

    /**
     * @param      $items
     * @param      $quote
     * @param      $isCalculateShipping
     * @param      $isCalculateTax
     * @param bool $isCalculateAffDiscount
     *
     * @return int|mixed
     */
    public function getTotalOnCart(
        $items,
        $quote,
        $isCalculateShipping,
        $isCalculateTax,
        $isCalculateAffDiscount = true,
        $isCalculateCommision = false
    ) {
        $total = 0;
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                /** @var Item $child */
                foreach ($item->getChildren() as $child) {
                    $total += $this->getItemTotalForDiscount($child, $isCalculateTax, $isCalculateAffDiscount);
                }
            } else {
                $total += $this->getItemTotalForDiscount($item, $isCalculateTax, $isCalculateAffDiscount);
            }
        }

        if ($isCalculateShipping) {
            $total += $this->getShippingTotalForDiscount($quote, $isCalculateAffDiscount);
        }

        if ($isCalculateTax && $isCalculateCommision) {
            $total += $this->_total->getBaseTaxAmount();
        }

        return $total;
    }

    /**
     * @param $items
     *
     * @return float
     */
    public function getDiscountOnCampaign($items)
    {
        $discount = 0;

        foreach ($items as $item) {
            $discount += $item->getAffiliateDiscountAmount();
        }

        return $discount;
    }

    /**
     * Round price or commission
     *
     * @param float $price
     * @param string $type
     *
     * @return float
     */
    public function round($price, $type = 'regular')
    {
        if ($price) {
            if (!isset($this->_calculators[$type])) {
                $this->_calculators[$type] = $this->_calculatorFactory->create();
            }
            $price = $this->_calculators[$type]->deltaRound($price);
        }

        return $price;
    }

    /**
     * @param      $value
     * @param bool $format
     * @param bool $includeContainer
     * @param null $scope
     *
     * @return float|string
     */
    public function convertPrice($value, $format = true, $includeContainer = true, $scope = null)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat(
                $value,
                $includeContainer,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $scope
            )
            : $this->priceCurrency->convert($value, $scope);
    }

    /**
     * @param       $object ($invoice | $creditmemo )
     * @param array $fields
     */
    public function calculateAffiliateDiscount($object, $fields = [])
    {
        $order                   = $object->getOrder();
        $affiliateDiscounted     = $order->getData($fields[0]);
        $baseAffiliateDiscounted = $order->getData($fields[1]);
        $totalDiscountAmount     = 0;
        $baseTotalDiscountAmount = 0;
        $addShippingDiscount     = true;
        if ($object instanceof Invoice) {
            foreach ($object->getOrder()->getInvoiceCollection() as $previousInvoice) {
                if ($previousInvoice->getDiscountAmount()) {
                    $addShippingDiscount = false;
                }
            }
        } else {
            $addShippingDiscount = false;
            if ($order->getShippingRefunded() < $order->getShippingAmount()) {
                if (abs(($object->getShippingAmount() + $order->getShippingRefunded())
                        - $order->getShippingAmount()) < 0.00001) {
                    $addShippingDiscount = true;
                }
            }
        }

        if ($addShippingDiscount) {
            $totalDiscountAmount     += $order->getAffiliateDiscountShippingAmount();
            $baseTotalDiscountAmount += $order->getBaseAffiliateDiscountShippingAmount();
        }

        foreach ($object->getAllItems() as $item) {
            $orderItem        = $item->getOrderItem();
            $orderItemQty     = $orderItem->getQtyOrdered();
            $itemDiscount     = $orderItem->getAffiliateDiscountAmount();
            $itemBaseDiscount = $orderItem->getBaseAffiliateDiscountAmount();
            if ($orderItemQty && $itemBaseDiscount > 0 && $item->getQty() > 0) {
                $itemPercent      = $item->getQty() / $orderItemQty;
                $itemBaseDiscount = $object->roundPrice($itemPercent * $itemBaseDiscount, 'aff_base');
                $itemDiscount     = $object->roundPrice($itemPercent * $itemDiscount, 'aff');

                $item->setBaseAffiliateDiscountAmount($itemBaseDiscount)
                    ->setAffiliateDiscountAmount($itemDiscount);
                $totalDiscountAmount     += $itemDiscount;
                $baseTotalDiscountAmount += $itemBaseDiscount;
            }
        }

        $order->setData($fields[0], $affiliateDiscounted + $totalDiscountAmount);
        $order->setData($fields[1], $baseAffiliateDiscounted + $baseTotalDiscountAmount);

        $object->setBaseAffiliateDiscountAmount($baseTotalDiscountAmount);
        $object->setAffiliateDiscountAmount($totalDiscountAmount);

        $object->setBaseGrandTotal($object->getBaseGrandTotal() - $baseTotalDiscountAmount);
        $object->setGrandTotal($object->getGrandTotal() - $totalDiscountAmount);
    }

    /**
     * @param $totalTierCommission
     * @param $tierId
     * @param $tierCommission
     */
    public function setTierCommission(&$totalTierCommission, $tierId, $tierCommission)
    {
        if (isset($totalTierCommission[$tierId])) {
            $totalTierCommission[$tierId] += $tierCommission;
        } else {
            $totalTierCommission[$tierId] = $tierCommission;
        }
    }

    /**
     * @param      $totalTierCommission
     * @param      $commission
     * @param null $percentQty
     */
    public function setTotalTierCommission(&$totalTierCommission, $commission, $percentQty = null)
    {
        foreach ($commission as $tierId => $tierCommission) {
            if ($percentQty) {
                $tierCommission = $percentQty * $tierCommission;
            }

            $this->setTierCommission($totalTierCommission, $tierId, $tierCommission);
        }
    }

    /**
     * @param $commission
     *
     * @return array
     */
    public function getTotalTierCommission($commission)
    {
        $totalTierCommission = [];
        if (is_array($commission)) {
            foreach ($commission as $campaign) {
                foreach ($campaign as $tierId => $tierCommission) {
                    $this->setTierCommission($totalTierCommission, $tierId, $tierCommission);
                }
            }
        }

        return $totalTierCommission;
    }

    /**
     * @param $commission
     *
     * @return array
     */
    public function parseCommissionOnTier($commission)
    {
        $commission = $this->unserialize($commission);
        $commission = $this->getTotalTierCommission($commission);

        return $commission;
    }

    /**
     * @param $item
     * @param $field
     * @param $action
     *
     * @return $this|array
     */
    public function calculateCommissionOrder($item, $field, $action)
    {
        $totalTierCommission = [];
        if ($item->getOrigData('entity_id') === null && $item->getAffiliateCommission()) {
            $orderCommission        = $this->parseCommissionOnTier($item->getAffiliateCommission());
            $orderCommissionEarned  = $this->unserialize($item->getData($field));
            $isAddHoldingCommission = false;
            $orderCommissionHolding = '';
            if ((string) $field === 'affiliate_commission_refunded') {
                $isAddHoldingCommission = true;
                $orderCommissionHolding = $this->unserialize($item->getAffiliateCommissionHoldingRefunded());
                if (!is_array($orderCommissionHolding)) {
                    $orderCommissionHolding = [];
                }
            }
            if (!is_array($orderCommissionEarned)) {
                $orderCommissionEarned = [];
            }

            $orderItem = $item;
            if ($orderItem->getParentItemId()) {
                return $this;
            }
            if ($orderItem->getAffiliateCommission()) {
                $itemCommission   = $this->unserialize($orderItem->getAffiliateCommission());
                $account_id       = (int) array_key_last($this->getTotalTierCommission($itemCommission));
                $transactionQty   = 0;
                $transactionItems = $this->transactionFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('order_id', $item->getOrderId())
                    ->addFieldToFilter('order_item_id', $item->getItemId())
                    ->getItems();
                if (count($transactionItems)) {
                    foreach ($transactionItems as $transactionItem) {
                        if ($action === 'order/refund' && $transactionItem->getStatus() !== "3") {
                            $transactionItem->complete($transactionItem->getOrderItemId());
                        }
                        if ((int) $transactionItem->getAccountId() === $account_id) {
                            if ($transactionItem->getAction() === 'order/refund' && $action === 'order/refund') {
                                $transactionQty += (int) $transactionItem->getProductQty();
                            }
                            if ($transactionItem->getAction() === 'order/invoice' && $action === 'order/invoice') {
                                $transactionQty += (int) $transactionItem->getProductQty();
                            }
                        }
                    }
                }
                if ($action === 'order/invoice') {
                    if ($this->getEarnCommissionInvoiceAfter($item->getStoreId())) {
                        $qty = $item->getQtyInvoiced() - $transactionQty;
                    } else {
                        $qty = $item->getQtyShipped() - $transactionQty;
                    }
                } else {
                    $qty = $transactionQty;
                    if ($item->getQtyRefunded() > $transactionQty) {
                        $qty = $item->getQtyRefunded() - $transactionQty;
                    }
                }
                if ($itemCommission && $qty > 0) {
                    $totalItemCommission = $this->getTotalTierCommission($itemCommission);
                    foreach ($totalItemCommission as $tierId => $tierCommission) {
                        $tierCommission = ($qty / $item->getQtyOrdered()) * $tierCommission;

                        if (!isset($orderCommissionEarned[$tierId])) {
                            $orderCommissionEarned[$tierId] = 0;
                        }

                        if ($orderCommissionEarned[$tierId] + $tierCommission > $orderCommission[$tierId]) {
                            $tierCommission = $orderCommission[$tierId] - $orderCommissionEarned[$tierId];
                        }

                        $orderCommissionEarned[$tierId] += $tierCommission;
                        if ($isAddHoldingCommission) {
                            if (!isset($orderCommissionHolding[$tierId])) {
                                $orderCommissionHolding[$tierId] = 0;
                            }
                            $orderCommissionHolding[$tierId] += $tierCommission;
                        }
                        $this->setTierCommission($totalTierCommission, $tierId, $tierCommission);
                    }
                    $item->setQtyAffiliate($qty);
                    $this->createTransactionByAction($item, $totalTierCommission, $action);
                }
            }
            $item->setData($field, $this->serialize($orderCommissionEarned));
            if ($isAddHoldingCommission) {
                $item->setAffiliateCommissiozznHoldingRefunded($this->serialize($orderCommissionHolding));
            }
            $item->save();
        }

        return $totalTierCommission;
    }

    /**
     * @param $item
     * @param $totalTierCommission
     * @param $action
     *
     * @return $this
     */
    public function createTransactionByAction($item, $totalTierCommission, $action)
    {
        if (is_array($totalTierCommission) && count($totalTierCommission)) {
            foreach ($totalTierCommission as $id => $com) {
                $account = $this->accountFactory->create()->load($id);
                if ($account->getId()) {
                    $item->setCommissionAmount($com);
                    try {
                        $this->transactionFactory->create()->createTransaction($action, $account, $item);
                    } catch (Exception $e) {
                        $this->_messageManager->addErrorMessage(__('Something went wrong when creating transaction!'));
                    }
                }
            }
        }

        return $this;
    }
}
