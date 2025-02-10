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
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    'mage/translate',
    'Bss_ProductStockAlert/js/model/product/configurable',
    'Bss_ProductStockAlert/js/model/product/configurable-price'
], function ($, _, $t, configurableModel, configurablePriceModel) {
    'use strict';

    return function (widget) {
        $.widget('bss.swatch', widget, {
            _create: function () {
                this._super();
                let $widget = this;
                $widget.element.closest('tr').on('click', function () {
                    if (!$(this).hasClass('bss-clicked')) {
                        if (!($widget.options.jsonConfig.productStockAlert === undefined)) {
                            $widget._UpdateDetailStock($(this));
                            $widget._UpdateDetailStockForm($(this));
                            $('.item-info').removeClass('bss-clicked');
                            $(this).addClass('bss-clicked');
                        }
                    }
                });
            },

            /**
             * Update detail stock
             * @param $this
             * @return {boolean}
             * @private
             */
            _UpdateDetailStock: function ($this) {
                var $widget = this,
                    index = '',
                    childProductData = this.options.jsonConfig.productStockAlert;

                let $row = $this;
                $row.find('input.' + $widget.options.classes.attributeClass).each(function () {
                    index += $(this).val() + '_';
                });

                if (!childProductData['child'].hasOwnProperty(index)) {
                    $widget._ResetStock($this);
                    return false;
                }
                $widget._UpdateStock(
                    $this,
                    childProductData['child'][index]['stock_status'],
                    childProductData['child'][index]['url'],
                    childProductData['child'][index]['parent_url']
                );
            },

            /**
             * Get stock-notice form
             * @param $this
             * @private
             */
            _UpdateDetailStockForm: function ($this) {
                var $widget = this,
                    index = null;
                index =  $widget.getChildProduct();
                var templateId = '#bss-stock-notice-form',
                    templateCancelid = '#bss-stock-notice-cancel-form';
                var priceTemplateId = '#bss-price-notice-form',
                    priceTemplateCancelId = '#bss-price-notice-cancel-form';
                var element = '#product_stock_alert_container';
                var elementPrice = '#product_price_alert_container';

                if ($this.hasClass('selected')) {
                    if (index !== null && index && !isNaN(parseInt(index))) {
                        var htmlFormPrice = '';
                        var htmlFormStock = '';

                        var confiurablePriceData = configurablePriceModel();
                        if ((confiurablePriceData.length || _.size(confiurablePriceData))) {
                            var productData = null;
                            var simpleData = confiurablePriceData['product_data'];
                            if (undefined !== simpleData[parseInt(index)]) {
                                productData = simpleData[parseInt(index)];
                            } else if (undefined !== simpleData[index]) {
                                productData = simpleData[index];
                            }
                            if (productData !== null) {
                                var confiurablePriceData = _.omit(confiurablePriceData, 'product_data'),
                                    dataRenderer = helper.mergeObject(confiurablePriceData, productData),
                                    hasEmail = dataRenderer.has_email;

                                if (!hasEmail) {
                                    var templatePrice = mageTemplate(priceTemplateId);
                                    htmlFormPrice += templatePrice({
                                        data: dataRenderer
                                    });
                                } else {
                                    var templatePriceCancel = mageTemplate(priceTemplateCancelId);
                                    htmlFormPrice += templatePriceCancel({
                                        data: dataRenderer
                                    });
                                }
                            }
                        }

                        var confiurableData = configurableModel();
                        if ((confiurableData.length || _.size(confiurableData))) {
                            var productData = null;
                            var simpleData = confiurableData['product_data'];
                            if (undefined !== simpleData[parseInt(index)]) {
                                productData = simpleData[parseInt(index)];
                            } else if (undefined !== simpleData[index]) {
                                productData = simpleData[index];
                            }
                            if (productData !== null) {
                                var confiurableData = _.omit(confiurableData, 'product_data'),
                                    dataRenderer = helper.mergeObject(confiurableData, productData),
                                    hasEmail = dataRenderer.has_email;

                                if (!hasEmail) {
                                    var template = mageTemplate(templateId);
                                    htmlFormStock += template({
                                        data: dataRenderer
                                    });
                                } else {
                                    var templateCancel = mageTemplate(templateCancelid);
                                    htmlFormStock += templateCancel({
                                        data: dataRenderer
                                    });
                                }
                            }
                        }

                        if (htmlFormPrice || htmlFormStock) {
                            $(elementPrice).html(htmlFormPrice).trigger('contentUpdated');
                            $(element).html(htmlFormStock).trigger('contentUpdated');

                            var email = $.mage.cookies.get('customer_email_cookie');
                            if (email) {
                                if ($(element).find('.input-text.stockalert_email').length > 0) {
                                    $(element).find('.input-text.stockalert_email').attr("value", email);
                                }
                                if ($(elementPrice).find('.input-text.stockalert_email').length > 0) {
                                    $(elementPrice).find('.input-text.stockalert_email').attr("value", email);
                                }
                            }
                        } else {
                            $(elementPrice).empty();
                            $(element).empty();
                        }
                    }
                } else {
                    $(elementPrice).empty();
                    $(element).empty();
                }
            },
            /**
             * Update stock
             * @param $this
             * @param $status
             * @param $url
             * @param $parentUrl
             * @private
             */
            _UpdateStock: function ($this, $status, $url, $parentUrl) {
                var _this = this;
                var $aTag = '<a type="button" href="' + $parentUrl +'" title="' + $t('Notify Me') +
                    '" class="product_alert_notify action primary"><span>' + $t('Notify Me') + '</span></a>';
                if (_this.options.jsonConfig.productStockAlert !== undefined &&
                    _this.options.jsonConfig.productStockAlert.buttonDesign !== undefined) {
                    var btnText = _this.options.jsonConfig.productStockAlert.buttonDesign.btnText;
                    var btnTextColor = _this.options.jsonConfig.productStockAlert.buttonDesign.btnTextColor;
                    var btnColor = _this.options.jsonConfig.productStockAlert.buttonDesign.btnColor;
                    $aTag = '<a type="button" ' +
                        'href="' + $parentUrl +'" ' +
                        'title="' + $t(btnText) +
                        '" class="product_alert_notify action primary"' +
                        ' style="background-color: ' + btnColor + '">' +
                        '<span style="color: ' + btnTextColor + '">' + $t(btnText) + '</span>' +
                        '</a>';
                }
                if ($status > 0) {
                    if ($this.parents('.product-item').length > 0) {
                        $this.parents('.product-item').find('.action.tocart').removeAttr('disabled');
                        $this.parents('.product-item').find('.product_alert_notify').remove();
                    } else {
                        $('#product-addtocart-button').removeAttr('disabled');
                        $('.container-child-product').html("");
                        $('.product-info-stock-sku .stock span').html("In Stock");
                    }
                } else {
                    if ($this.parents('.product-item').length > 0) {
                        $this.parents('.product-item').find('.action.tocart').attr('disabled', 'disabled');
                        var appendToElem = $this.parents('.product-item').find('.product-item-actions');
                        if (appendToElem.find('.product_alert_notify').length) {
                            appendToElem.find('.product_alert_notify').remove();
                        }
                        $this.parents('.product-item').find('.product-item-actions').append($aTag);
                    } else {
                        $('#product-addtocart-button').attr('disabled', 'disabled');
                        $('.product-info-stock-sku .stock span').html("Out of Stock");
                    }
                }
            },
            /**
             * Reset stock
             * @param $this
             * @private
             */
            _ResetStock: function ($this) {
                if (this.options.jsonConfig.productStockAlert['stock_status'] > 0) {
                    if ($this.parents('.product-item').length > 0) {
                        $this.parents('.product-item').find('.action.tocart').removeAttr('disabled');
                    } else {
                        $('#product-addtocart-button').removeAttr('disabled');
                        $('.container-child-product').html("");
                    }
                } else {
                    if ($this.parents('.product-item').length > 0) {
                        $this.parents('.product-item').find('.action.tocart').attr('disabled', 'disabled');
                    } else {
                        $('#product-addtocart-button').attr('disabled', 'disabled');
                        $('.container-child-product').html("");
                    }
                }
            },
        });

        return $.bss.swatch;
    }
});
