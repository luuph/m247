<?php
namespace Unveels\OrderDetails\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\QuoteFactory;

class BeforePlaceOrderObserver implements ObserverInterface
{
    private $session;
    private $quoteFactory;

    public function __construct(
        SessionManagerInterface $session,
        QuoteFactory $quoteFactory
    ) {
        $this->session = $session;
        $this->quoteFactory = $quoteFactory;
    }

    public function execute(Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/BeforePlaceOrderObserver.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info('Observer executed: BeforePlaceOrderObserver triggered.');

        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            $logger->info('No order data found in event.');
            return;
        }

        $quoteId = $order->getQuoteId();
        $logger->info('Order Quote ID: ' . $quoteId);

        if ($quoteId) {
            $quote = $this->quoteFactory->create()->load($quoteId);

            if ($quote->getId()) {
                $logger->info('Quote loaded successfully.');

                if ($quote->getCustomerIsGuest()) {
                    $logger->info('Guest user detected.');

                    $shippingAddress = $quote->getShippingAddress();
                    $billingAddress = $quote->getBillingAddress();
                    $shippingData = $shippingAddress->getData();

                    $logger->info('Shipping Address: ' . print_r($shippingData, true));
                    $logger->info('Billing Address: ' . print_r($billingAddress->getData(), true));

                    $extensionAttributes = $shippingAddress->getExtensionAttributes();
                    if (isset($shippingData['customer_address_attribute'])) {
                        $originalData = json_decode($shippingData['customer_address_attribute'], true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $transformedData = [];
                            foreach ($originalData as $attribute) {
                                $attributeCode = $attribute['attribute_code'] ?? null;
                                $value = $attribute['value'] ?? null;
                                $label = $attribute['label'] ?? ucfirst($attributeCode);

                                if ($attributeCode) {
                                    if ($attributeCode === 'address_title') {
                                        $transformedData[$attributeCode] = [
                                            'label' => 'Address Title',
                                            'value' => $label
                                        ];
                                    } else {
                                        $transformedData[$attributeCode] = [
                                            'value' => $value,
                                            'label' => $label
                                        ];
                                    }
                                }
                            }

                            $logger->info('Transformed Customer Address Attributes: ' . print_r($transformedData, true));

                            // Save the transformed data to the session
                            $this->session->setCustomFieldData(json_encode($transformedData));
                            $logger->info('Transformed data saved to session.');
                        } else {
                            $logger->info('Failed to decode customer_address_attribute JSON.');
                        }
                    } else {
                        $logger->info('No customer_address_attribute data found.');
                    }

                    $logger->info('Quote ID: ' . $quote->getId());
                } else {
                    $logger->info('User is not a guest. Skipping custom_field session save.');
                }
            } else {
                $logger->info('Quote could not be loaded.');
            }
        } else {
            $logger->info('Quote ID is missing in the order.');
        }
    }
}
