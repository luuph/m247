define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';
    return function (setBillingAddressAction) {
        return wrapper.wrap(setBillingAddressAction, function (originalAction, billingAddress) {
            if (billingAddress['extension_attributes'] === undefined) {
                billingAddress['extension_attributes'] = {};
            }

            billingAddress['extension_attributes']['city_id'] = 0;
            if (billingAddress.customAttributes !== undefined) {
                $.each(billingAddress.customAttributes, function(index, attribute) {
                    if (attribute.attribute_code !== undefined && attribute.attribute_code === 'city_id') {
                        // in case of new address
                        var cityId = attribute.value ? attribute.value : 0;
                        billingAddress['extension_attributes']['city_id'] = cityId;
                    } else if (index == 'city_id') {
                        // in case of old address
                        var cityId = attribute ? attribute : 0;
                        billingAddress['extension_attributes']['city_id'] = cityId;
                    }
                });
            }

            // pass execution to original action
            return originalAction(billingAddress);
        });
    };
});
