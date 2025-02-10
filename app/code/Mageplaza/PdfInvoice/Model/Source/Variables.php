<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_PdfInvoice
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PdfInvoice\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\Store;

/**
 * Store Contact Information source model
 */
class Variables implements ArrayInterface
{
    /**
     * Assoc array of configuration variables
     *
     * @var array
     */
    protected $_configVariables = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_configVariables = [
            [
                'value' => Store::XML_PATH_UNSECURE_BASE_URL,
                'label' => __('Base Unsecure URL'),
            ],
            ['value' => Store::XML_PATH_SECURE_BASE_URL, 'label' => __('Base Secure URL')],
            ['value' => 'trans_email/ident_general/name', 'label' => __('General Contact Name')],
            ['value' => 'trans_email/ident_general/email', 'label' => __('General Contact Email')],
            ['value' => 'trans_email/ident_sales/name', 'label' => __('Sales Representative Contact Name')],
            ['value' => 'trans_email/ident_sales/email', 'label' => __('Sales Representative Contact Email')],
            ['value' => 'trans_email/ident_custom1/name', 'label' => __('Custom1 Contact Name')],
            ['value' => 'trans_email/ident_custom1/email', 'label' => __('Custom1 Contact Email')],
            ['value' => 'trans_email/ident_custom2/name', 'label' => __('Custom2 Contact Name')],
            ['value' => 'trans_email/ident_custom2/email', 'label' => __('Custom2 Contact Email')],
            ['value' => 'general/store_information/name', 'label' => __('Store Name')],
            ['value' => 'general/store_information/phone', 'label' => __('Store Phone Number')],
            ['value' => 'general/store_information/hours', 'label' => __('Store Hours')],
            ['value' => 'general/store_information/country_id', 'label' => __('Country')],
            ['value' => 'general/store_information/region_id', 'label' => __('Region/State')],
            ['value' => 'general/store_information/postcode', 'label' => __('Zip/Postal Code')],
            ['value' => 'general/store_information/city', 'label' => __('City')],
            ['value' => 'general/store_information/street_line1', 'label' => __('Street Address 1')],
            ['value' => 'general/store_information/street_line2', 'label' => __('Street Address 2')],
            ['value' => 'general/store_information/merchant_vat_number', 'label' => __('VAT Number')],
        ];
    }

    /**
     * Retrieve option array of store contact and order variables
     *
     * @param bool $withGroup
     *
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = [];
        if ($withGroup) {
            $optionArray[] = [
                'label' => __('Store Contact Information'),
                'value' => $this->getOptionConfig($this->getData()),
            ];
            $optionArray[] = [
                'label' => __('Order Info'),
                'value' => $this->getOrderOption(),
            ];
            $optionArray[] = [
                'label' => __('Shipping Information'),
                'value' => $this->getShippingOption(),
            ];
            $optionArray[] = [
                'label' => __('Billing Information'),
                'value' => $this->getBillingOption(),
            ];
        }

        return $optionArray;
    }

    /**
     * get config option Array
     *
     * @param $variables
     *
     * @return array
     */
    public function getOptionConfig($variables)
    {
        $optionArrays = [];
        foreach ($variables as $variable) {
            $optionArrays[] = [
                'value' => '{{config path="' . $variable['value'] . '"}}',
                'label' => $variable['label'],
            ];
        }

        return $optionArrays;
    }

    /**
     * Return Order variables
     *
     * @return array
     */
    public function getOrderOption()
    {
        return [
            ['label' => __('Order Status'), 'value' => '{{var order.status}}'],
            ['label' => __('Order ID'), 'value' => '{{var order_id}}'],
            ['label' => __('Order Date'), 'value' => '{{var order_date}}'],
            ['label' => __('Payment Method'), 'value' => '{{var payment_title}}'],
            ['label' => __('Shipping Method'), 'value' => '{{var order.getShippingDescription()}}'],
            [
                'label' => __('Total Shipping Amount'),
                'value' => '{{var shipping_amount}}',
            ],
            ['label' => __('Subtotal'), 'value' => '{{var subtotal}}'],
            ['label' => __('Comment Label'), 'value' => '{{var commentLabel}}'],
            ['label' => __('Comment Text'), 'value' => '{{var commentText}}'],
            ['label' => __('Grand Total'), 'value' => '{{var grand_total}}'],
            ['label' => __('Total Due'), 'value' => '{{var total_due}}'],
            ['label' => __('Total Tax Amount'), 'value' => '{{var tax_amount}}'],
        ];
    }

    /**
     * @return array[]
     */
    public function getShippingOption()
    {
        return [
            ['label' => __('Shipping Full Name'), 'value' => '{{var shippingAddress_getFullName}}'],
            ['label' => __('Shipping First Name'), 'value' => '{{var shippingAddress.firstname}}'],
            ['label' => __('Shipping Last Name'), 'value' => '{{var shippingAddress.lastname}}'],
            ['label' => __('Shipping Name Prefix'), 'value' => '{{var shippingAddress.prefix}}'],
            ['label' => __('Shipping Middle Name'), 'value' => '{{var shippingAddress.middlename}}'],
            ['label' => __('Shipping Name Suffix'), 'value' => '{{var shippingAddress.suffix}}'],
            ['label' => __('Shipping Company'), 'value' => '{{var shippingAddress.company}}'],
            ['label' => __('Shipping Address'), 'value' => '{{var formattedShippingAddress|raw}}'],
            ['label' => __('Shipping State/Province'), 'value' => '{{var shippingAddress.getRegion()}}'],
            ['label' => __('Shipping Vat Number'), 'value' => '{{var shippingAddress.getVatId()}}'],
            ['label' => __('Shipping Fax'), 'value' => '{{var shippingAddress.getFax()}}'],
            ['label' => __('Shipping Zip/Postcode'), 'value' => '{{var shippingAddress.getPostcode()}}'],
            ['label' => __('Shipping Street Address'), 'value' => '{{var shippingAddress_getStreet}}'],
            ['label' => __('Shipping City'), 'value' => '{{var shippingAddress.getCity()}}'],
            ['label' => __('Shipping Phone Number'), 'value' => '{{var shippingAddress.getTelephone()}}'],
            ['label' => __('Shipping Country ID'), 'value' => '{{var shippingAddress.getCountryId()}}'],
            ['label' => __('Shipping Country'), 'value' => '{{var countryShipping}}'],
        ];
    }

    /**
     * @return array[]
     */
    private function getBillingOption()
    {
        return [
            ['label' => __('Billing Full Name'), 'value' => '{{var billingAddress_getFullName}}'],
            ['label' => __('Billing First Name'), 'value' => '{{var billingAddress.firstname}}'],
            ['label' => __('Billing Last Name'), 'value' => '{{var billingAddress.lastname}}'],
            ['label' => __('Billing Name Prefix'), 'value' => '{{var billingAddress.prefix}}'],
            ['label' => __('Billing Middle Name'), 'value' => '{{var billingAddress.middlename}}'],
            ['label' => __('Billing Name Suffix'), 'value' => '{{var billingAddress.suffix}}'],
            ['label' => __('Billing Company'), 'value' => '{{var billingAddress.company}}'],
            ['label' => __('Billing Address'), 'value' => '{{var formattedBillingAddress|raw}}'],
            ['label' => __('Billing State/Province'), 'value' => '{{var billingAddress.getRegion()}}'],
            ['label' => __('Billing Vat Number'), 'value' => '{{var billingAddress.getVatId()}}'],
            ['label' => __('Billing Fax'), 'value' => '{{var billingAddress.getFax()}}'],
            ['label' => __('Billing Zip/Postcode'), 'value' => '{{var billingAddress.getPostcode()}}'],
            ['label' => __('Billing Street Address'), 'value' => '{{var billingAddress_getStreet}}'],
            ['label' => __('Billing City'), 'value' => '{{var billingAddress.getCity()}}'],
            ['label' => __('Billing Phone Number'), 'value' => '{{var billingAddress.getTelephone()}}'],
            ['label' => __('Billing Country ID'), 'value' => '{{var billingAddress.getCountryId()}}'],
            ['label' => __('Billing Country'), 'value' => '{{var countryBilling}}'],
        ];
    }

    /**
     * Return available config variables
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getData()
    {
        return $this->_configVariables;
    }
}
