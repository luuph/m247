<?php
namespace Unveels\SortFields\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;

class LayoutProcessorPlugin
{
    public function afterProcess(
        LayoutProcessor $subject,
        array $jsLayout
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/LayoutProcessorPluginASAD.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Plugin executedddd');

        // Ensure shipping address fields exist
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children'])) {
            $fields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children'];

            $logger->info('Sorting fields...');
            
            // Define sort order
            if (isset($fields['lastname'])) {
                $fields['lastname']['sortOrder'] = 20;
            }
            if (isset($fields['country_id'])) {
                $fields['country_id']['sortOrder'] = 30;
            }
            if (isset($fields['region_id'])) {
                $fields['region_id']['sortOrder'] = 40;
            }
            if (isset($fields['city'])) {
                $fields['city']['sortOrder'] = 50;
            }
            if (isset($fields['postcode'])) {
                $fields['postcode']['sortOrder'] = 60;
            }
            if (isset($fields['street'])) {
                $fields['street']['sortOrder'] = 700000000000;
            }
            
            $logger->info('Sort order updated');
        }

        return $jsLayout;
    }
}
