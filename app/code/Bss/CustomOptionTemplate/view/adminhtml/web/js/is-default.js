/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomOptionTemplate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'Magento_Ui/js/form/element/single-checkbox',
    'uiRegistry',
], function (uiCheckbox, registry) {
    'use strict';

    return uiCheckbox.extend({
        isDefaultIndex: 289,
        availableTypes: [
            'drop_down',
            'radio'
        ],

        setInitialValue: function () {
            this._super();

            return this;
        },

        onCheckedChanged: function () {
            this._super();
            var self = this;
            /**
             * Wait for the option type select render and observe its value
             */
            new Promise(function (resolve, reject) {
                var timer_search_container = setInterval(function () {
                    if (typeof self.containers[0] !== 'undefined') {
                        var option = self.containers[0].containers[0];
                        if (typeof option !== 'undefined') {
                            clearInterval(timer_search_container);
                            var path = 'source.' + option.dataScope,
                                optionType = self.get(path).type,
                                typeSelect = registry.get("ns = " + option.ns +
                                    ", parentScope = " + option.dataScope +
                                    ", index = type");
                            if (self.availableTypes.indexOf(optionType) !== -1) {
                                if (self.checked() === true) {
                                    option.elems.each(function (record) {
                                        var isDefault = record._elems[self.isDefaultIndex];
                                        if (isDefault !== self) {
                                            isDefault.checked(false);
                                        }
                                    });
                                }
                            }
                            resolve(typeSelect);
                        }
                    }
                }, 500);
            }).then(
                function (result) {
                    result.on('update', function (e) {
                        var option = self.containers[0].containers[0],
                            newOptionType = result.value(),
                            checkedCounter = 0;
                        option.elems.each(function (record) {
                            var isDefault = record._elems[self.isDefaultIndex];
                            if (isDefault.checked() === true) {
                                checkedCounter += 1;
                            }
                        });

                        //do not uncheck values if there is less then 2 checked values
                        //or new option type is drop_down/radio
                        if (self.availableTypes.indexOf(newOptionType) !== -1 && checkedCounter > 1) {
                            option.elems.each(function (record) {
                                var isDefault = record._elems[self.isDefaultIndex];
                                isDefault.checked(false);
                            });
                        }
                    });
                },
                function (error) {
                    console.log(error);
                }
            );

            return this;
        }
    });
});
