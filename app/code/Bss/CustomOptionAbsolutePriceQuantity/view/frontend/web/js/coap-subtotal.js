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
    'underscore',
    'Magento_Catalog/js/price-utils',
    'mage/template',
    'mage/translate',
    'Magento_Catalog/js/price-box'
], function ($, _, priceUtils, mageTemplate) {
    'use strict';
    $.widget('bsscoap.subTotal', {
        amount: {},
        tierPrices: {},
        tierPercent: 1,
        optionPercent: 1,
        subTotalBox: '#coap_subtotal',
        sideBox: '#coap_sidetotal',
        selectorPriceBox: '[data-role="priceBox"]',
        optionIdQty: "",
        _create: function () {
            var $widget = this;
            $widget.firstLoadPrice();
            $widget.firstLoadQtyandToolTip();
            $($widget.subTotalBox).bind('updateSubtotal', this.onUpdateSubtotal.bind(this));

            $('.bss-option-qty input').on('input', function () {
                $widget.optionIdQty = this.id;
                return $widget.onUpdateQtybox($widget);
            });

            $('#qty').on('input', function () {
                return $widget.onUpdateProductQtybox($widget);
            });

            $(this.selectorPriceBox).on('updatePrice', function ($data, $eventData) {
                if ($widget.options.tierPrices.type === 'configurable') {
                    $widget.onUpdateSwatch($widget);
                }
                $.each($eventData, function (ind, val) {
                    if ($widget.options.tierPrices.type === 'bundle' && $.isEmptyObject(val)) {
                        val = {finalPrice: {amount: 0}, basePrice: {amount: 0}}
                        $widget.amount[ind] = {amount: val, type: 'fixed', qty: 1, object: 'product'};
                        return true;
                    }
                    if ($.isEmptyObject(val) || ((ind === 'prices') && $widget.options.tierPrices.type === 'configurable')) {
                        return true;
                    }
                    $widget.amount[ind] = {amount: val, type: 'fixed', qty: 1, object: 'product'};
                });
                $widget.reloadPrice();
            });
        },
        firstLoadQtyandToolTip: function () {
            var $widget = this,
                $optionProduct = jQuery(".product-custom-option", jQuery('form#product_addtocart_form'));
            if ($optionProduct.length > 0) {
                $($optionProduct).each(function() {
                    var idElement = $(this).attr('id');
                    if (idElement) {
                        var string = idElement.split('_');
                        var optionId = string[1];
                        if ($(this).parents('.control').parent().find('.bss-abs-option').length < 1 && $(this).closest('div.field.date').length < 1) {
                            $(this).parents('.control').parent().addClass('bss_options_' + optionId);
                            $(this).parents('.control').parent().append($widget.options.optionQtyData[optionId]);
                            $(this).parents('.control').parent().find('> label:first').append($widget.options.optionToolTip[optionId]);
                            $(this).parents('.control').parent().trigger('contentUpdated');
                        }
                        if ($(this).closest('div.field.date').find('.bss-abs-option').length < 1 && $(this).closest('div.field.date').length > 0) {
                            $(this).closest('div.field.date').addClass('bss_options_' + optionId);
                            $(this).closest('div.field.date').append($widget.options.optionQtyData[optionId]);
                            $(this).closest('div.field.date').find('>fieldset').addClass('option-date-custom');
                            $(this).closest('div.field.date').find('>fieldset legend:first .price-notice').addClass('display-inline');
                            $(this).closest('div.field.date').find('>fieldset legend:first').append($widget.options.optionToolTip[optionId]);
                            $(this).closest('div.field.date').trigger('contentUpdated');
                        }
                    }
                });
            }

        },
        firstLoadPrice: function () {
            var $widget = this,
                html = '<div class="coap_subtotal">',
                productPrice;
            productPrice = {
                finalPrice: {
                    amount: $widget.options.finalPrice
                },
                basePrice: {
                    amount: $widget.options.basePrice
                }
            }
            html +=
                '<label class="coap_subtotal_label" for="coap_subtotal">' + $.mage.__('Subtotal: ') + '</label>' +
                '<span class="coap_subtotal_amount" id="coap_subtotal"></span>';
            if ($widget.options.displayType == '3') {
                html +=
                    '<label class="coap_subtotal_label side_box" for="coap_sidetotal">' + $.mage.__('Excl Tax Subtotal: ') + '</label>' +
                    '<span class="coap_subtotal_amount side_box" id="coap_sidetotal"></span>';
            }
            if ($widget.options.tierPrices.type === 'downloadable' && $widget.options.displayType == '3') {
                html +=
                    '<label class="coap_subtotal_label side_box" for="coap_sidetotal">' + $.mage.__('Excl Tax Subtotal: ') + '</label>' +
                    '<span class="coap_subtotal_amount side_box" id="coap_sidetotal"></span>';
            }
            html += '</div>';
            $('.box-tocart .field.qty').after(html);
            $widget.amount['price'] = {amount: productPrice, type: 'fixed', qty: 1, object: 'product'};
            if ($widget.options.tierPrices.type === 'simple') {
                $widget.tierPrices = $widget.options.tierPrices.price;
            } else if ($widget.options.tierPrices.type === 'configurable') {
                $widget.tierPrices.length = 0;
            } else if ($widget.options.tierPrices.type === 'bundle') {
                $widget.showBundleTierPrice($widget, $widget.options.tierPrices.price);
                $widget.tierPrices = $widget.options.tierPrices.price;
            }

            $widget.reloadPrice();
        },
        onUpdateSubtotal: function onUpdateSubtotal(event, code, price, type, id, object, optionValueId)
        {
            return this.updateSubtotal(code, price, type, id, object, optionValueId);
        },
        updateSubtotal: function updateSubtotal(code, price, type, id, object, optionValueId)
        {
            var qtyBox = $('#product-options-wrapper').find('#bss_option_qty_' + id),
                qty = qtyBox.length > 0 ? qtyBox.val() : 1;
            this.amount[code] = {amount: price, type: type, qty: qty, id: id, object: object, optionValueId :optionValueId};
            this.reloadPrice();
        },
        onUpdateSwatch: function onUpdateSwatch($widget)
        {
            var index = '',
                html = '',
                have_tier_price = false,
                productPrice,
                needSwatchTier = $('.product-info-main .price-box.price-tier_price').length == 0,
                tierPriceTemplate1 = '<span class="percent tier-%1">&nbsp;%2</span>%</strong>',
                tierPriceTemplate2 =
                    '<span class="price-container price-tier_price tax weee">'
                    + '<span data-price-amount="<%- inclAmount %>" data-price-type="" class="price-wrapper price-including-tax">'
                    + '<span class="price"><%- formatInclAmount %></span>'
                    + '</span>'
                    + '<% if (isShowBoth) { %>'
                    + '&nbsp;<span data-price-amount="<%- exclAmount %>" data-price-type="basePrice" data-label="Excl. Tax" class="price-wrapper price-excluding-tax">'
                    + '<span class="price"><%- formatExclAmount %></span>'
                    + '</span>'
                    + '<% } %>'
                    + '</span>';
            $('[data-role=swatch-options]').find('.swatch-attribute[option-selected]').each(function () {
                index += $(this).attr('option-selected') + '_';
            });
            $('.product-options-wrapper').find('.super-attribute-select').not('disabled').each(function () {
                if (this.value > 0) {
                    index += this.value + '_';
                }
            });
            if ($widget.options.tierPrices.price['child'].hasOwnProperty(index)) {
                html = '<ul class="prices-tier items">';
                $.each($widget.options.tierPrices.price['child'][index], function (index, tier) {
                    var tierHtml = '';
                    tierHtml += '<li class="item">';
                    tierHtml += $.mage.__('Buy %1 for ').replace('%1', tier['qty']);
                    tierHtml += mageTemplate(
                        tierPriceTemplate2,
                        {
                            inclAmount: tier.final,
                            formatInclAmount: getFormattedPrice(tier.final),
                            exclAmount: tier.base,
                            formatExclAmount: getFormattedPrice(tier.base),
                            isShowBoth: $widget.options.finalPrice != $widget.options.basePrice && $widget.options.displayType == '3'
                        }
                    );
                    tierHtml += $.mage.__(' each and ');
                    tierHtml += '<strong class="benefit">';
                    tierHtml += $.mage.__('save');
                    tierHtml += tierPriceTemplate1.replace('%1', index).replace('%2', tier['percent'].toFixed(2));
                    tierHtml += '</li>';
                    html += tierHtml;
                    have_tier_price = true;
                });
                html += '</ul>';
                if (needSwatchTier) {
                    $(".prices-tier.items").remove();
                    if (have_tier_price) {
                        $('.product-info-price').after(html);
                    }
                }

                $widget.tierPrices = $widget.options.tierPrices.price['child'][index];
                $widget.onUpdateProductQtybox($widget);
            }

            if ($widget.options.configurablePricesData['child'].hasOwnProperty(index)) {
                productPrice = {
                    finalPrice: {
                        amount: $widget.options.configurablePricesData['child'][index]['final']
                    },
                    basePrice: {
                        amount: $widget.options.configurablePricesData['child'][index]['base']
                    }
                }
                $widget.amount['price'] = {amount: productPrice, type: 'fixed', qty: 1, object: 'product'};
            } else {
                productPrice = {
                    finalPrice: {
                        amount: $widget.options.finalPrice
                    },
                    basePrice: {
                        amount: $widget.options.basePrice
                    }
                }
                $widget.amount['price'] = {amount: productPrice, type: 'fixed', qty: 1, object: 'product'};
            }
        },
        showBundleTierPrice: function ($widget, $prices) {
            var html = '',
                have_tier_price = false,
                needSwatchTier = $('.bundle-options-wrapper .prices-tier.items').length == 0,
                tierPriceTemplate1 = '<span class="percent tier-%1">&nbsp;%2</span>%</strong>';
            if (!needSwatchTier) {
                return;
            }
            html = '<ul class="prices-tier items">';
            $.each($prices, function (index, tier) {
                var tierHtml = '';
                tierHtml += '<li class="item">';
                tierHtml += $.mage.__('Buy %1 with').replace('%1', Number(tier['qty']));
                tierHtml += '<strong class="benefit">';
                tierHtml += tierPriceTemplate1.replace('%1', index).replace('%2', 100 - tier['tier_percent'].toFixed(2));
                tierHtml += $.mage.__(' discount each');
                tierHtml += '</li>';
                html += tierHtml;
                have_tier_price = true;
            });
            html += '</ul>';
            if (have_tier_price) {
                $('.product-options-bottom').html(html);
            }
        },
        onUpdateQtybox: function onUpdateQtybox($widget)
        {
            var optionIdChange = $widget.optionIdQty;
            $.each($widget.amount, function (id, vl) {
                var qtyBox = $('#product-options-wrapper').find('#bss_option_qty_' + vl.id),
                    qty = qtyBox.length > 0 ? qtyBox.val() : 1;
                vl.qty = qty;
            });
            this.reloadPrice();
        },
        onUpdateProductQtybox: function onUpdateProductQtybox($widget)
        {
            var productQty = $('#qty').val(),
                applyTierPrice = {
                    finalPrice: {
                        amount: 0
                    },
                    basePrice: {
                        amount: 0
                    }
                };
            $widget.tierPercent = 1;
            $widget.optionPercent = 1;
            if ($widget.tierPrices.length > 0) {
                $.each($widget.tierPrices, function (index, price) {
                    if (productQty >= Number(price.qty)) {
                        if ($widget.options.tierPrices.type === 'bundle') {
                            $widget.tierPercent = Number(price.tier_percent)/100;
                        } else {
                            applyTierPrice.finalPrice.amount = price.final_discount;
                            applyTierPrice.basePrice.amount = price.base_discount;
                            $widget.optionPercent = (100 - Number(price.percent))/100;
                        }
                    } else {
                        return false;
                    }
                });
            }
            $widget.amount['tier'] = {amount: applyTierPrice, type: 'fixed', qty: 1, object: 'product'};
            this.reloadPrice();
        },
        reloadPrice: function reloadPrice()
        {
            var subtotal = 0,
                sideSubtotal = 0,
                productQty = $('#qty').val(),
                $widget = this;
            var unitTotalIncl = 0,
                unitTotalExcl = 0;
            var regularExcl = 0,
                regularIncl = 0;
            let optionIdChange = $widget.optionIdQty,
                idChange = optionIdChange.slice(15);
                //Format id:bss_option_qty_ + id; slice 15 is size of bss_option_qty_
            $.each($widget.amount, function (id, vl) {
                var qtyBox = $('#product-options-wrapper').find('#bss_option_qty_' + vl.id);
                if (!vl.id) {
                    if (id.includes("options["+idChange+"]")
                        || id.includes("options_"+idChange)
                    ) {
                        qtyBox = $('#product-options-wrapper').find("#"+optionIdChange+"");
                        vl.qty = qtyBox.length > 0 ? qtyBox.val() : 1;
                    }
                }

                var qty = qtyBox.length > 0 ? qtyBox.val() : 1;
                var checkQty;
                if (vl.type === 'abs') {
                    checkQty = qty;
                } else {
                    checkQty = qtyBox.length > 0 ? qtyBox.val() * productQty  : productQty;
                }
                vl.amount.checkTier = 0;
                if (typeof $widget.options.tierPricesOption[vl.id] !== "undefined") {
                    if (typeof vl.amount.finalOldPrice !== "undefined") {
                        vl.amount.finalPrice.amount = vl.amount.finalOldPrice.amount;
                        vl.amount.basePrice.amount = vl.amount.baseOldPrice.amount;
                    }
                    if (vl.optionValueId !== "" && typeof $widget.options.tierPricesOption[vl.id][vl.optionValueId] !== "undefined") {
                        var tierPrice = 0;
                        var baseTierPrice = 0;
                        var tierOptions = $widget.options.tierPricesOption[vl.id][vl.optionValueId];
                        if (tierOptions) {
                            tierOptions.sort(function (a, b) {
                                if (undefined !== a.price_qty && undefined !== b.price_qty) {
                                    return parseInt(a.price_qty) - parseInt(b.price_qty);
                                }
                                return 0;
                            });
                        }
                        $.each(tierOptions, function (index, tier) {
                            if (Number(checkQty) >= Number(tier['price_qty'])) {
                                if (tier['price-type'] === "percent" && tier['parent_price_type'] === "percent") { // Recalculate tier price when product has tier price
                                    tier['final_tier_price'] = tier['optionPrice'] * $widget.optionPercent * tier['price'] / 100 * tier['currency_rate'];
                                }
                                tierPrice = tier['final_tier_price'];

                                if (tier['price-type'] === "percent" && tier['parent_price_type'] === "percent") { // Recalculate base tier price when product has tier price
                                    tier['base_final_tier_price'] = tier['optionBasePrice'] * $widget.optionPercent * tier['price'] / 100 * tier['currency_rate'];
                                }
                                baseTierPrice = tier['base_final_tier_price'];
                            }
                        });
                        if (tierPrice > 0 && vl.amount.finalPrice.amount > 0) {
                            vl.amount.finalOldPrice = {};
                            vl.amount.baseOldPrice = {};
                            vl.amount.finalOldPrice.amount = vl.amount.finalPrice.amount;
                            vl.amount.baseOldPrice.amount = vl.amount.basePrice.amount;
                            vl.amount.finalPrice.amount = tierPrice;
                            vl.amount.basePrice.amount = baseTierPrice;
                            vl.amount.checkTier = 1;
                        }
                    } else {
                        var tierPriceOption = 0;
                        var baseTierPriceOption = 0;
                        var tierOptions = $widget.options.tierPricesOption[vl.id][0];
                        if (tierOptions) {
                            tierOptions.sort(function (a, b) {
                                if (undefined !== a.price_qty && undefined !== b.price_qty) {
                                    return parseInt(a.price_qty) - parseInt(b.price_qty);
                                }
                                return 0;
                            });
                        }
                        $.each(tierOptions, function (index, tier) {
                            if (Number(checkQty) >= Number(tier['price_qty'])) {
                                if (tier['price-type'] === "percent" && tier['parent_price_type'] === "percent") { // Recalculate tier price when product has tier price
                                    tier['final_tier_price'] = tier['optionPrice'] * $widget.optionPercent * tier['price'] / 100 * tier['currency_rate'];
                                }
                                tierPriceOption = tier['final_tier_price'];

                                if (tier['price-type'] === "percent" && tier['parent_price_type'] === "percent") { // Recalculate base tier price when product has tier price
                                    tier['base_final_tier_price'] = tier['optionBasePrice'] * $widget.optionPercent * tier['price'] / 100 * tier['currency_rate'];
                                }
                                baseTierPriceOption = tier['base_final_tier_price'];
                            }

                        });
                        if (tierPriceOption > 0 && vl.amount.finalPrice.amount > 0) {
                            vl.amount.finalOldPrice = {};
                            vl.amount.baseOldPrice = {};
                            vl.amount.finalOldPrice.amount = vl.amount.finalPrice.amount;
                            vl.amount.baseOldPrice.amount = vl.amount.basePrice.amount;
                            vl.amount.finalPrice.amount = tierPriceOption;
                            vl.amount.basePrice.amount = baseTierPriceOption;
                            vl.amount.checkTier = 1;
                        }
                    }
                }
                var type = (vl.type !== 'abs') ? productQty : 1,
                    ajustment = vl.object !== 'option' ? $widget.tierPercent : 1,
                    optionPercent = vl.object === 'option' && vl.type === 'percent' && vl.amount.checkTier === 0 ? $widget.optionPercent : 1;
                subtotal += vl.amount.finalPrice.amount * Number(vl.qty) * Number(type) * ajustment * optionPercent;
                sideSubtotal += vl.amount.basePrice.amount * Number(vl.qty) * Number(type) * ajustment * optionPercent;

                // Calculate unit price in product page.
                var qtyCO = 1,
                    amountCOIncl = 0,
                    amountCOExcl = 0;
                if (id !== 'price') { // If diff qty product
                    qtyCO = Number(vl.qty); // QTY custom option
                }
                if (vl.type !== 'abs') { // Calculate when type diff absolute price
                    amountCOIncl = vl.amount.finalPrice.amount;
                    amountCOExcl = vl.amount.basePrice.amount;
                }
                unitTotalIncl += amountCOIncl * qtyCO * ajustment * optionPercent;
                unitTotalExcl += amountCOExcl * qtyCO * ajustment * optionPercent;

                //Calculate regular price in product page
                switch (id) {
                    case "price":
                        regularExcl += $widget.options.regularPrice;
                        regularIncl += $widget.options.regularPriceTax;
                        break;
                    case "tier":
                        if (Number(amountCOExcl) !== 0 || Number(amountCOExcl) !== 0)
                        {
                            regularExcl = unitTotalExcl;
                            regularIncl = unitTotalIncl;
                        }
                        break;
                    default:
                        regularExcl += amountCOExcl * qtyCO * ajustment * optionPercent;
                        regularIncl += amountCOIncl * qtyCO * ajustment * optionPercent;
                        break;
                }
            });
            if ($widget.options.displayType == '1') {
                $($widget.subTotalBox).html(getFormattedPrice(sideSubtotal));
            } else {
                $($widget.subTotalBox).html(getFormattedPrice(subtotal));
            }
            if ($widget.options.displayType == '3') {
                $($widget.sideBox).html(getFormattedPrice(sideSubtotal));
            }
            this.reloadRegularPrice(regularExcl, regularIncl, $widget.options.displayType);
            this.reloadUnitPrice(unitTotalIncl, unitTotalExcl, $widget.options.displayType);
        },
        reloadUnitPrice: function (unitTotalIncl, unitTotalExcl, displayTypeTax)
        {
            var priceBox = $('.product-info-price ' + this.selectorPriceBox).find('[data-price-type!="oldPrice"]').children('span.price');

            if (displayTypeTax == 1) { // Show excl. tax
                priceBox.html(getFormattedPrice(unitTotalExcl));
            } else if (displayTypeTax == 2) { // Show incl. tax
                priceBox.html(getFormattedPrice(unitTotalIncl));
            } else if (displayTypeTax == 3) { // Show incl. & excl. tax
                priceBox.parent('.price-including-tax').children().html(getFormattedPrice(unitTotalIncl));
                priceBox.parent('.price-excluding-tax').children().html(getFormattedPrice(unitTotalExcl));
            }
        },
        reloadRegularPrice: function (regularExcl, regularIncl, displayTypeTax)
        {
            var regular = $('.product-info-price ' + this.selectorPriceBox).find('[data-price-type="oldPrice"]').children('span.price');

            if (displayTypeTax == 3 || displayTypeTax == 2) { // Show incl. & excl. tax and show inl .tax
                regular.html(getFormattedPrice(regularIncl));
            } else { // Show excl. tax
                regular.html(getFormattedPrice(regularExcl));
            }
        }
    });
    return $.bsscoap.subTotal;

    function getFormattedPrice(price)
    {
        return priceUtils.formatPrice(price);
    }
});
