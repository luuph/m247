<?php

namespace Unveels\OrderDetails\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;

class AfterOrderPlaceObserver implements ObserverInterface
{
    private $session;
    private $orderAddressRepository;
    private $customerSession;

    public function __construct(
        SessionManagerInterface $session,
        OrderAddressRepositoryInterface $orderAddressRepository,
        CustomerSession $customerSession
    ) {
        $this->session = $session;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->customerSession = $customerSession;
    }

    public function execute(Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom_after_order.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info('Observer executed: AfterOrderPlaceObserver triggered.');
        $customFieldData = $this->session->getCustomFieldData();

        // if ($this->customerSession->isLoggedIn()) {
        //     $logger->info('Customer is logged in. Skipping processing for logged-in customers.');
        //     return;
        // }

        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            $logger->info('No order data found in event.');
            return;
        }

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $customFieldData = $this->session->getCustomFieldData();
        if (!$customFieldData) {
            $logger->info('No custom field data found in session. Skipping update.');
            return;
        }

        $logger->info('Custom Field Data from Session: ' . print_r($customFieldData, true));

        if ($billingAddress) {
            $this->processAddress($billingAddress, $customFieldData, $logger);
        } else {
            $logger->info('No Billing Address found.');
        }

        if ($shippingAddress) {
            $this->processAddress($shippingAddress, $customFieldData, $logger);
        } else {
            $logger->info('No Shipping Address found.');
        }

        $this->session->unsCustomFieldData();
        $logger->info('Session data cleared.');
    }

    private function processAddress($address, $customFieldData, $logger)
    {
        $logger->info('Processing Address: ' . print_r($address->getData(), true));

        $customerAddressAttribute = $address->getData('customer_address_attribute');
        if (!$customerAddressAttribute) {
            $logger->info('customer_address_attribute is missing. Setting data from session.');

            $address->setData('customer_address_attribute', $customFieldData);

            try {
                $this->orderAddressRepository->save($address);
                $logger->info('Address updated with session customer_address_attribute.');
            } catch (\Exception $e) {
                $logger->error('Error while updating address: ' . $e->getMessage());
            }
        } else {
            $logger->info('customer_address_attribute already exists: ' . $customerAddressAttribute);
        }
    }
}
