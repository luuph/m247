define([
    'jquery',
    'Magento_Catalog/js/price-box'
], function ($, priceBox) {
    'use strict';

    return priceBox.extend({
        /**
         * Override reloadPrice method
         */
        reloadPrice: function () {
            this._super(); // Call the parent reloadPrice method

            // Replace Arabic numerals with English numerals in all price elements
            $('.price').each(function () {
                $(this).text($(this).text().replace(/[٠-٩]/g, function (d) {
                    return "٠١٢٣٤٥٦٧٨٩".indexOf(d);
                }).replace('٫', '.'));
            });
        }
    });
});
