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
 * @category  BSS
 * @package   Bss_ConfigurableProductWholesale
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'underscore',
    'priceUtils',
    'priceBox',
    'jquery-ui-modules/widget',
    'jquery/jquery.parsequery',
    'Bss_ConfigurableProductWholesale/js/cpwd',
    'domReady!'
], function ($, _, priceUtils) {
    'use strict';
    return function (widget) {
        $.widget('mage.configurable', widget, {
            options: {
                classes: {
                    selectClass: 'super-attribute-select',
                },
                ids: {
                    wrapper: 'product-options-wrapper',
                    optionPrice: 'bss-option-price'
                },
            },

            _create: function () {
                this._super();
                this.eventListener();
            },

            eventListener: function () {
                var self = this,
                    options = self.options;
                if (!options.isEnabledSdcp) {
                    $(document).on('updateProductBaseImage', function () {
                        setTimeout(function () {
                            $( '.super-attribute-select').each(function () {
                                var _this = this;
                                var value = $(_this).find('option:eq(1)').val();
                                if (!$(_this).val() && !$(_this).parent().parent().hasClass('bss-last-select')) {
                                    $(_this).val(value).trigger('change');
                                }
                            });
                        }, 1000);
                    });

                    if (options.hasOwnProperty('preselect')) {
                        self._preselectByConfig(options);
                    }
                }
            },

            _configureElement: function (element) {
                this.simpleProduct = this._getSimpleProductId(element);

                if (element.value) {
                    this.options.state[element.config.id] = element.value;

                    if (element.nextSetting) {
                        element.nextSetting.disabled = false;
                        this._fillSelect(element.nextSetting);
                        this._resetChildren(element.nextSetting);
                    } else {
                        if (!!document.documentMode) { //eslint-disable-line
                            this.inputSimpleProduct.val(element.options[element.selectedIndex].config.allowedProducts[0]);
                        } else {
                            this.inputSimpleProduct.val(element.selectedOptions[0].config.allowedProducts[0]);
                        }
                    }
                } else {
                    this._resetChildren(element);
                }

                this._reloadPrice();
                this._displayRegularPriceBlock(this.simpleProduct);
                if (!$('.prices-tier.items').length) {
                    this._displayTierPriceBlock(this.simpleProduct);
                }
                this._displayNormalPriceLabel();
                if (!window.bsssdcp) {
                    this._changeProductImage();
                }
            },

            _preselectByConfig: function (options) {
                var optionAttribute = options.spConfig.attributes,
                    preselect = {};
                $.each(optionAttribute, function (id, attribute) {
                    if (options.preselect.hasOwnProperty(attribute.id)) {
                        preselect[attribute.id] = {};
                        preselect[attribute.id]['id'] = attribute.id;
                        preselect[attribute.id]['position'] = attribute.position;
                        preselect[attribute.id]['value'] = options.preselect[attribute.id];
                    }
                });

                $.each(preselect, function ($index, $vl) {
                    try {
                        if (!$('.configurable #attribute' + $vl.id + '').parents('.configurable').hasClass('bss-hidden')) {
                            $('.configurable #attribute'
                                + $vl.id
                                + '').val($vl.value).trigger('change');
                        }
                    } catch (e) {
                        console.log($.mage.__('Error when applied preselect product'));
                    }
                });
            },

            _configureForValues: function () {
                this._super();
                if ($('.bss-ptd-table').length) {
                    var item_id = 0,
                        product_id = 0,
                        encodeAddCart,
                        addCart,
                        encodeUpdateCart,
                        updateCart,
                        bssConfig = window.checkout.bssConfigurableWholesale;
                    if (bssConfig) {
                        item_id = bssConfig.item_id;
                        product_id = bssConfig.product_id;
                    }
                    if (item_id && product_id) {
                        $.ajax({
                            type: 'post',
                            url: bssConfig.urlLoadItem,
                            data: {product: product_id, item_id: item_id},
                            dataType: 'json',
                            success: function (data) {
                                window.jsonEditInfo = data;
                                $('.bss-ptd-table').trigger('reloadTable');
                            },
                            error: function () {

                            }
                        });
                    }
                }
            },

            /**
             * Populates an option's selectable choices.
             * @private
             * @param {*} element - Element associated with a configurable option.
             */
            _fillSelect: function (element) {
                var attributeId = element.id.replace(/[a-z]*/, ''),
                    options = this._getAttributeOptions(attributeId),
                    prevConfig,
                    index = 1,
                    allowedProducts,
                    i,
                    j,
                    finalPrice = parseFloat(this.options.spConfig.prices.finalPrice.amount),
                    optionFinalPrice,
                    optionPriceDiff,
                    optionPrices = this.options.spConfig.optionPrices,
                    allowedProductMinPrice;

                this._clearSelect(element);
                element.options[0] = new Option('', '');
                element.options[0].innerHTML = this.options.spConfig.chooseText;
                prevConfig = false;

                if (element.prevSetting) {
                    prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
                }

                if (options) {
                    for (i = 0; i < options.length; i++) {
                        allowedProducts = [];
                        optionPriceDiff = 0;

                        /* eslint-disable max-depth */
                        if (prevConfig) {
                            for (j = 0; j < options[i].products.length; j++) {
                                // prevConfig.config can be undefined
                                if (prevConfig.config &&
                                    prevConfig.config.allowedProducts &&
                                    prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                    allowedProducts.push(options[i].products[j]);
                                }
                            }
                        } else {
                            allowedProducts = options[i].products.slice(0);

                            if (typeof allowedProducts[0] !== 'undefined' &&
                                typeof optionPrices[allowedProducts[0]] !== 'undefined' &&
                                typeof this._getAllowedProductWithMinPrice === 'function') {
                                allowedProductMinPrice = this._getAllowedProductWithMinPrice(allowedProducts);
                                optionFinalPrice = parseFloat(optionPrices[allowedProductMinPrice].finalPrice.amount);
                                optionPriceDiff = optionFinalPrice - finalPrice;

                                if (optionPriceDiff !== 0) {
                                    options[i].label = options[i].label + ' ' + priceUtils.formatPrice(
                                        optionPriceDiff,
                                        this.options.priceFormat,
                                        true);
                                }
                            }
                        }

                        if (allowedProducts.length > 0) {
                            options[i].allowedProducts = allowedProducts;
                            element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                            if (typeof options[i].price !== 'undefined') {
                                element.options[index].setAttribute('price', options[i].price);
                            }

                            element.options[index].config = options[i];
                            index++;
                        }

                        /* eslint-enable max-depth */
                    }
                }
            },
        });

        return $.mage.configurable;
    }
});
