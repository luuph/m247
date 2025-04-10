<?php

namespace Unveels\OrderDetails\Plugin\Model\Checkout;

use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Session\SessionManagerInterface;

class GuestPaymentInformationManagementPlugin
{   
    /**
     * @var SessionManagerInterface
     */
    private $session;

    public function __construct(
        SessionManagerInterface $session
    ) {
        $this->session = $session;
    }

    public function aroundSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/GuestPaymentPluginCustom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Guest Payment');
        $logger->info(print_r($billingAddress->getExtensionAttributes(), true));
        if ($billingAddress && ($extensionAttributes = $billingAddress->getExtensionAttributes())) {
            $customField = $extensionAttributes->getCustomField();
            if ($customField) {
                $transformedCustomField = $this->transformCustomField($customField);
                $logger->info($transformedCustomField);
                $this->session->setCustomFieldData($transformedCustomField);
            }
        }
        return $proceed($cartId, $email, $paymentMethod, $billingAddress);
    }
    
    private function transformCustomField($customField)
    {
        $originalData = json_decode($customField, true);
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

        return json_encode($transformedData);
    }
}
