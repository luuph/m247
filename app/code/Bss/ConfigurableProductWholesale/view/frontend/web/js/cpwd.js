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
 * @copyright Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

define(
    [
        'ko',
        'uiComponent',
        'jquery',
        'Magento_Ui/js/modal/alert',
        'underscore',
        'Magento_Catalog/js/price-utils',
        'Magento_Customer/js/customer-data',
        'Magento_Swatches/js/swatch-renderer',
        'mage/translate'
    ],
    function (ko, Component, $, alert, _, priceUtils, customerData) {
        'use strict';

        const ORDER_NO = '0';
        const ORDER_YES = '1';
        const ORDER_OUT_OF_STOCK = '2';
        return Component.extend({
            defaults: {
                template: 'Bss_ConfigurableProductWholesale/renderer'
            },
            selectAttr: ko.observable(),
            subtotal: ko.observable(),
            data: ko.observable(),
            orderQty: ko.observable(0),
            orderPrice: ko.observable(),
            orderPriceExclTax: ko.observable(),
            isSmall: ko.observable(window.matchMedia("(max-width: 480px)")),
            isMedium: ko.observable(window.matchMedia("(min-width: 481px) and (max-width: 1024px)")),
            isLarge: ko.observable(window.matchMedia("(min-width: 1025px)")),
            swatchConfig: ko.observable(),
            formSelector: '#product_addtocart_form',
            isIncrementQty: ko.observable(),
            isQtyError: ko.observable(),
            advancedTierPrice: ko.observable(),
            dataOrdered: ko.observable(),
            dataAjaxOrdered: ko.observableArray([]),
            dataAjaxCart: ko.observable(),
            sortOrder: ko.observable(1),
            customOption: ko.observable(),
            canUpdateQty: ko.observable(),
            mainProductSectionClass: '.product-info-main',
            supperAttributeClass: '#product_addtocart_form .super-attribute-select',
            ptdTableClass: '.bss-ptd-table',
            addToCartClass: '#product_addtocart_form',
            checkoutCartConfigureClass: '.checkout-cart-configure',
            bssTableRowClass: '.bss-table-row',
            lastSupperAttributeClass: '#product_addtocart_form .bss-last-select .super-attribute-select',
            optionPrice: '#bss-option-price',


            /**
             * Initializes class
             */
            initialize: function () {
                this._super()
                    .preSelectAttr();
                var self = this;

                this.removeFieldQty();
                if (!this.isAjaxLoad) {
                    this.data(_.values(this.productData));
                }
                this.convertProductOption();
                this.eventListener();
                this.customOption.subscribe(function () {
                    self.calculateTotal();
                });
                this.isPreOrder = false;
            },

            getTranslationTierPrice: function (qty, price, percent) {
                return $.mage.__('Buy %1 for %2 each and <strong> save %3%</strong>').replace('%1', qty).replace('%2', price).replace('%3', percent);
            },

            /**
             * Handle events like click or change
             */
            eventListener: function () {
                var self = this;

                if (this.jsonSystemConfig.hidePrice) {
                    $(this.formSelector).find('button[type=submit]').css('display','none');
                    var messageWarningPrice = $.mage.__('Can\'t add to cart right now. The price of this product is hidden for your customer group.');
                    $(this.mainProductSectionClass).append('<strong>' + messageWarningPrice + '</strong>');
                }

                if (this.isAjaxLoad) {
                    $(self.supperAttributeClass).on('change', function () {
                        var $this = $(this);
                        if ($this.closest('.bss-last-select').length === 0) {
                            self.loadTableData();
                        }
                    });
                    $(self.ptdTableClass).on('reloadTable', function () {
                        if ($(self.checkoutCartConfigureClass).length &&
                            (!$(self.bssTableRowClass).length || !$(self.supperAttributeClass).length || $(self.lastSupperAttributeClass).length)) {
                            self.loadTableData();
                        }
                    })
                } else {
                    $(self.supperAttributeClass).on('change', function () {
                        self.calculateTotal();
                    });
                }

                $(this.addToCartClass).on('change', self.optionPrice, function (event) {
                    var prices = {
                        price: event.target.value,
                        priceExcelTax: event.target.dataset.excltaxPrice
                    };
                    self.customOption(prices);
                });

                $(document).on('removeClass', function () {
                    self.removeSelectedClass();
                });
            },

            removeSelectedClass: function () {
                _.each(this.data(), function (product) {
                    product.is_selected = 0;
                });
            },

            loadNoSwatch: function (data, event) {
                var optionId,
                    target = $(event.target);
                if (data.is_selected) {
                    data.is_selected = 0;
                    $('.bss-last-select .super-attribute-select').val(0).change();
                } else {
                    target.trigger('removeClass');
                    data.is_selected = 1;
                    optionId = this.getAttrValue($(target.closest('.swatch-option')), 'option-id');
                    $('.bss-last-select .super-attribute-select').val(optionId).change();
                }
                target.trigger('reloadPrice');
            },

            addSelectedClass: function (data, event) {
                var target = $(event.target);
                if (data.is_selected) {
                    data.is_selected = 0;
                } else {
                    target.trigger('removeClass');
                    data.is_selected = 1;
                }
            },

            convertProductOption: function (productId) {
                var html = '';
                if (productId === 'undefined') {
                    return html;
                }
                _.each(this.productOptionsIndex[productId], function (option, id) {
                    html += '<input class="product-id-' + productId + ' bss-product-id" data-product-id="' + productId + '" type="hidden" name="bss_super_attribute[' + productId + '][' + id + ']" data-option-id="' + id + '" value="' + option + '">';
                });

                return html;
            },

            /**
             * Remove default input qty
             */
            removeFieldQty: function () {
                $('.product-options-bottom .box-tocart .field.qty').remove();
            },

            /**
             * Check device screen resolution
             * @returns {string}
             */
            checkDeviceScreen: function () {
                if (this.isSmall().matches && this.jsonSystemConfig.isEnableMobile) {
                    return 'mobile';
                }

                if (this.isMedium().matches && this.jsonSystemConfig.isEnableTablet) {
                    return 'tablet';
                }

                if (this.isLarge().matches) {
                    return 'desktop';
                }
            },

            /**
             * Check column is display
             * @param col
             * @returns {boolean}
             */
            isDisplayCol: function (col) {
                var device,
                    isDisplay = false;
                device  = this.checkDeviceScreen();
                if (!device) {
                    return true;
                }

                if (_.keys(this.jsonSystemConfig[device]).length === 0) {
                    device = 'desktop';
                }
                _.each(this.jsonSystemConfig[device], function (displayCol) {
                    if (col === displayCol) {
                        isDisplay = true;
                    }
                });
                return isDisplay;
            },
            /**
             * @param data
             * @returns {boolean|*}
             */
            checkSameTierPrice: function (data) {
                if (data) {
                    var tierPricesList = data.map(function (item) {
                        return item.tierPrice
                    });
                    if (tierPricesList.length >= 2 && tierPricesList.every((tierPrice) => tierPrice !== undefined)) {
                        return !tierPricesList.some((tier) => JSON.stringify(tier) !== JSON.stringify(tierPricesList[0]));
                    }
                }
                return false;
            },
            /**
             * Recalculate table
             */
            calculateTotal: function () {
                var qty = 0,
                    price = 0,
                    priceExclTax = 0,
                    data = ko.observableArray([]),
                    dataOrdered = ko.observableArray([]),
                    self = this,
                    i = 0,
                    orderQtyTierPrice = 0,
                    newDataOrdered,
                    sameTierPrice = this.checkSameTierPrice(this.data());
                _.each(this.data(), function (product) {
                    if (!product.order_qty) {
                        product.order_qty = 0;
                    }

                    if (product.order_qty >= 0) {
                        if (product.order_qty > 0) {
                            dataOrdered.push(product);
                            if (self.isAjaxLoad) {
                                var dataAjaxOrdered = self.dataAjaxOrdered();
                                _.each(dataAjaxOrdered, function (productOrdered) {
                                    if (product.id == productOrdered.id) {
                                        dataAjaxOrdered = _.without(dataAjaxOrdered, productOrdered);
                                    }
                                });

                                dataAjaxOrdered.push(product);
                                self.dataAjaxOrdered(dataAjaxOrdered);
                            }
                        }

                        if (product.tierPrice) {
                            var productTierPrice = 0,
                                orderedQty = 0,
                                hasTierPrice = false;

                            if (self.jsonSystemConfig.tierPriceAdvanced || sameTierPrice) {
                                if (self.isDecimalQty) {
                                    orderQtyTierPrice += parseFloat(product.order_qty);
                                } else {
                                    orderQtyTierPrice += parseInt(product.order_qty);
                                }
                            }

                            // calculate total qty when enable ajax load
                            if (self.isAjaxLoad && self.jsonSystemConfig.tierPriceAdvanced && sameTierPrice) {
                                _.each(self.dataAjaxOrdered(), function (product, id) {
                                    orderedQty += parseFloat(product.order_qty);
                                });
                                orderQtyTierPrice = orderedQty;
                            }

                            _.each(product.tierPrice, function (tierPrice) {
                                if (product.order_qty >= tierPrice.qty || orderQtyTierPrice >= tierPrice.qty) {
                                    productTierPrice = tierPrice;
                                    hasTierPrice = true;
                                }
                            });

                            if (hasTierPrice) {
                                self.recalculateTierPrice();
                                price += parseFloat(product.subtotal);
                                priceExclTax += parseFloat(product.subtotal_excl_tax);
                            }
                        } else {
                            self.calculatePrice(product);
                            price += parseFloat(product.subtotal);
                            priceExclTax += parseFloat(product.subtotal_excl_tax);
                        }

                        if (product.order_qty > 0 && self.incrementQty && product.order_qty % self.incrementQty !== 0) {
                            i++;
                        }

                        if (self.isDecimalQty) {
                            if (product.order_qty > 0) {
                                qty += parseFloat(product.order_qty);
                            }
                        } else {
                            if (product.order_qty > 0) {
                                qty += parseInt(product.order_qty);
                            }
                        }
                    }

                    if (!product.order_qty || product.order_qty < 0 || isNaN(product.order_qty)) {
                        product.order_qty = 0;
                    }

                    data.push(product);
                });

                if (this.incrementQty) {
                    if (i > 0) {
                        this.disableBtnCart();
                    } else {
                        this.enableBtnCart();
                    }
                }

                if (qty < 0) {
                    this.isQtyError(true);
                } else {
                    this.isQtyError(false);
                }

                this.orderQty(qty);
                if (this.jsonSystemConfig.tierPriceAdvanced && sameTierPrice) {
                    this.recalculateTotal(orderQtyTierPrice);
                } else {
                    this.orderPrice(this.getFormattedPrice(price));
                    this.orderPriceExclTax(this.getFormattedPrice(priceExclTax));
                }

                if (this.canUpdateQty() === undefined && !this.canUpdateQty()) {
                    this.updateCartQty(data());
                    this.canUpdateQty(false);
                }
                this.data([]);
                this.data(data());
                if (this.isAjaxLoad) {
                    newDataOrdered = this.prepareOrderedProduct(this.dataAjaxOrdered());
                } else {
                    newDataOrdered = this.prepareOrderedProduct(dataOrdered());
                }
                this.dataOrdered([]);
                this.dataOrdered(newDataOrdered);
            },

            updateCartQty: function (data) {
                var updateCart;
                if (window.jsonEditInfo && window.jsonEditInfo.hasOwnProperty('product')) {
                    updateCart = window.jsonEditInfo.product;
                }
                _.each(data, function (product, id) {
                    _.each(updateCart, function (ordered, orderedId) {
                        if (product.id == orderedId) {
                            product.order_qty = parseFloat(ordered.qty);
                        }
                    });
                });

                return data;
            },

            /**
             * Recalculate total order price
             * @param orderQtyTierPrice
             */
            recalculateTotal: function (orderQtyTierPrice) {
                var price = 0,
                    priceExclTax = 0,
                    productTierPrice = 0,
                    productTierPriceExclTax = 0,
                    subtotal = 0,
                    subtotal_excl_tax = 0,
                    data,
                    self = this,
                    customOptionPrice = $(self.optionPrice).val(),
                    customOptionPriceExlTax = this.getAttrValue($(self.optionPrice), 'data-excltax-price');
                price += parseFloat(customOptionPrice);
                priceExclTax += parseFloat(customOptionPriceExlTax);
                if (this.isAjaxLoad) {
                    data = this.dataAjaxOrdered();
                } else {
                    data = this.data();
                }

                _.each(data, function (product) {
                    if (product.tierPrice && product.order_qty > 0) {
                        _.each(product.tierPrice, function (tierPrice) {
                            if (orderQtyTierPrice >= tierPrice.qty) {
                                productTierPrice = tierPrice.price;
                                productTierPriceExclTax = tierPrice.price_excl_tax;
                            }
                        });

                        if (!productTierPrice) {
                            productTierPrice = product.current_price;
                            productTierPriceExclTax = product.current_price_excl_tax;
                        }
                        var customOptionPriceChild = 0,
                            customOptionPriceExlTaxChild = 0;
                        if ($(self.optionPrice +'-'+ product.id).length && $(self.optionPrice +'-'+ product.id).val() > 0) {
                            customOptionPriceChild = $(self.optionPrice +'-'+ product.id).val();
                            customOptionPriceExlTaxChild = self.getAttrValue($(self.optionPrice +'-'+ product.id), 'data-excltax-price');
                        }

                        productTierPrice = parseFloat(productTierPrice) + parseFloat(customOptionPriceChild);
                        productTierPriceExclTax = parseFloat(productTierPriceExclTax) + parseFloat(customOptionPriceExlTaxChild);

                        subtotal = parseFloat(product.order_qty) * parseFloat(productTierPrice);
                        subtotal_excl_tax = parseFloat(product.order_qty) * parseFloat(productTierPriceExclTax);
                        if (subtotal || subtotal === 0) {
                            product.price = productTierPrice;
                            product.price_excl_tax = productTierPriceExclTax;
                            product.subtotal = subtotal;
                            product.subtotal_excl_tax = subtotal_excl_tax;
                        }
                    }
                    price += parseFloat(product.subtotal);
                    priceExclTax += parseFloat(product.subtotal_excl_tax);
                });
                this.orderPrice(this.getFormattedPrice(price));
                this.orderPriceExclTax(this.getFormattedPrice(priceExclTax));
            },

            /**
             * Calculate price in table
             */
            recalculateTierPrice: function () {
                var self = this,
                    customOptionPrice = $(self.optionPrice).val(),
                    customOptionPriceExlTax = this.getAttrValue($(self.optionPrice), 'data-excltax-price'),
                    customOptionPriceChild = 0,
                    customOptionPriceExlTaxChild = 0;

                var data = [],
                    productTierPrice;

                _.each(self.data(), function (product) {
                    let orderQtyTierPrice = 0,
                        check = false;

                    if (product.tierPrice && product.checkAdvanceTierPrice) {
                        _.each(Object.values(product.checkAdvanceTierPrice), function (id) {
                            _.each(self.data(), function (item) {
                                if (item.order_qty < 0 || isNaN(item.order_qty) || !item.order_qty) {
                                    item.order_qty = 0;
                                }
                                if (self.jsonSystemConfig.tierPriceAdvanced) {
                                    if (id == item.id) {
                                        orderQtyTierPrice += parseInt(item.order_qty);
                                    }
                                } else {
                                    if (id == item.id && id == product.id) {
                                        orderQtyTierPrice += parseInt(item.order_qty);
                                    }
                                }
                            });
                        });

                        _.each(product.tierPrice, function (tierPrice) {
                            if (orderQtyTierPrice >= tierPrice.qty || product.order_qty >= tierPrice.qty) {
                                productTierPrice = tierPrice.price;
                                check = true;
                            }
                        });
                    }

                    if (!productTierPrice || !check) {
                        productTierPrice = product.old_price;
                    }

                    if (product.special_price && product.special_price < productTierPrice) {
                        productTierPrice = product.special_price;
                    }

                    if ($(self.optionPrice +'-'+ product.id).length && $(self.optionPrice +'-'+ product.id).val() > 0) {
                        customOptionPriceChild = $(self.optionPrice +'-'+ product.id).val();
                        customOptionPriceExlTaxChild = self.getAttrValue($(self.optionPrice +'-'+ product.id), 'data-excltax-price');
                    }

                    product.price = parseFloat(productTierPrice) + parseFloat(customOptionPrice) + parseFloat(customOptionPriceChild);
                    product.price_excl_tax = parseFloat(productTierPrice) + parseFloat(customOptionPriceExlTax) + parseFloat(customOptionPriceExlTaxChild);
                    product.subtotal = parseFloat(product.order_qty) * parseFloat(product.price);
                    product.subtotal_excl_tax = parseFloat(product.order_qty) * parseFloat(product.price_excl_tax);

                    data.push(product);
                });
                this.data([]);
                this.data(data);
            },

            /**
             * Calculate product price when input number is integer
             * @param product
             * @returns {*}
             */
            calculatePrice: function (product) {
                var self = this,
                    customOptionPrice = $(self.optionPrice).val(),
                    customOptionPriceExlTax = this.getAttrValue($(self.optionPrice), 'data-excltax-price'),
                    customOptionPriceChild = 0,
                    customOptionPriceExlTaxChild = 0,
                    orderQty;

                if (this.isDecimalQty) {
                    orderQty = parseFloat(product.order_qty);
                } else {
                    orderQty = parseInt(product.order_qty);
                }

                if ($(self.optionPrice +'-'+ product.id).length && $(self.optionPrice +'-'+ product.id).val() > 0) {
                    customOptionPriceChild = $(self.optionPrice +'-'+ product.id).val();
                    customOptionPriceExlTaxChild = this.getAttrValue($(self.optionPrice +'-'+ product.id), 'data-excltax-price');
                }

                product.price = parseFloat(product.current_price) + parseFloat(customOptionPrice) + parseFloat(customOptionPriceChild);
                product.price_excl_tax = parseFloat(product.current_price_excl_tax) + parseFloat(customOptionPriceExlTax) + parseFloat(customOptionPriceExlTaxChild);

                product.subtotal = orderQty * parseFloat(product.price);
                product.subtotal_excl_tax = orderQty * parseFloat(product.price_excl_tax);
                if (product.order_qty === '' || product.order_qty < 0) {
                    product.subtotal = 0;
                    product.subtotal_excl_tax = 0;
                }

                return product;
            },

            /**
             * Check button addtocart is visible
             */
            enableBtnCart: function () {
                $(this.formSelector).find('button[type=submit]').removeAttr('disabled');
            },

            /**
             * Disable button addtocart
             */
            disableBtnCart: function () {
                $(this.formSelector).find('button[type=submit]').attr('disabled', 'disabled');
            },

            /**
             * Auto select first attribute
             */
            preSelectAttr: function () {
                var cartData = customerData.get('cart')(),
                    productId = $('#product_addtocart_form [name="product"]').val(),
                    orderedData,
                    self = this,
                    productIndex = [],
                    quoteIndex = [];

                if (this.noSwatch === "1" && !this.jsonSystemConfig.hasOwnProperty('preselect')) {
                    $(document).trigger('updateProductBaseImage');
                }

                if (!$('#product-updatecart-button').length) {
                    this.updatePreselect();
                }

                if (cartData && cartData.items && cartData.items.length && productId) {
                    _.each(cartData.items, function (product, id) {
                        var optionId = [],
                            optionValue = [],
                            option;
                        if (productId === product.product_id && !_.isEmpty(product.options)) {
                            _.each(product.options, function (item) {
                                if (item.option_id !== undefined && item.option_value !== undefined) {
                                    optionId.push(item.option_id);
                                    optionValue.push(item.option_value);
                                }
                            });
                            option = _.object(optionId, optionValue);
                            _.each(self.productOptionsIndex, function (item, index) {
                                if (_.isEqual(item, option)) {
                                    productIndex.push(index);
                                    quoteIndex.push(product.item_id);
                                }
                            });
                        }
                    })
                    orderedData = _.object(productIndex, quoteIndex);
                    orderedData = JSON.stringify(orderedData);
                    $('#bss-updatecart-data').val(encodeURI(orderedData));
                }

                if (this.isAjaxLoad && _.isEmpty(window.checkout.bssConfigurableWholesale)) {
                    this.loadTableData();
                }
            },


            cartDataInput: function () {
                var cartData = customerData.get('cart')(),
                    productId = $('#product_addtocart_form [name="product"]').val(),
                    orderedData,
                    self = this,
                    productIndex = [],
                    quoteIndex = [];

                _.each(cartData.items, function (product, id) {
                    var optionId = [],
                        optionValue = [],
                        option;
                    if (productId === product.product_id && !_.isEmpty(product.options)) {
                        _.each(product.options, function (item) {
                            if (item.option_id !== undefined && item.option_value !== undefined) {
                                optionId.push(item.option_id);
                                optionValue.push(item.option_value);
                            }
                        });
                        option = _.object(optionId, optionValue);
                        _.each(self.productOptionsIndex, function (item, index) {
                            if (_.isEqual(item, option)) {
                                productIndex.push(index);
                                quoteIndex.push(product.item_id);
                            }
                        });
                    }
                })
                orderedData = _.object(productIndex, quoteIndex);
                orderedData = JSON.stringify(orderedData);
                return encodeURI(orderedData);
            },

            /**
             * Load table data by ajax
             */
            loadTableData: function () {
                var options = {},
                    data = [],
                    self = this,
                    attrId,
                    attrVal,
                    attributeSelectElm = self.supperAttributeClass,
                    attrCount = 0,
                    error = false;

                $(attributeSelectElm).each(function () {
                    var $this = $(this);
                    if ($this.hasClass('.bss') || $this.closest('.bss-last-select').length) {
                        attrCount++;
                    }
                    if (!$this.closest('div.field').hasClass('bss-last-select') && !$this.hasClass('bss')) {
                        attrId = self.getAttrValue($this.closest('.swatch-attribute'), 'attribute-id');
                        attrVal = self.getAttrValue($this.closest('.swatch-attribute'), 'data-option-selected');
                        if ($this.val()) {
                            if (!attrVal) {
                                attrVal = $this.val();
                                if (typeof attrVal == "undefined") {
                                    attrVal = self.getAttrValue($this.parent().find('.swatch-option:eq(0)'), 'option-id');
                                }
                            }
                            if (self.getAttrValue($this, 'name') != '') {
                                attrId = self.getAttrValue($this, 'name').toString().replace(/^\D+|\D+$/g, "");
                                if (typeof attrId == "undefined") {
                                    attrId = self.getAttrValue($this.closest('.swatch-attribute'), 'attribute-id');
                                }
                            }
                            data.push(attrId + '_' + attrVal);
                        }

                        if ($this.closest('.swatch-attribute').find('.swatch-select').is("select") && typeof attrVal == "undefined") {
                            attrVal = $this.closest('.swatch-attribute').find('option:eq(1)').val();
                            data.push(attrId + '_' + attrVal);
                        }
                        if (_.isEmpty(data)) {
                            attrVal = self.getAttrValue($this.parent().find('.swatch-option:eq(0)'), 'option-id');
                            attrId = self.getAttrValue($this.closest('.swatch-attribute'), 'attribute-id');
                            data.push(attrId + '_' + attrVal);
                        }
                        if (typeof attrId == "undefined" || typeof attrVal == "undefined") {
                            error = true;
                        }
                    }
                });
                options.productId = $('#product_addtocart_form input[name=product]').val();
                options.option = data;

                if (error && this.jsonSystemConfig.enableSDCP) {
                    return;
                }

                if ($(attributeSelectElm).length > 0 && !_.isEmpty(options.option) || attrCount == $(attributeSelectElm).length) {
                    $.ajax({
                        type: 'post',
                        url: this.jsonSystemConfig.ajaxLoadUrl,
                        data: {options: JSON.stringify(options)},
                        dataType: 'json',
                        beforeSend: function () {
                            $('div.bss-ptd-table').addClass('bss-cwd-spinner');
                        },
                        success: function (data) {
                            self.addQtyToData(data);
                            $(document).trigger('contentUpdated');
                        },
                        complete: function () {
                            $('div.bss-ptd-table').removeClass('bss-cwd-spinner');
                        }
                    });
                }
            },

            addQtyToData: function (data) {
                var self = this,
                    updateCart,
                    isUpdateCart;
                if (window.jsonEditInfo && window.jsonEditInfo.hasOwnProperty('product')) {
                    isUpdateCart = true;
                    updateCart = window.jsonEditInfo.product;
                } else {
                    updateCart = self.dataAjaxOrdered();
                }
                _.each(data, function (product, id) {
                    _.each(updateCart, function (ordered, orderedId) {
                        if (isUpdateCart) {
                            if (product.id == orderedId) {
                                product.order_qty = parseFloat(ordered.qty);
                            }
                        } else {
                            if (product.id == ordered.id) {
                                product.order_qty = parseFloat(ordered.order_qty);
                            }
                        }
                    });
                });
                this.data(data);
                this.calculateTotal();
            },

            /**
             * Get table attribute
             * @param productId
             * @returns {string}
             */
            getAttributeId: function (productId) {
                var productOptions = '';
                _.each(this.attributeData.options, function (attributes) {
                    _.each(attributes.products, function (product) {
                        if (productId === product) {
                            productOptions = attributes.id;
                        }
                    })
                });

                return productOptions;
            },

            /**
             * Get table option label
             * @param productId
             * @param optionId
             * @returns {string}
             */
            getOptionLabel: function (productId, optionId) {
                var attributeId = this.getOptionId(optionId, productId);
                var label = '';
                _.each(this.attributeData.options, function (option) {
                    if (attributeId === option.id) {
                        label = option.label;
                    }
                });
                return label;
            },

            getOptionId: function (attributeId, productId) {
                if (this.productOptionsIndex.hasOwnProperty(productId)) {
                    return this.productOptionsIndex[productId][attributeId];
                }
            },

            /**
             * Check row is display
             * @param productId
             * @returns {boolean}
             */
            isDisplayed: function (productId) {
                var isDisplay = false;
                var currentAttr = this.selectAttr;
                var self = this;

                if (self.isAjaxLoad) {
                    isDisplay = true;
                } else {
                    if (typeof currentAttr == 'function') {
                        currentAttr = $('.super-attribute-select').val();
                        if (!currentAttr && this.noSwatch == "1") {
                            var detailedEl = $('#product-options-wrapper .swatch-attribute:first');
                            currentAttr = detailedEl.find('option:eq(1)').val();
                        }
                    }

                    if ($('.swatch-opt .swatch-attribute').length >= 1) {
                        var preselect = {};
                        $('.swatch-opt .swatch-attribute').each(function (id, element) {
                            var selectAttr = self.getAttrValue($(element), 'attribute-id');
                            var swatchItem = $(element).find('.swatch-option'),
                                swatchDropItem = $(element).find('.swatch-select'),
                                selectOption;
                            if (swatchItem.length) {
                                swatchItem.each(function (id, item) {
                                    if ($(item).hasClass('selected')) {
                                        selectOption = self.getAttrValue($(item), 'option-id');
                                    }
                                });

                                if (!selectOption) {
                                    if ($(element).find('[option-id]').is(':selected')) {
                                        var selectElement = $(element).find('select');
                                        if (selectElement.val() > 0) {
                                            selectOption = selectElement.val();
                                        } else {
                                            selectOption = $(element).find('select option:eq(1)').attr('select', 'select').val();
                                        }
                                    } else {
                                        selectOption = self.getAttrValue($(element).find('.swatch-option').first(), 'option-id');
                                    }
                                }
                                preselect[selectAttr] = selectOption;
                            } else if (swatchDropItem.length) {
                                swatchDropItem.each(function (idSelect, itemSelect) {
                                    if ($(itemSelect).val()) {
                                        selectOption = $(itemSelect).val();
                                    }
                                });
                                if (!selectOption || selectOption == 0) {
                                    console.log($(element).find('.swatch-select').eq(0).find('option').eq(1));
                                    selectOption = $(element).find('.swatch-select').eq(0).find('option').eq(1).attr('value');
                                }
                                preselect[selectAttr] = selectOption;
                            }

                        });
                        _.each(self.productOptionsIndex, function (products, id) {
                            if (productId === id) {
                                isDisplay = true;
                                _.each(preselect, function (option, attr) {
                                    if (typeof option == "number") {
                                        option = option.toString();
                                    }
                                    if (!_.contains(products, option)) {
                                        isDisplay = false;
                                    }
                                });
                            }
                        });
                    } else {
                        if (this.noSwatch == "1") {
                            var selectedAttr = [];
                            var selectedValue = [];
                            $('#product-options-wrapper .super-attribute-select').each(function () {
                                var error = false;
                                var selectEl = $(this).parents('.configurable');
                                if (selectEl.hasClass('bss-last-select') || $(this).hasClass('bss')) {
                                    error = true;
                                }
                                if (!error) {
                                    var value = $(this).val();
                                    var attr = self.getAttrValue($(this), 'attribute-id');
                                    var defaultValue = $(this).find('option:eq(1)').val();
                                    if (!value) {
                                        value = defaultValue;
                                    }
                                    selectedAttr.push(attr);
                                    selectedValue.push(value);
                                }
                            });
                            currentAttr = _.object(selectedAttr, selectedValue);
                            if (_.isMatch(this.productOptionsIndex[productId], currentAttr)) {
                                isDisplay = true;
                            }
                        }
                    }

                    if ($('.swatch-opt .swatch-attribute').length === 0 && this.noSwatch !== "1") {
                        isDisplay = true;
                    }

                    if ($('.product-options-wrapper .configurable').length === 1 && this.noSwatch === "1") {
                        isDisplay = true;
                    }
                }

                return isDisplay;
            },

            updatePreselect: function () {
                var customUrl,
                    selectedAttr;

                customUrl = $(location).attr('href');
                selectedAttr = customUrl.split('+');

                if (selectedAttr.length > 1 && selectedAttr[length - 1] !== 'sdcp-redirect') {
                    if (selectedAttr.length >= 2) {
                        selectedAttr.shift();
                        if (this.noSwatch === "0") {
                            this.emulateSelected(selectedAttr, 'code');
                        } else {
                            this.emulateChanges(selectedAttr, 'code');
                        }
                    }

                    if (selectedAttr.length < 2 && this.noSwatch === "1") {
                        setTimeout(function () {
                            $('.super-attribute-select').each(function () {
                                var _this = this;
                                var value = $(_this).find('option:eq(1)').val();
                                if (!$(_this).val() && !$(_this).parent().parent().hasClass('bss-last-select')) {
                                    $(_this).val(value).trigger('change');
                                }
                            });
                        }, 1000);
                    }
                } else {
                    if (this.jsonSystemConfig.hasOwnProperty('preselect')) {
                        var preselect = this.jsonSystemConfig.preselect;

                        if (this.noSwatch === "0") {
                            this.emulateSelected(preselect, 'id');
                        } else {
                            this.emulateChanges(preselect, 'code');
                        }
                    }
                }
            },

            emulateChanges: function (selectedAttr, attrType) {
                var $code,
                    $value;

                if (attrType == 'code') {
                    $.each(selectedAttr, function ($index, $vl) {
                        if (typeof $vl === 'string') {
                            $code = $vl.substring(0, $vl.indexOf('-'));
                            $value = $vl.substring($code.length + 1);
                            $value = $value.replace(/~/g, '');
                            $value = decodeURIComponent($value);

                            try {
                                $('.configurable #attribute'
                                    + $index
                                    + '').val($vl).trigger('change');
                                return true;
                            } catch (e) {
                                console.log($.mage.__('Error when get product from urls'));
                            }
                        }
                    });
                } else {
                    $.each(selectedAttr, function ($index, $vl) {
                        try {
                            $('.configurable #attribute'
                                + $index
                                + '').val($vl).trigger('change');
                        } catch (e) {
                            console.log($.mage.__('Error when applied preselect product'));
                        }
                    });
                    return true;
                }
            },

            emulateSelected: function (selectedAttr, attrType) {
                var $code,
                    $value,
                    self = this;
                var swSelector = '';
                if ($('.swatch-attribute[data-attribute-code] .swatch-attribute-options').length) {
                    swSelector = 'data-';
                }

                if (attrType == 'code') {
                    //Selected table CPWD by url
                    var url = $(location).attr('href');
                    if (url.split('+')) {
                        window.paramSelected = url.split('+');
                        window.paramSelected.shift();
                    }

                    $.each(selectedAttr, function ($index, $vl) {
                        if (typeof $vl === 'string') {
                            $code = $vl.substring(0, $vl.indexOf('-'));
                            $value = $vl.substring($code.length + 1);
                            $value = $value.replace(/~/g,'');
                            $value = decodeURIComponent($value);

                            try {
                                if ($('.swatch-attribute[' + swSelector + 'attribute-code="'
                                    + $code
                                    + '"] .swatch-attribute-options').children().is('div')) {
                                    $('.swatch-attribute[' + swSelector + 'attribute-code="' + $code + '"] .swatch-attribute-options .swatch-option').each(function () {
                                        var optionLable = self.getAttrValue($(this), 'option-label').toString().replace(/[~`!@#$%^&*()_|\;:'",.<>\{\}\[\]\\\/]/g,'').replace(/\s/g,'');
                                        optionLable = $.trim(optionLable);
                                        if (optionLable == $value) {
                                            if (this.isAjaxLoad) {
                                                setTimeout(function () {
                                                    $(this).trigger('click');
                                                    return false;
                                                })
                                            } else {
                                                $(this).trigger('click');
                                                return false;
                                            }
                                        }
                                    });
                                } else {
                                    $.each($('.swatch-attribute[' + swSelector + 'attribute-code="'
                                        + $code
                                        + '"] .swatch-attribute-options select option'), function ($index2, $vl2) {
                                        if ($vl2.text == decodeURIComponent($value)) {
                                            $('.swatch-attribute[' + swSelector + 'attribute-code="'
                                                + $code
                                                + '"] .swatch-attribute-options select').val($vl2.value).trigger('change');
                                        }
                                    })
                                }
                            } catch (e) {
                                console.log($.mage.__('Error when get product from urls'));
                            }
                        }
                    });
                } else {
                    $.each(selectedAttr, function ($index, $vl) {
                        try {
                            if ($('.swatch-attribute[' + swSelector + 'attribute-id='
                                + $index
                                + '] .swatch-attribute-options').children().is('div')
                            ) {
                                $('.swatch-attribute[' + swSelector + 'attribute-id='
                                    + $index
                                    + '] .swatch-attribute-options [' + swSelector + 'option-id='
                                    + $vl
                                    + ']').trigger('click');
                            } else {
                                //Option in table CPWD
                                window.paramPreSelect = [$index + "-" + $vl];
                            }
                        } catch (e) {
                            console.log($.mage.__('Error when applied preselect product'));
                        }
                    });
                }
            },

            /**
             * Format price with currency code
             * @param price
             * @returns {*}
             */
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, this.fomatPrice);
            },

            /**
             * Check product is stock
             * @param quantity
             * @param id
             * @returns {boolean}
             */
            isOutOfStock: function (quantity, id) {
                var status = false,
                    currentDate = new Date();
                if (quantity === "Out of stock") {
                    status = this.checkPreOrderStatus(id, currentDate);
                }
                return status;
            },
            /**
             * Get data PreOrder
             * @param productId
             * @returns {*}
             */
            dataPreOrder: function (productId) {
                var self = this;
                if(self.jsonConfig.pre_order_status !== undefined) {
                    var data = self.jsonConfig.pre_order_status.child[productId];
                    data.isPreOrder = this.isPreOrderProduct(data.pre_order_status,data.stock_status,data.availability_preorder);
                    return self.jsonConfig.pre_order_status.child[productId];
                } else {
                    return false;
                }
            },
            /**
             *  Check Is PreOrder
             * @param pre_order_status
             * @param stock_status
             * @param availability_preorder
             * @returns {boolean}
             */
            isPreOrderProduct: function (pre_order_status,stock_status,availability_preorder) {
                if ((pre_order_status === ORDER_YES && availability_preorder) || (!stock_status && pre_order_status === ORDER_OUT_OF_STOCK)) {
                    return true;
                }
                return false;
            },

            checkPreOrderStatus: function (id,currentDate) {
                var self = this,
                    status = false;
                if (self.jsonConfig &&
                    self.jsonConfig.pre_order_status &&
                    self.jsonConfig.pre_order_status.child &&
                    self.jsonConfig.pre_order_status.child[id] &&
                    self.jsonConfig.pre_order_status.child[id].pre_order_status) {
                    if (self.jsonConfig.pre_order_status.child[id].pre_order_status == 1) {
                        var toDate = self.jsonConfig.pre_order_status.child[id].pre_order_to_date,
                            fromDate = self.jsonConfig.pre_order_status.child[id].pre_order_from_date,
                            currentTime = currentDate.getTime();
                        if (toDate && Date.parse(toDate) < currentTime
                            || fromDate && Date.parse(fromDate) > currentTime
                        ) {
                            status = true;
                        }
                    }
                    return status;
                }
                return status;
            },

            updateData: function (data) {
                var self = this;
                _.each(this.data(), function (product) {
                    _.each(data.product, function (cartProduct, cartProductId) {
                        if (product.id === cartProductId) {
                            product.order_qty = cartProduct.qty;
                            product.is_update_item = 1;
                            product.is_update_value = data.item[cartProductId];
                        }

                        if (product.id === data.default) {
                            product.is_selected = 1;
                        }
                    });
                });
                self.calculateTotal();
            },

            getAttrOptionLabel: function (productId) {
                var detailedEl,
                    selectedAttr,
                    attrId,
                    text,
                    optionConfig;

                if (this.noSwatch == "1") {
                    detailedEl = $('#product-options-wrapper .swatch-attribute:first');
                } else {
                    detailedEl = $('[data-role=swatch-options] .swatch-attribute:first');
                }

                attrId = this.getAttrValue(detailedEl, 'attribute-id');
                selectedAttr = this.productOptionsIndex[productId][attrId];
                optionConfig = this.jsonSwatchConfig[attrId];

                if (typeof optionConfig != "undefined") {
                    text = optionConfig[selectedAttr].label ? optionConfig[selectedAttr].label : '';
                } else {
                    if (this.noSwatch == "1") {
                        text = detailedEl.find('option[value='+selectedAttr+']').text();
                    } else {
                        text = detailedEl.find('.swatch-select option[option-id='+selectedAttr+']').text();
                    }
                }

                return text;
            },

            getAttrOptionValue: function (productId) {
                var detailedEl,
                    selectedAttr,
                    attrId;
                detailedEl = $('[data-role=swatch-options] .swatch-attribute:first');
                attrId = this.getAttrValue(detailedEl, 'attribute-id');
                selectedAttr = this.productOptionsIndex[productId][attrId];
                if (this.jsonSwatchConfig.hasOwnProperty(attrId)) {
                    return this.jsonSwatchConfig[attrId][selectedAttr].value;
                } else {
                    _.each(this.swatchAtrributeData.attributes[attrId].options, function (option) {
                        if (selectedAttr == option.id) {
                            return option.value;
                        }
                    })
                }
            },

            prepareOrderedProduct: function (products) {
                var optionLabel,
                    self = this,
                    total = {},
                    ids = [],
                    newProduct = [],
                    optionValue,
                    totalArray,
                    error,
                    attributeLength,
                    productQty = 0,
                    price = 0,
                    priceExclTax = 0,
                    cartProductData = [],
                    labelHtml;

                if (this.noSwatch == "1") {
                    attributeLength = $('.super-attribute-select').length;
                    if (attributeLength < 2) {
                        error = true;
                    }
                } else {
                    attributeLength = $('.swatch-opt .swatch-attribute').length;
                    if (attributeLength < 1) {
                        error = true;
                    }
                }

                if (error) {
                    return;
                }

                _.each(products, function (product, id) {
                    if (product.order_qty > 0) {
                        productQty += parseFloat(product.order_qty);
                        optionLabel = self.getAttrOptionLabel(product.id);
                        price += parseFloat(product.subtotal);
                        priceExclTax += parseFloat(product.subtotal_excl_tax);
                        labelHtml = self.getAttrOptionHtml(product.id);
                        if (self.noSwatch === "0") {
                            optionValue = self.getAttrOptionValue(product.id);
                        }
                        if (_.indexOf(ids, optionLabel) === -1) {
                            newProduct = {
                                label: optionLabel,
                                order_qty: parseFloat(product.order_qty),
                                value: optionValue,
                                html: labelHtml
                            };
                            total[optionLabel] = newProduct;
                            ids.push(optionLabel);
                        } else {
                            var qty = parseFloat(newProduct.order_qty);
                            qty += parseFloat(product.order_qty);
                            total[optionLabel].order_qty = qty;
                        }
                        cartProductData.push(product);
                    }
                });
                this.orderQty(productQty);
                this.orderPrice(this.getFormattedPrice(price));
                this.orderPriceExclTax(this.getFormattedPrice(priceExclTax));
                this.dataAjaxOrdered([]);
                this.dataAjaxOrdered(cartProductData);
                totalArray = _.values(total);
                return totalArray;
            },

            prepareCartData: function () {
                var cartData = {},
                    self = this,
                    data;
                if (this.isAjaxLoad) {
                    data = this.dataAjaxOrdered();
                } else {
                    data = this.data();
                }
                _.each(data, function (product, id) {
                    var dataAttr = {};
                    if (product.order_qty > 0) {
                        cartData[product.id] = {};
                        cartData[product.id]['qty'] = product.order_qty;
                        cartData[product.id]['data'] = product.order_qty;
                        _.each(self.productOptionsIndex[product.id], function (attr, id) {
                            var key = 'data-option-' + id;
                            dataAttr[key] = attr;
                        });

                        cartData[product.id]['data'] = dataAttr;
                    }
                });
                return encodeURI(JSON.stringify(cartData));
            },

            sortTableData: function (data, event) {
                var col = event.target.className,
                    newData;
                if (this.sortOrder) {
                    newData = _.sortBy(this.data(), col);
                    this.sortOrder = 0;
                } else {
                    newData = _.sortBy(this.data(), col).reverse();
                    this.sortOrder = 1;
                }
                this.data([]);
                this.data(newData);
            },

            getAttrOptionHtml: function (productId) {
                var detailedEl,
                    selectedAttr,
                    attrId,
                    html,
                    text,
                    self = this;

                if (this.noSwatch == "1") {
                    detailedEl = $('#product-options-wrapper .swatch-attribute:first');
                } else {
                    detailedEl = $('[data-role=swatch-options] .swatch-attribute:first');
                }

                attrId = self.getAttrValue(detailedEl, 'attribute-id');
                selectedAttr = this.productOptionsIndex[productId][attrId];
                html = '';
                if (typeof this.jsonSwatchConfig[attrId] != "undefined") {
                    html += self.renderOptionHtml(attrId, selectedAttr);
                } else {
                    if (this.noSwatch == "1") {
                        text = detailedEl.find('option[value='+selectedAttr+']').text();
                    } else {
                        text = detailedEl.find('.swatch-select option[option-id='+selectedAttr+']').text();
                    }
                    html += '<div>' + text + '</div>';
                }
                return html;
            },

            renderOptionHtml: function (attrId, id) {
                var html = '';

                if (!this.jsonSwatchConfig.hasOwnProperty(attrId)) {
                    _.each(this.attributeData.options, function (options) {
                        if (id === options.id) {
                            html = '<div>' + options.label + '</div>';
                        }
                    })
                } else {
                    var optionConfig = this.jsonSwatchConfig[attrId],
                        type = parseInt(optionConfig[id].type, 10),
                        value = optionConfig[id].hasOwnProperty('value') ? optionConfig[id].value : '',
                        optionClass = 'swatch-option',
                        label = optionConfig[id].label ? optionConfig[id].label : '',
                        attr =
                            ' option-type="' + type + '"' +
                            ' option-id="' + id + '"' +
                            ' option-label="' + label + '"';
                    if (type === 0) {
                        // Text
                        html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) +
                            '</div>';
                    } else if (type === 1) {
                        // Color
                        html += '<div class="' + optionClass + ' color" ' + attr +
                            '" style="background: ' + value +
                            ' no-repeat center; background-size: initial;">' + '' +
                            '</div>';
                    } else if (type === 2) {
                        // Image
                        html += '<div class="' + optionClass + ' image" ' + attr +
                            '" style="background: url(' + value + ') no-repeat center; background-size: initial;">' + '' +
                            '</div>';
                    } else if (type === 3) {
                        // Clear
                        html += '<div class="' + optionClass + '" ' + attr + '></div>';
                    } else {
                        // Default
                        html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                    }
                }
                return html;
            },

            showTierPrice: function (data, event) {
                var target = $(event.target).parents('.bss-table-row'),
                    qtyBox = $(target).find('.bss-qty'),
                    allow_pre_order = $(target).find('.allow_pre_order'),
                    height = $(target).find('.bss-tier-detailed').height(),
                    width = $(target).find('.bss-tier-detailed').width(),
                    heightPreoder = $(target).find('.bss-pre-order').height(),
                    widthPreoder = $(target).find('.bss-pre-order').width(),
                    currenttarget = $(event.target),
                    top = 0,
                    left = 0,
                    preOderTop = 0,
                    preOderLeft = 0;
                if (typeof $(qtyBox).offset() !== 'undefined') {
                    top = $(qtyBox).offset().top;
                    left = $(qtyBox).offset().left;
                }
                if (currenttarget.hasClass('allow_pre_order')) {
                    if (typeof $(allow_pre_order).offset() !== 'undefined') {
                        preOderTop = $(allow_pre_order).offset().top;
                        preOderLeft = $(allow_pre_order).offset().left;
                    }
                    $(target).find('.bss-pre-order').css({'display': 'block', 'position': 'fixed', 'top': preOderTop - heightPreoder - window.pageYOffset - 30, 'left': preOderLeft - widthPreoder - window.pageXOffset + 80});
                } else {
                    $(target).find('.bss-tier-detailed').css({'display': 'block', 'position': 'fixed', 'top': top - height - window.pageYOffset - 30, 'left': left - width - window.pageXOffset + 30});
                }
            },

            hideTierPrice: function (data, event) {
                var target = $(event.target).parents('.bss-table-row'),
                    currenttarget = $(event.target);
                if (currenttarget.hasClass('allow_pre_order')) {
                    $(target).find('.bss-pre-order').css({'display': 'none'});
                } else {
                    $(target).find('.bss-tier-detailed').css({'display': 'none'});
                }
            },

            updateInputQty: function (dataProduct, event) {
                this.recalculateTierPrice();

                if (this.jsonConfig.pre_order_status) {
                    if (this.jsonConfig.pre_order_status.child) {
                        if (this.jsonConfig.pre_order_status.child[dataProduct.id]) {
                            let self = this,
                                data = this.data(),
                                cartData = JSON.parse(localStorage['mage-cache-storage']).cart,
                                cartQty = [];
                            if (cartData !== undefined) {
                                if (cartData.items.length) {
                                    _.each(data, function (item, id) {
                                        if (item.product_id == self.jsonConfig.productId) {
                                            let childId = Object.keys(self.jsonConfig.pre_order_status.child).find(key => self.jsonConfig.pre_order_status.child[key]['sku'] === item.product_sku);
                                            cartQty[childId] = item.qty;
                                        }
                                    });
                                }
                            }
                            let preorder = false, notPreOrder = false, i = 0;
                            _.each(data, function (product, id) {
                                let productOrderQty = product.order_qty;
                                if (cartQty[product.id]) {
                                    productOrderQty += cartQty[product.id];
                                }
                                if (productOrderQty > 0) {
                                    i++;
                                    let preOrderProduct = self.jsonConfig.pre_order_status.child[product.id];
                                    if ((preOrderProduct.pre_order_status == 1 && preOrderProduct.availability_preorder)
                                        || (preOrderProduct.pre_order_status == 2 && !preOrderProduct.stock_status)
                                        || (preOrderProduct.pre_order_status == 2 && parseFloat(product.order_qty) > parseFloat(product.saleable_quantity))
                                    ) {
                                        preorder = true;
                                    } else {
                                        notPreOrder = true;
                                    }
                                }
                            });

                            if (!this.jsonConfig.preorder_allow_mixin) {
                                if (i > 1 && preorder && notPreOrder) {
                                    alert({
                                        content: $.mage.__("We could not add both pre-order and regular items to an order")
                                    })
                                    $('#bss-qty-' + dataProduct.id).val(0).change();
                                }
                            }
                            if (!this.defaultBtnAddToCartText) {
                                this.defaultBtnAddToCartText = $('#product_addtocart_form .action.tocart').text();
                            }
                            if (dataProduct.order_qty > 0) {
                                if (preorder && !notPreOrder) {
                                    $('#product_addtocart_form .action.tocart').text($.mage.__(this.jsonConfig.pre_order_status.child[dataProduct.id].button));
                                } else {
                                    $('#product_addtocart_form .action.tocart').text($.mage.__(this.defaultBtnAddToCartText));
                                }
                            }
                            if (!preorder && !notPreOrder) {
                                $('#product_addtocart_form .action.tocart').text($.mage.__(this.defaultBtnAddToCartText));
                            }
                        }
                    }
                }
                this.canUpdateQty(true);
                this.calculateTotal();
            },

            /**
             * Selected option in table work with config redirect module SDCP.
             *
             * @returns {void}
             */
            selectedRedirectSDCP: function () {
                var attrOption = '';
                if (window.paramRedirect) {
                    attrOption = window.paramRedirect;
                } else if (window.paramSelected) {
                    attrOption = window.paramSelected;
                } else if (window.paramPreSelect) {
                    attrOption = window.paramPreSelect;
                }

                if (attrOption) {
                    var sdcp_redirect = attrOption[attrOption.length - 1].split('-').pop();

                    if (sdcp_redirect) {
                        var itemSelectedLabel = $(`.bss-table-row-attr[option-label='${sdcp_redirect}']`);
                        var itemSelectedId = $(`.bss-table-row-attr[option-id='${sdcp_redirect}']`);

                        if (itemSelectedLabel.length) {
                            setTimeout(function () {
                                itemSelectedLabel.click();
                            }, (200));
                        }

                        if (itemSelectedId.length) {
                            setTimeout(function () {
                                itemSelectedId.click();
                            }, (200));
                        }
                    }
                }
            },

            /**
             * Get attribute value in two case
             * @param $ele
             * @param $attr
             * @returns {*}
             */
            getAttrValue($ele, $attr) {
                if ($ele.attr($attr)) {
                    return $ele.attr($attr);
                }
                return $ele.data($attr);
            }
        });
    },
);
