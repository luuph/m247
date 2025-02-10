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
 * @copyright  Copyright (c) 2019-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Model;

use Bss\RewardPoint\Api\Data\EarnPointInterfaceFactory;
use Bss\RewardPoint\Api\Data\UpdateItemDetailsInterfaceFactory;
use Bss\RewardPoint\Helper\Data;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\StoreManagerInterface;

class UpdateQuoteTotal implements \Bss\RewardPoint\Api\QuoteTotalsInterface
{
    /**
     * @var QuoteRepository
     */
    protected $quote;

    /**
     * @var Total
     */
    protected $total;

    /**
     * @var UpdateItemDetailsInterfaceFactory
     */
    private $updateItemDetails;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalRepository;

    /**
     * @var EarnPointInterfaceFactory
     */
    private $earnPointInterfaceFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ApplyPoint
     */
    protected $applyPoint;

    /**
     * UpdateQuoteTotal constructor.
     *
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param EarnPointInterfaceFactory $earnPointInterfaceFactory
     * @param UpdateItemDetailsInterfaceFactory $updateItemDetails
     * @param Total $total
     * @param QuoteRepository $quote
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param \Bss\RewardPoint\Model\ApplyPoint $applyPoint
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentMethodManagement,
        CartTotalRepositoryInterface $cartTotalRepository,
        EarnPointInterfaceFactory $earnPointInterfaceFactory,
        UpdateItemDetailsInterfaceFactory $updateItemDetails,
        Total $total,
        QuoteRepository $quote,
        Data $helper,
        StoreManagerInterface $storeManager,
        \Bss\RewardPoint\Model\ApplyPoint $applyPoint
    ) {
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->earnPointInterfaceFactory = $earnPointInterfaceFactory;
        $this->updateItemDetails = $updateItemDetails;
        $this->total = $total;
        $this->quote = $quote;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->applyPoint = $applyPoint;
    }

    /**
     * Update Totals from cart detail
     *
     * @param int $quoteId
     * @param int $spendPoint
     * @return \Bss\RewardPoint\Api\Data\UpdateItemDetailsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function update($quoteId, $spendPoint)
    {
        $quote = $this->quote->get($quoteId);
        $this->checkAndUsePoints($quote, $spendPoint);
        $paymentMethods = $this->paymentMethodManagement->getList($quoteId);
        $shippingMethods = $quote->getShippingAddress()->getShippingMethod();
        $totals = $this->cartTotalRepository->get($quoteId);
        $cartDetails = $this->updateItemDetails->create();
        $cartDetails->setShippingMethods($shippingMethods);
        $cartDetails->setPaymentMethods($paymentMethods);
        $cartDetails->setTotals($totals);
        return $cartDetails;
    }

    /**
     * Validate points
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $spendPoint
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkAndUsePoints($quote, $spendPoint)
    {
        $response = $this->applyPoint->calculatorApplyPoint($spendPoint, $quote);
        if ($response['status_message'] !== 'success' || isset($response['cancel'])) {
            $errorMessage = $response['message'];
            throw new \Magento\Framework\Exception\LocalizedException($errorMessage);
        }
    }

    /**
     * Apply Earn point to quote
     *
     * @param int $quoteId
     * @return \Bss\RewardPoint\Api\Data\EarnPointInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function applyEarnPoints($quoteId)
    {
        $quote = $this->quote->get($quoteId);
        $store = $this->storeManager->getStore($quote->getStoreId());
        $websiteId = $store->getWebsiteId();

        $postEarnPoint = $this->earnPointInterfaceFactory->create();
        if (!$quote->getCustomer()->getId() || $quote->getIsMultiShipping() || !$this->helper->isActive($websiteId)) {
            $postEarnPoint->setStatus(false);
            $postEarnPoint->setEarnPoint(0);
            return $postEarnPoint;
        }

        if (!$this->helper->isEarnOrderPaidbyPoint($websiteId) && $quote->getSpendPoints() > 0) {
            $postEarnPoint->setStatus(false);
            $postEarnPoint->setEarnPoint(0);
            return $postEarnPoint;
        }

        $points = $quote->getEarnPoints();
        $postEarnPoint->setEarnPoint($points);
        $postEarnPoint->setStatus(true);
        return $postEarnPoint;
    }
}
