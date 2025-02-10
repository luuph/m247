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
namespace Bss\RewardPoint\Block\Cart;

use Bss\RewardPoint\Helper\Data as HelperData;
use Bss\RewardPoint\Model\RateFactory;
use Bss\RewardPoint\Model\TransactionFactory;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class spend point
 *
 * Bss\RewardPoint\Block\Cart
 */
class SpendPoint extends Template
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\RewardPoint\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Bss\RewardPoint\Model\RateFactory
     */
    protected $rateFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * SpendPoint constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Data $priceHelper
     * @param StoreManagerInterface $storeManager
     * @param Http $request
     * @param Data $helper
     * @param TransactionFactory $transactionFactory
     * @param RateFactory $rateFactory
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        \Bss\RewardPoint\Helper\Data $helper,
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory,
        \Bss\RewardPoint\Model\RateFactory $rateFactory,
        HelperData $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->priceHelper = $priceHelper;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->helper = $helper;
        $this->transactionFactory = $transactionFactory;
        $this->rateFactory = $rateFactory;
        $this->helperData = $helperData;
    }

    /**
     * Get rewardpoint total
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRewardPointsTotal()
    {
        $spend_point = $this->getQuote()->getSpendPoints();
        $pointLeft = (int)$this->getPointBalance() - (int)$spend_point;
        return $pointLeft;
    }

    /**
     * Get point balance
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPointBalance()
    {
        $transaction = $this->transactionFactory->create()->loadByCustomer(
            $this->getCustomerId(),
            $this->getWebsiteId()
        );
        return $transaction->getPointBalance();
    }

    /**
     * Get currency
     *
     * @return float|string
     */
    public function getCurrency()
    {
        return $this->priceHelper->currency(1, true, false);
    }

    /**
     * Get rate currency to point
     *
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRateCurrencytoPoint()
    {
        $rate = $this->rateFactory->create()->fetch(
            $this->getCustomerGroupId(),
            $this->getWebsiteId()
        );
        return $rate->getBasecurrencyToPointRate() ? round($rate->getBasecurrencyToPointRate()) : 1;
    }

    /**
     * Get spend points
     *
     * @return int
     */
    public function getSpendPoints()
    {
        return (int)$this->getQuote()->getSpendPoints();
    }

    /**
     * Get customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->getQuote()->getCustomerGroupId();
    }

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getQuote()->getCustomerId();
    }

    /**
     * Get website id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId()
    {
        $store = $this->storeManager->getStore($this->getQuote()->getStoreId());
        return $store->getWebsiteId();
    }

    /**
     * Get quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->create()->getQuote();
    }

    /**
     * Is diaplay
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isDisplay()
    {
        $minimum = (int)$this->helper->getPointsThreshold($this->getWebsiteId());
        if ($minimum <= (int)$this->getPointBalance() && $this->helper->isActive()) {
            return true;
        }
        return false;
    }

    /**
     * Is point slider
     *
     * @return bool
     */
    public function isPointSlider()
    {
        return $this->helper->isPointSlider();
    }

    /**
     * Is page paypal
     *
     * @return bool
     */
    public function isPagePayPalReview()
    {
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();
        $route = $this->request->getRouteName();
        $handle = $route . '_' . $controller . '_' . $action;
        return $handle == 'paypal_express_review';
    }

    /**
     * Check ver magento
     *
     * @return bool
     */
    public function checkMagentoHigherV244()
    {
        return $this->helperData->checkMagentoHigherV244();
    }
}
