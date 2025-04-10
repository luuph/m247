/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'uiComponent',
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mageplaza_Shopbybrand/summary/item/osc/brand',
        },

        getBrand: function(item) {
            var brand = null;
            if (item.brand) {
                brand = item.brand;
            } else {
                var items = window.checkoutConfig.totalsData.items;
                $.each(items, function(index, value) {
                    if (item.item_id == value.item_id) {
                        brand = value.brand;
                        return false;
                    }
                })
            }

            return brand;
        }
    });
});
