<?php
namespace Unveels\OrderDetails\Plugin\Model\Checkout;

use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Session\SessionManagerInterface;

class PaymentInformationManagementPlugin
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

    public function aroundSavePaymentInformation(
        PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/PaymentPluginCustom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Payment Plugin');
    
        if ($billingAddress && ($extensionAttributes = $billingAddress->getExtensionAttributes())) {
            $logger->info(print_r($extensionAttributes, true));
    
            $customField = $extensionAttributes->getCustomField();
            if ($customField) {
                $transformedCustomField = $this->transformCustomField($customField);
                $logger->info($transformedCustomField);
                $this->session->setCustomFieldData($transformedCustomField);
            }
        } else {
            $logger->info('Billing address or extension attributes not found.');
        }
    
        return $proceed($cartId, $paymentMethod, $billingAddress);
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
