<?php

namespace Unveels\CheckoutCustomization\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Zend_Log;
use Zend_Log_Writer_Stream;
use Exception;

class SameBillingShipping implements ObserverInterface
{
    protected $orderRepository;
    protected $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;

        // Set up custom logger
        $writer = new Zend_Log_Writer_Stream(BP . '/var/log/SameBillingShippingEND2.log');
        $this->logger = new Zend_Log();
        $this->logger->addWriter($writer);
    }

    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

            if (!$order) {
                $this->logger->info("❌ ERROR: Order not found in observer!");
                return;
            }

            $this->logger->info("✅ Observer Triggered - Processing Order: " . $order->getIncrementId());

            $cartItems = $order->getAllVisibleItems();
            $hasGiftCard = false;

            foreach ($cartItems as $item) {
                $this->logger->info("🔍 Product Type: " . $item->getProductType());
                if ($item->getProductType() === "bss_giftcard") {
                    $hasGiftCard = true;
                }
            }

            if ($hasGiftCard) {
                $this->logger->info("🎁 Gift Card Found - Billing Address NOT changed.");
                return;
            }


            $shippingAddress = $order->getShippingAddress();
            $billingAddress = $order->getBillingAddress();

            // Check first if both addresses exist
            if (!$shippingAddress || !$billingAddress) {
            $this->logger->info("❌ ERROR: Missing address data.");
             return;
             }
            
            // Get shipping and billing addresses
            $regionid = $shippingAddress->getRegionId();
            $region = $shippingAddress->getRegion();
            $postcode = $shippingAddress->getPostcode();
            $lastname = $shippingAddress->getLastname();
            $street = $shippingAddress->getStreet();
            $city = $shippingAddress->getCity();
            $cityId = $shippingAddress->getCityId();
            $email = $shippingAddress->getEmail();
            $telephone = $shippingAddress->getTelephone();
            $countryid = $shippingAddress->getCountryId();
            $firstname = $shippingAddress->getFirstname();
            $company = $shippingAddress->getCompany();
            $customerAddressAttribute = $shippingAddress->getCustomerAddressAttribute();

           

            // Update billing address inside try-catch
            try {
                if (!empty($regionid)) $billingAddress->setRegionId($regionid);
                if (!empty($region)) $billingAddress->setRegion($region);
                if (!empty($postcode)) $billingAddress->setPostcode($postcode);
                if (!empty($lastname)) $billingAddress->setLastname($lastname);
                if (!empty($street)) $billingAddress->setStreet($street);
                if (!empty($city)) $billingAddress->setCity($city);
                if (!empty($cityId)) $billingAddress->setCityId($cityId);
                if (!empty($email)) $billingAddress->setEmail($email);
                if (!empty($telephone)) $billingAddress->setTelephone($telephone);
                if (!empty($countryid)) $billingAddress->setCountryId($countryid);
                if (!empty($firstname)) $billingAddress->setFirstname($firstname);
                if (!empty($company)) $billingAddress->setCompany($company);
                if (!empty($customerAddressAttribute)) $billingAddress->setCustomerAddressAttribute($customerAddressAttribute);

                $billingAddress->setAddressType('billing');
                $billingAddress->setSameAsShipping(1);
                $billingAddress->setCustomerAddressId($shippingAddress->getCustomerAddressId());
                $this->logger->info("✅ Billing Address Updated BEFORE.");

                // Save updated order
                $this->orderRepository->save($order);
                $this->logger->info("✅ Billing Address Updated Successfully.");
            } catch (Exception $e) {
                $this->logger->info("❌ ERROR Updating Billing Address: " . $e->getMessage());
            }
        } catch (Exception $e) {
            $this->logger->info("❌ Fatal Error in Observer: " . $e->getMessage());
        }
    }
}
