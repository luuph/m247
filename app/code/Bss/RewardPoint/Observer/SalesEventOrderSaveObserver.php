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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\RewardPoint\Observer;

use Bss\RewardPoint\Helper\Data;
use Bss\RewardPoint\Helper\RewardCustomAction;
use Bss\RewardPoint\Model\RateFactory;
use Bss\RewardPoint\Model\TransactionFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\Observer;
use Bss\RewardPoint\Model\Config\Source\TransactionActions;
use Psr\Log\LoggerInterface;

class SalesEventOrderSaveObserver implements ObserverInterface
{
    /**
     * @var \Bss\RewardPoint\Helper\RewardCustomAction
     */
    protected $helperCustomAction;

    /**
     * @var \Bss\RewardPoint\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Bss\RewardPoint\Helper\Data
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\RewardPoint\Model\RateFactory
     */
    protected $rateFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * SalesEventOrderSaveObserver constructor.
     *
     * @param Data $helper
     * @param RewardCustomAction $helperCustomAction
     * @param TransactionFactory $transactionFactory
     * @param RateFactory $rateFactory
     * @param StoreManagerInterface $storeManager
     * @param QuoteFactory $quoteFactory
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Bss\RewardPoint\Helper\Data $helper,
        \Bss\RewardPoint\Helper\RewardCustomAction $helperCustomAction,
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory,
        \Bss\RewardPoint\Model\RateFactory $rateFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper              = $helper;
        $this->helperCustomAction  = $helperCustomAction;
        $this->transactionFactory  = $transactionFactory;
        $this->rateFactory  = $rateFactory;
        $this->storeManager        = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (
            $order->getCustomerIsGuest()
            || $order->isCanceled()
            || !$this->helper->isActive()
            || !$order->getQuoteId()
        ) {
            return $this;
        }
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());

        $options = [
                'customerId' => $order->getCustomerId(),
                'action_id'  => $order->getId(),
                'storeId'    => $order->getStoreId()
               ];

        $this->helperCustomAction->processCustomRule(TransactionActions::FIRST_ORDER, $options);

        if ($order->getState() == 'complete' && !$this->transactionFactory->create()->checkOrderAddPoint($order->getId())){
            $earn_point = $quote->getEarnPoints();
            $this->saveTransaction($quote, $order->getId(), $earn_point);
        } elseif ($order->getState() == 'new') {
            $amount = $quote->getRwpAmount();
            $baseAmount = $quote->getBaseRwpAmount();
            if ($baseAmount && $amount) {
                if ($this->checkoutSession->getSpentPoint()){
                    $spend_point = $quote->getSpendPoints()*(-1);
                    $this->saveTransaction($quote, $order->getId(), $spend_point);
                    $this->checkoutSession->unsSpentPoint();
                } else {
                    $this->checkoutSession->setSpentPoint(true);
                    $order->setRwpAmount($amount)
                        ->setBaseRwpAmount($baseAmount)
                        ->save();
                }
            }
        }

        return $this;
    }

    /**
     * Save transaction
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $orderId
     * @param int $point
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function saveTransaction($quote, $orderId, $point)
    {
        if ((int)$point !== 0) {
            $transaction  = $this->transactionFactory->create();

            $customerId = $quote->getCustomerId();
            $websiteId = $this->storeManager->getStore($quote->getStoreId())->getWebsiteId();

            $data = [
                'website_id'   => $websiteId,
                'customer_id'  => $customerId,
                'point'        => $point,
                'action_id'    => $orderId,
                'action'       => TransactionActions::ORDER,
                'created_at'   => $this->helper->getCreateAt(),
                'created_by'   => $quote->getCustomerEmail()
            ];

            if ($point > 0) {
                $data['note']       = $quote->getRwpNote();
                $data['is_expired'] = (bool)$this->helper->getExpireDay($websiteId);
                $data['expires_at'] = $this->helper->getExpireDay($websiteId);
            } else {
                $customerGroupId = $quote->getCustomerGroupId();
                $rate = $this->rateFactory->create()->fetch(
                    $customerGroupId,
                    $websiteId
                );

                $data['amount'] = $quote->getBaseRwpAmount();
                $data['base_currrency_code'] = $quote->getBaseCurrencyCode() ?? '';
                $data['basecurrency_to_point_rate'] = $rate->getBasecurrencyToPointRate() ?? 1;
            }

            try {
                $transaction->setData($data);
                if ($point < 0) {
                    $transaction->updatePointUsed();
                }
                $transaction->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
