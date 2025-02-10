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

use Bss\RewardPoint\Api\RewardPointManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;

class RewardPointManagement implements RewardPointManagementInterface
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var RateFactory
     */
    protected $rateFactory;

    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $helper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSession;

    /**
     * RewardPointManagement constructor.
     *
     * @param \Magento\Checkout\Model\SessionFactory $checkoutSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param TransactionFactory $transactionFactory
     * @param RateFactory $rateFactory
     * @param \Bss\RewardPoint\Helper\Data $helper
     */
    public function __construct(
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory,
        \Bss\RewardPoint\Model\RateFactory $rateFactory,
        \Bss\RewardPoint\Helper\Data $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->jsonHelper = $jsonHelper;
        $this->transactionFactory = $transactionFactory;
        $this->rateFactory = $rateFactory;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function getAllModuleConfig($storeview = null)
    {
        $configs = $this->getGroupConfig($storeview, 'bssrewardpoint');
        return ['module_configs' => $configs];
    }

    /**
     * Apply point
     *
     * @param int $spendPoint
     * @param \Magento\Quote\Model\Quote|null $quote
     * @return RewardPointManagement|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function apply($spendPoint, $quote = null)
    {
        $response = [];

        if ($quote == null) {
            $quote = $this->checkoutSession->create()->getQuote();
        }
        $store = $this->storeManager->getStore($quote->getStoreId());
        $websiteId = $store->getWebsiteId();
        $customerId = $quote->getCustomerId();
        $customerGroupId = $quote->getCustomerGroupId();

        $total_points = $this->transactionFactory->create()->loadByCustomer($customerId, $websiteId)->getPointBalance();

        $rate = $this->rateFactory->create()->fetch(
            $customerGroupId,
            $websiteId
        );

        $maximum_spend_point = (int)$this->helper->getMaximumPointCanSpendPerOrder();
        $response['message'] = __('Successfully!');
        $response['status_message']  = 'success';
        if ($spendPoint < 0 || !$quote->getId()) {
            $response['message'] = __('Something went wrong. Please enter a value again');
            $response['status_message']  = 'error';
            $response['status']  = true;
        } elseif ($spendPoint == 0) {
            $quote->setSpendPoints($spendPoint);
            $quote->collectTotals();
            $quote->save();
            $response['message'] = __('Successfully cancel!');
            $response['status'] = true;
            $response['spend_point'] = 0;
            $response['amount'] = 0;
            $response['pointLeft'] = $total_points;
        } else {
            $spend_point = $spendPoint;
            if ($maximum_spend_point > 0 && $spendPoint > $maximum_spend_point) {
                $response['message'] = __("You can't use more reward point than you have");
                $response['status_message']  = 'warning';
                $spend_point = $maximum_spend_point;
            }

            if ($spend_point > $total_points) {
                $response['message'] = __("You can't use more reward point than the order amount.");
                $response['status_message']  = 'warning';
                $spend_point = $total_points;
            }

            $baseCurrency = $rate->getBasecurrencyToPointRate() ?? 1;
            $base_amount = $this->priceCurrency->round($spend_point/$baseCurrency);

            $quote->setSpendPoints($spend_point);
            $quote->collectTotals();
            $quote->save();
            $baseRwpAmount = $this->priceCurrency->round($quote->getBaseRwpAmount());

            if ($base_amount > $baseRwpAmount) {
                $response['status_message']  = 'warning';
                $response['message'] = __("You can't use more reward point than the order amount.");
            }

            $spend_point = $quote->getSpendPoints();
            $pointLeft = $total_points - $spend_point;
            $response['status'] = true;
            $response['spend_point'] = $spend_point;
            $response['amount'] = $quote->getRwpAmount();
            $response['pointLeft'] = $pointLeft;
        }

        return $this->jsonHelper->jsonEncode($response);
    }

    /**
     * Get config
     *
     * @param int $storeId
     * @param string $path
     * @return array
     */
    protected function getGroupConfig($storeId, $path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
