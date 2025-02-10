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
 * @package    Bss_CustomOptionAbsolutePriceQuantity
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

// @codingStandardsIgnoreFile
define([
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'mage/translate',
    'Magento_Catalog/js/price-options'
], function ($, _, mageTemplate, utils) {
    'use strict';
    $.widget('bsscoap.priceOptions', $.mage.priceOptions, {
        _onOptionChanged: function onOptionChanged(event)
        {
            var changes,
                option = $(event.target),
                handler = this.options.optionHandlers[option.data('role')];
            option.data('optionContainer', option.closest(this.options.controlContainer));
            if (handler && handler instanceof Function) {
                changes = handler(option, this.options.optionConfig, this);
            } else {
                changes = coapGetOptionValue(option, this.options.optionConfig);
            }
            $(this.options.priceHolderSelector).trigger('updatePrice', changes);
        },
        _applyOptionNodeFix: function applyOptionNodeFix(options)
        {
            var config = this.options,
                format = config.priceFormat,
                template = config.optionTemplate;
            template += ' <%= data.coapInfo %>';
            template = mageTemplate(template);
            options.filter('select').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element);
                var  optionConfig = config.optionConfig && config.optionConfig[optionId];

                $element.find('option').each(function (idx, option) {
                    var $option,
                        optionValue,
                        toTemplate,
                        prices;

                    $option = $(option);
                    optionValue = $option.val();

                    if (!optionValue && optionValue !== 0) {
                        return;
                    }
                    toTemplate = {
                        data: {
                            label: optionConfig[optionValue] && optionConfig[optionValue].name,
                            coapInfo: optionConfig[optionValue] && optionConfig[optionValue].type === 'abs' ? $.mage.__('(absolute price)') : ''
                        }
                    };
                    prices = optionConfig[optionValue] ? optionConfig[optionValue].prices : null;
                    if (prices) {
                        _.each(prices, function (price, type) {
                            var value = +(price.amount);
                            value += _.reduce(price.adjustments, function (sum, x) {
                                return sum + x;
                            }, 0);
                            toTemplate.data[type] = {
                                value: value,
                                formatted: utils.formatPrice(value, format)
                            };
                        });
                        $option.text(template(toTemplate));
                    }
                });
            });
        }
    });
    return $.bsscoap.priceOptions;

    function coapGetOptionValue(element, optionsConfig)
    {
        var changes = {},
            optionValue = element.val(),
            optionId = utils.findOptionId(element[0]),
            optionName = element.prop('name'),
            optionType = element.prop('type'),
            optionConfig = optionsConfig[optionId],
            optionHash = optionName,
            emptyPrice = {finalPrice: {amount: 0}, basePrice: {amount: 0}};
        switch (optionType) {
            case 'text':

            case 'textarea':
                triggerSubtotal(
                    optionName,
                    optionValue ? optionConfig.prices : emptyPrice,
                    optionValue ? optionConfig['type'] : 'fixed',
                    optionId,
                    ''
                );
                break;

            case 'radio':
                if (element.is(':checked')) {
                    triggerSubtotal(
                        optionName,
                        optionConfig[optionValue] ? optionConfig[optionValue].prices : emptyPrice,
                        optionConfig[optionValue] ? optionConfig[optionValue]['type'] : 'fixed',
                        optionId,
                        optionValue
                    );
                }
                break;
            case 'select-one':
                triggerSubtotal(
                    optionName,
                    optionConfig[optionValue] ? optionConfig[optionValue].prices : emptyPrice,
                    optionConfig[optionValue] ? optionConfig[optionValue]['type'] : 'fixed',
                    optionId,
                    optionValue
                );
                break;

            case 'select-multiple':
                _.each(optionConfig, function (row, optionValueCode) {
                    optionHash = optionName + '##' + optionValueCode;
                    triggerSubtotal(
                        optionHash,
                        _.contains(optionValue, optionValueCode) ? row.prices : emptyPrice,
                        row['type'],
                        optionId,
                        optionValue
                    );
                });
                break;

            case 'checkbox':
                optionHash = optionName + '##' + optionValue;
                triggerSubtotal(
                    optionHash,
                    element.is(':checked') ? optionConfig[optionValue].prices : emptyPrice,
                    optionConfig[optionValue]['type'],
                    optionId,
                    optionValue
                );
                break;

            case 'file':
                triggerSubtotal(
                    optionName,
                    optionValue ? optionConfig.prices : emptyPrice,
                    optionValue ? optionConfig['type'] : 'fixed',
                    optionId,
                    optionValue
                );
                break;
        }

        return changes;
    }

    function triggerSubtotal(code, amount, type, id, optionValueId)
    {
        $('#coap_subtotal').trigger('updateSubtotal',[code, amount, type, id, 'option', optionValueId]);
    }
});
