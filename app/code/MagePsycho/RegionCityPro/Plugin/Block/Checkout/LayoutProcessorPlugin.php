<?php

namespace MagePsycho\RegionCityPro\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use MagePsycho\RegionCityPro\Helper\Data as RegionCityProHelper;
use MagePsycho\RegionCityPro\Api\Data\CityInterface;
use Magento\Checkout\Helper\Data as CheckoutDataHelper;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LayoutProcessorPlugin
{
    private const DEFAULT_COUNTRY_SORT_ORDER = 80;

    /**
     * @var CheckoutDataHelper
     */
    protected $checkoutHelper;

    /**
     * @var RegionCityProHelper
     */
    protected $regionCityProHelper;

    public function __construct(
        CheckoutDataHelper $checkoutDataHelper,
        RegionCityProHelper $regionCityProHelper
    ) {
        $this->checkoutHelper = $checkoutDataHelper;
        $this->regionCityProHelper = $regionCityProHelper;
    }

    public function afterProcess(
        LayoutProcessor $subject,
        array $result
    ) {
        // if ($this->regionCityProHelper->isFxnSkipped()) {
        //     return $result;
        // }

        // // Add/update shipping address fields
        // $rootJsLayout = &$result['components']['checkout']['children']['steps']['children'];
        // $renderOptions = $this->prepareRenderOptions($rootJsLayout);
        // $rootJsLayout = $this->addShippingAddressCityIdField($rootJsLayout, $renderOptions);
        // $rootJsLayout = $this->addShippingAddressCountryTemplate($rootJsLayout, $renderOptions);
        // $rootJsLayout = $this->addShippingAddressRegionTemplate($rootJsLayout, $renderOptions);
        // $rootJsLayout = $this->addShippingAddressCityVisibility($rootJsLayout, $renderOptions);

        // // Add/update billing address fields
        // if ($this->checkoutHelper->isDisplayBillingOnPaymentMethodAvailable()) {
        //     $paymentForms = $rootJsLayout['billing-step']['children']['payment']['children']['payments-list']['children'];

        //     foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
        //         $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);
        //         if (! isset($rootJsLayout['billing-step']['children']
        //             ['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
        //             continue;
        //         }
        //         $componentConfig = [
        //             'scope' => 'billingAddress' . $paymentMethodCode,
        //             'customScope' => 'billingAddress' . $paymentMethodCode . '.custom_attributes',
        //             'paymentNode' => 'payments-list',
        //             'formNode' => $paymentMethodCode . '-form',
        //         ];
        //         $rootJsLayout = $this->addBillingAddressCityIdField($rootJsLayout, $componentConfig, $renderOptions);
        //         $rootJsLayout = $this->addBillingAddressCountryTemplate($rootJsLayout, $componentConfig, $renderOptions);
        //         $rootJsLayout = $this->addBillingAddressRegionTemplate($rootJsLayout, $componentConfig, $renderOptions);
        //         $rootJsLayout = $this->addBillingAddressCityVisibility($rootJsLayout, $componentConfig, $renderOptions);
        //     }
        // } else {
        //     $componentConfig = [
        //         'scope' => 'billingAddressshared',
        //         'customScope' => 'billingAddressshared',
        //         'paymentNode' => 'afterMethods',
        //         'formNode' => 'billing-address-form',
        //     ];
        //     $rootJsLayout = $this->addBillingAddressCityIdField($rootJsLayout, $componentConfig, $renderOptions);
        //     $rootJsLayout = $this->addBillingAddressCountryTemplate($rootJsLayout, $componentConfig, $renderOptions);
        //     $rootJsLayout = $this->addBillingAddressRegionTemplate($rootJsLayout, $componentConfig, $renderOptions);
        //     $rootJsLayout = $this->addBillingAddressCityVisibility($rootJsLayout, $componentConfig, $renderOptions);
        // }

        return $result;
    }

    private function addShippingAddressCityIdField($rootJsLayout, $renderOptions = [])
    {
        $cityIdField = [
            'component' => 'MagePsycho_RegionCityPro/js/form/element/city',
            'config'    => [
                'customScope'   => 'shippingAddress.custom_attributes',
                'customEntry'   => 'shippingAddress.city',
                'template'      => 'ui/form/field',
                'elementTmpl'   => 'ui/form/element/select',
            ],
            'label' => __('City'),
            //'value' => '',
            'dataScope' => 'shippingAddress.custom_attributes.' . CityInterface::ID,
            'provider' => 'checkoutProvider',
            'sortOrder' => $renderOptions['country_sort_order'] + 2,
            'customEntry' => null,
            'visible' => true,
            'options' => [],
            'validation' => [
                'required-entry' => true,
            ],
            'filterBy' => [
                'target' => '${ $.provider }:shippingAddress.region_id',
                'field' => 'region_id'
            ],
            'imports' => [
                'initialOptions' => 'index = checkoutProvider:dictionaries.' . CityInterface::ID,
                'setOptions' => 'index = checkoutProvider:dictionaries.' . CityInterface::ID
            ]
        ];
        if ($renderOptions['searchable_city']) {
            $cityIdField['config']['elementTmpl'] = 'MagePsycho_RegionCityPro/form/element/select2';
        }

        $rootJsLayout['shipping-step']['children']['shippingAddress']['children']
        ['shipping-address-fieldset']['children'][CityInterface::ID] = $cityIdField;
        return $rootJsLayout;
    }

    private function addShippingAddressCityVisibility($rootJsLayout, $renderOptions = [])
    {
        if (isset(
            $rootJsLayout['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['city']
        )) {
            $rootJsLayout['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']
            ['children']['city']['visible'] = false;

            $rootJsLayout['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']
            ['children']['city']['sortOrder'] = $renderOptions['country_sort_order'] + 2;
        }
        return $rootJsLayout;
    }

    private function addShippingAddressCountryTemplate($rootJsLayout, $renderOptions = [])
    {
        if ($renderOptions['searchable_country']) {
            $rootJsLayout['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id']['component']
                = 'MagePsycho_RegionCityPro/js/form/element/region';
            $rootJsLayout['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['country_id']['config']['elementTmpl']
                = 'MagePsycho_RegionCityPro/form/element/select2';
        }
        return $rootJsLayout;
    }

    private function addShippingAddressRegionTemplate($rootJsLayout, $renderOptions = [])
    {
        if ($renderOptions['searchable_region']) {
            $rootJsLayout['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['component']
                = 'MagePsycho_RegionCityPro/js/form/element/region';
            $rootJsLayout['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['region_id']['config']['elementTmpl']
                = 'MagePsycho_RegionCityPro/form/element/select2';
        }
        // Reorder shipping region fields
        $rootJsLayout['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['region']['sortOrder'] = $renderOptions['country_sort_order'] + 1;
        $rootJsLayout['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['region_id']['sortOrder'] = $renderOptions['country_sort_order'] + 1;
        return $rootJsLayout;
    }

    private function addBillingAddressCityIdField($rootJsLayout, $componentConfig, $renderOptions = [])
    {
        $cityIdField = [
            'component' => 'MagePsycho_RegionCityPro/js/form/element/city',
            'config' => [
                'customScope'   => $componentConfig['customScope'],
                'customEntry'   => $componentConfig['scope'] . '.city',
                'template'      => 'ui/form/field',
                'elementTmpl'   => 'ui/form/element/select'
            ],
            'label' => __('City'),
            //'value' => '',
            'dataScope' =>  $componentConfig['customScope'] . '.' . CityInterface::ID,
            'provider' => 'checkoutProvider',
            'sortOrder' => $renderOptions['country_sort_order'] + 2,
            'customEntry' => null,
            'visible' => true,
            'options' => [],
            'validation' => [
                'required-entry' => true,
            ],
            'filterBy' => [
                'target' => '${ $.provider }:${ $.parentScope }.region_id',
                'field' => 'region_id'
            ],
            'imports'   => [
                'initialOptions' => 'index = checkoutProvider:dictionaries' . '.' . CityInterface::ID,
                'setOptions' => 'index = checkoutProvider:dictionaries' . '.' . CityInterface::ID
            ]
        ];

        if ($renderOptions['searchable_city']) {
            $cityIdField['config']['elementTmpl'] = 'MagePsycho_RegionCityPro/form/element/select2';
        }

        $rootJsLayout['billing-step']['children']
        ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]['children']
        ['form-fields']['children'][CityInterface::ID] = $cityIdField;
        return $rootJsLayout;
    }

    private function addBillingAddressCityVisibility($rootJsLayout, $componentConfig, $renderOptions = [])
    {
        if (isset(
            $rootJsLayout['billing-step']
            ['children']['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['city']
        )) {
            $rootJsLayout['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['city']['visible'] = false;

            $rootJsLayout['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['city']['sortOrder'] = $renderOptions['country_sort_order'] + 2;
        }
        return $rootJsLayout;
    }

    private function addBillingAddressCountryTemplate($rootJsLayout, $componentConfig, $renderOptions = [])
    {
        if ($renderOptions['searchable_country']) {
            $rootJsLayout['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['country_id']['component']
                = 'MagePsycho_RegionCityPro/js/form/element/region';

            $rootJsLayout['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['country_id']['config']['elementTmpl']
                = 'MagePsycho_RegionCityPro/form/element/select2';
        }

        return $rootJsLayout;
    }

    private function addBillingAddressRegionTemplate($rootJsLayout, $componentConfig, $renderOptions = [])
    {
        if ($renderOptions['searchable_region']) {
            $rootJsLayout['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['region_id']['component']
                = 'MagePsycho_RegionCityPro/js/form/element/region';

            $rootJsLayout['billing-step']['children']
            ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
            ['children']['form-fields']['children']['region_id']['config']['elementTmpl']
                = 'MagePsycho_RegionCityPro/form/element/select2';
        }

        // Reorder billing region fields
        $rootJsLayout['billing-step']['children']
        ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
        ['children']['form-fields']['children']['region']['sortOrder'] = $renderOptions['country_sort_order'] + 1;
        $rootJsLayout['billing-step']['children']
        ['payment']['children'][$componentConfig['paymentNode']]['children'][$componentConfig['formNode']]
        ['children']['form-fields']['children']['region_id']['sortOrder'] = $renderOptions['country_sort_order'] + 1;

        return $rootJsLayout;
    }

    private function prepareRenderOptions($rootJsLayout)
    {
        $countrySortOrder =  $rootJsLayout['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children']['country_id']['sortOrder'] ?? self::DEFAULT_COUNTRY_SORT_ORDER;
        return [
            'country_sort_order' => $countrySortOrder,
            'searchable_city' => $this->regionCityProHelper->getConfig()->isCitySearchable(),
            'searchable_country' => $this->regionCityProHelper->getConfig()->isCountrySearchable(),
            'searchable_region' => $this->regionCityProHelper->getConfig()->isRegionSearchable(),
        ];
    }
}
