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
namespace Bss\RewardPoint\Controller\Cart;

use Bss\RewardPoint\Helper\Data;
use Magento\Checkout\Model\CartFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class Update PostPayPal
 *
 * Bss\RewardPoint\Controller\Cart
 */
class UpdatePostPayPal extends Action
{
    /**
     * @var CartFactory
     */
    protected $cartFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\RewardPoint\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * UpdatePost constructor.
     * @param Context $context
     * @param CartFactory $cartFactory
     * @param Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
     */
    public function __construct(
        Context $context,
        CartFactory $cartFactory,
        Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\RewardPoint\Model\TransactionFactory $transactionFactory
    ) {
        parent::__construct($context);
        $this->cartFactory = $cartFactory;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * Execute
     *
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $spendPoint = (int) $this->getRequest()->getParam('spend_reward_point');
        $quote = $this->cartFactory->create()->getQuote();

        $store = $this->storeManager->getStore($quote->getStoreId());
        $websiteId = $store->getWebsiteId();
        $customerId = $quote->getCustomerId();

        $totalPoints = $this->transactionFactory->create()->loadByCustomer($customerId, $websiteId)->getPointBalance();

        $maximumSpendPoint = (int)$this->helper->getMaximumPointCanSpendPerOrder();
        if ($spendPoint < 0 || !$quote->getId()) {
            $this->messageManager->addErrorMessage(__('Something went wrong. Please enter a value again'));
        } elseif ($spendPoint == 0) {
            $quote->setSpendPoints($spendPoint);
            $quote->collectTotals();
            $quote->save();
            $this->messageManager->addSuccessMessage(__('Successfully cancel!'));
        } else {
            if ($maximumSpendPoint > 0 && $spendPoint > $maximumSpendPoint) {
                $spendPoint = $maximumSpendPoint;
            }

            if ($spendPoint > $totalPoints) {
                $spendPoint = $totalPoints;
            }

            $quote->setSpendPoints($spendPoint);
            $quote->collectTotals();
            $quote->save();
            $this->messageManager->addSuccessMessage(__('Successfully!'));
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
