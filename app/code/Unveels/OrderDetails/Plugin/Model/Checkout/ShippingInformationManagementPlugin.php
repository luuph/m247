<?php

namespace Unveels\OrderDetails\Plugin\Model\Checkout;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Session\SessionManagerInterface;

class ShippingInformationManagementPlugin
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

    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/ShippingPluginCustom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Shipping Plugin');
        
        $shippingAddress = $addressInformation->getShippingAddress();
        $logger->info(print_r($shippingAddress->getExtensionAttributes(), true));
        if ($shippingAddress && ($extensionAttributes = $shippingAddress->getExtensionAttributes())) {
            $customField = $extensionAttributes->getCustomField();
            if ($customField) {
                $transformedCustomField = $this->transformCustomField($customField);
                $logger->info($transformedCustomField);
                $this->session->setCustomFieldData($transformedCustomField);
            }
        }
        return [$cartId, $addressInformation];
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
