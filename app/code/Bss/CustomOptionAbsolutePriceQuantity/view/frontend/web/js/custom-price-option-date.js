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

define([
    'jquery',
    'priceUtils',
    'priceOptions',
    'jquery/ui',
    'priceBox',
    'Magento_Catalog/js/price-option-date'
], function ($, utils) {
    'use strict';
    var optionHandler = {optionHandlers: {}};

    $.widget('bsscoap.priceOptionDate', $.mage.priceOptionDate, {
        _create: function initOptionDate()
        {
            var field = this.element,
                form = field.closest(this.options.fromSelector),
                dropdowns = $(this.options.dropdownsSelector, field),
                dateOptionId;
            if (dropdowns.length) {
                dateOptionId = this.options.dropdownsSelector + dropdowns.attr('name');
                optionHandler.optionHandlers[dateOptionId] = onCalendarDropdownChange(dropdowns);

                form.priceOptions(optionHandler);

                dropdowns.data('role', dateOptionId);
                dropdowns.on('change', onDateChange.bind(this, dropdowns));
            }
        }
    });

    function onCalendarDropdownChange(siblings)
    {
        return function (element, optionConfig, form) {
            var changes = {},
                optionId = utils.findOptionId(element),
                overhead = optionConfig[optionId].prices,
                isNeedToUpdate = true,
                optionHash = 'price-option-calendar-' + optionId;
            siblings.each(function (index, el) {
                isNeedToUpdate = isNeedToUpdate && !!$(el).val();
            });
            triggerSubtotal(
                'options[' + optionId + ']',
                isNeedToUpdate ? overhead : {finalPrice: {amount: 0}, basePrice: {amount: 0}},
                isNeedToUpdate ? optionConfig[optionId]['type'] : 'fixed',
                optionId,
                ''
            );
            changes[optionHash] = {};

            return changes;
        };
    }

    function triggerSubtotal(code, amount, type, id, optionValueId)
    {
        $('#coap_subtotal').trigger('updateSubtotal',[code, amount, type, id, 'option', optionValueId]);
    }

    function onDateChange(dropdowns)
    {
        var daysNodes,
            curMonth, curYear, expectedDays,
            options, needed,
            month = dropdowns.filter('[data-calendar-role=month]'),
            year = dropdowns.filter('[data-calendar-role=year]');

        if (month.length && year.length) {
            daysNodes = dropdowns.filter('[data-calendar-role=day]').find('option');

            curMonth = month.val() || '01';
            curYear = year.val() || '2000';
            expectedDays = getDaysInMonth(curMonth, curYear);

            if (daysNodes.length - 1 > expectedDays) { // remove unnecessary option nodes
                daysNodes.each(function (i, e) {
                    if (e.value > expectedDays) {
                        $(e).remove();
                    }
                });
            } else if (daysNodes.length - 1 < expectedDays) { // add missing option nodes
                options = [];
                needed = expectedDays - daysNodes.length + 1;

                while (needed--) {
                    options.push('<option value="' + (expectedDays - needed) + '">' + (expectedDays - needed) + '</option>');
                }
                $(options.join('')).insertAfter(daysNodes.last());
            }
        }
    }

    function getDaysInMonth(month, year)
    {
        return new Date(year, month, 0).getDate();
    }
});
