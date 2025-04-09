<?php

namespace Bss\GuestToCustomer\Plugin\Checkout;

class LayoutProcessor
{
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {

            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

            // Thêm validation vào firstname
            if (isset($fields['firstname'])) {
                $fields['firstname']['validation']['validate-custom-name'] = true;
            }

            // Thêm validation vào lastname
            if (isset($fields['lastname'])) {
                $fields['lastname']['validation']['validate-custom-name'] = true;
            }
        }
        return $jsLayout;
    }
}
