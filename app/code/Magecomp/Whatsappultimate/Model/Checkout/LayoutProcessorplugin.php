<?php
namespace Magecomp\Whatsappultimate\Model\Checkout;
 
class LayoutProcessorplugin
{
    protected $_helper;
    public function __construct(
        \Magecomp\Whatsappcountryflag\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['notice'] = __('Enter WhatsApp Number With Country code & without any sign.');

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children'])) {
            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'] as $key => $payment) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']['telephone']['notice'] = __('Enter WhatsApp Number With Country code & without any sign.');
            }

        }
        return $jsLayout;
    }
}
