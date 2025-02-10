<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Observer\Sales\Order\Email;

use Bss\CompanyAccount\Api\SubUserOrderRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;

/**
 * Class Sender
 *
 * @package Bss\CompanyAccount\Observer\Sales\Order\Email
 */
class Sender implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SubUserOrderRepositoryInterface
     */
    private $userOrderRepository;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Sender constructor.
     *
     * @param Registry $registry
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubUserOrderRepositoryInterface $userOrderRepository
     * @param Data $helper
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        SubUserRepositoryInterface $subUserRepository,
        SubUserOrderRepositoryInterface $userOrderRepository,
        Data $helper,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->userOrderRepository = $userOrderRepository;
        $this->subUserRepository = $subUserRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Registry sub-user email for notify to registered sub-user
     *
     * Working when module is enable and order was placed by sub-user
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $transportObject = $observer->getData('transportObject');
        $this->registry->unregister('bss_is_send_mail_to_sub_user');

        if ($this->helper->isEnable() && $transportObject) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $transportObject->getData('order');
            if (!$order) {
                return;
            }

            $subUserEmail = $this->getSubUserEmail($order);
            $this->registry->register('bss_is_send_mail_to_sub_user', $subUserEmail);
        }
    }

    private function getSubUserEmail($order) {
        $userOrder = $this->userOrderRepository->getByOrderId($order->getId());
        if ($userOrder) {
            $subUser = $this->subUserRepository->getById($userOrder->getSubId());
            if ($subUser && $subUser->getSubStatus() && $subUser->getSubEmail()) {
                return $subUser->getSubEmail();
            }
        }
        if (!$order->getCustomerId()) {
            return $order->getCustomerEmail();
        }
        $customer = $this->customerRepository->getById($order->getCustomerId());
        return $customer ? $customer->getEmail() : null;
    }
}
