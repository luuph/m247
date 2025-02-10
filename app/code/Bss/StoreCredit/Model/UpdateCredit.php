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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Model;

use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;
use Bss\StoreCredit\Api\UpdateCreditInterface;
use Bss\StoreCredit\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateCredit
 *
 * @package Bss\StoreCredit\Model
 */
class UpdateCredit implements UpdateCreditInterface
{

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $bssStoreCreditHelper;

    /**
     * @var Bss\StoreCredit\Model\CreditFactory
     */
    private $creditFactory;

    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    private $storeCreditRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Bss\StoreCredit\Model\HistoryFactory
     */
    private $historyFactory;
    private \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface;

    /**
     * @param CreditFactory $creditFactory
     * @param Data $bssStoreCreditHelper
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param LoggerInterface $logger
     * @param HistoryFactory $historyFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        CreditFactory                                     $creditFactory,
        Data                                              $bssStoreCreditHelper,
        StoreCreditRepositoryInterface                    $storeCreditRepository,
        LoggerInterface                                   $logger,
        HistoryFactory                                    $historyFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->creditFactory = $creditFactory;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->storeCreditRepository = $storeCreditRepository;
        $this->logger = $logger;
        $this->historyFactory = $historyFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Update store credit
     *
     * @param int $customerId
     * @param int $websiteId
     * @param float $amount
     * @param string $comment
     * @return mixed
     */
    public function updateCredit($customerId, $websiteId, $amount, $comment)
    {
        $credit = $this->storeCreditRepository->get($customerId, $websiteId);
        if ($credit && $credit->getBalanceId()) {
            $amountAfter = $credit->getBalanceAmount() + $amount;
        } else {
            $amountAfter = $amount;
            $credit = $this->creditFactory->create()
                ->setWebsiteId($websiteId)
                ->setCustomerId($customerId);
        }
        return $this->changeAmountCredit($credit, $amount, $amountAfter, $comment);
    }

    /**
     * Replace store credit
     *
     * @param int $customerId
     * @param int $websiteId
     * @param float $amount
     * @param string $comment
     * @return array
     */
    public function replaceCredit($customerId, $websiteId, $amount, $comment)
    {
        $credit = $this->storeCreditRepository->get($customerId, $websiteId);
        if (!isset($credit) || !$credit->getBalanceId()) {
            $credit = $this->creditFactory->create()
                ->setWebsiteId($websiteId)
                ->setCustomerId($customerId);
        }
        return $this->changeAmountCredit($credit, $amount, $amount, $comment);
    }

    /**
     * Change amount store credit & update history
     *
     * @param \Bss\StoreCredit\Model\Credit $credit
     * @param float $amount
     * @param float $amountAfter
     * @param string $comment
     * @return array
     */
    public function changeAmountCredit($credit, $amount, $amountAfter, $comment)
    {
        $result = [];
        try {
            $credit->setBalanceAmount($amountAfter)
                ->save();
            $customer = $this->customerRepositoryInterface->getById($credit->getCustomId());
            $customerName = $customer->getFirstname()
                . ' ' . $customer->getMiddlename()
                . ' ' . $customer->getLastname();
            $data = [
                'customer_id' => $credit->getCustomId(),
                'website_id' => $credit->getWebsiteId(),
                'type' => History::TYPE_UPDATE,
                'change_amount' => $amount,
                'balance_amount' => $amountAfter,
                'comment_content' => $comment,
                'is_notified' => 0,
                'customer_name' => $customerName,
                'customer_email' => $customer->getEmail()
            ];
            $historyModel = $this->historyFactory->create();
            $historyModel->updateHistory($data);
            $result["status"] = [
                "success" => true,
                "message" => __("You have successfully updated store credit.")
            ];
        } catch (\Exception $e) {
            $result["status"] = [
                "success" => false,
                "message" => __("You did not update store credit.")
            ];
            $this->logger->critical($e->getMessage());
        }
        return $result;
    }
}
