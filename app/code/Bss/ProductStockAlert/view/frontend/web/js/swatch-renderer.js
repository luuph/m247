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
 * @copyright  Copyright (c) 2015-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'underscore',
    'mage/translate',
    'mage/template',
    'Bss_ProductStockAlert/js/model/product/configurable',
    'Bss_ProductStockAlert/js/model/product/configurable-price',
    'Bss_ProductStockAlert/js/helper/data'
], function ($, _, $t, mageTemplate, configurableModel, configurablePriceModel, helper) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            productListItemElem: '.product-item',
            addToCartElem: '.action.tocart',
            btnNotifyElem: '.product_alert_notify',
            productActionerGroupElem: '.product-item-actions',
            addToCartElemInPage: '#product-addtocart-button',
            productStatusElemInPage: '.product-info-stock-sku',
            inStockText: $t('In Stock'),
            outStockText: $t('Out of Stock'),
            notiMeText: $t('Notify Me'),

            /**
             * Event for swatch options
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
                $widget._super($this, $widget);
                $widget._UpdateDetailStock($this);
                if (this.options.jsonConfig.productStockAlert !== undefined) {
                    if (this._isInProductDetailPage()) {
                        $widget._UpdateDetailStockForm($this);
                    }
                }
            },

            /**
             * Event for select
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnChange: function ($this, $widget) {
                $widget._super($this, $widget);
                $widget._UpdateDetailStock($this);
                if (this.options.jsonConfig.productStockAlert !== undefined) {
                    if (this._isInProductDetailPage()) {
                        $widget._UpdateDetailStockForm($this);
                    }
                }
            },

            /**
             * Get product
             * @returns {null|*}
             */
            getChildProduct: function () {
                var products = this._CalcProducts();
                if (_.isArray(products) && products.length === 1) {
                    return products[0];
                }
                return null;
            },

            /**
             * Update stock notice form every time click swatches
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

                if ($this.hasClass('selected') || $this.is('select')) {
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

                            var email = confiurableData.default_email;
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
             * Update detail stock
             * @param $this
             * @returns {boolean}
             * @private
             */
            _UpdateDetailStock: function ($this) {
                var $widget = this,
                    index = $widget.getChildProduct(),
                    productStockAlertData = this.options.jsonConfig.productStockAlert,
                    productData;

                if (productStockAlertData && productStockAlertData['child']) {
                    productData = productStockAlertData['child'][index] ? productStockAlertData['child'][index] : productStockAlertData['child'][parseInt(index)];
                } else {
                    productData = undefined;
                }

                if (productData === undefined || !productData) {
                    $widget._ResetStock($this);
                    return false;
                }

                $widget._UpdateStock(
                    $this,
                    productData
                );
            },

            /**
             * Update stock
             * @param $this
             * @param $salenable
             * @param $status
             * @param $parentUrl
             * @param $preorder
             * @private
             */
            _UpdateStock: function ($this, productData) {
                var self = this,
                    $salenable = productData['stock_number'],
                    $status = productData['stock_status'],
                    $parentUrl = productData['parent_url'],
                    $preorder = productData['preorder'],
                    jsonConfig = self.options.jsonConfig;

                var $aTag = '<a type="button" href="' + $parentUrl +'" title="' + self.notiMeText +
                    '" class="product_alert_notify action primary"><span>' + self.notiMeText + '</span></a>';
                if (jsonConfig.productStockAlert !== undefined &&
                    jsonConfig.productStockAlert.buttonDesign !== undefined) {
                    var btnText = jsonConfig.productStockAlert.buttonDesign.btnText;
                    var btnTextColor = jsonConfig.productStockAlert.buttonDesign.btnTextColor;
                    var btnColor = jsonConfig.productStockAlert.buttonDesign.btnColor;

                    /* Button stop notify me */
                    if (window.allProductAlert && window.allProductAlert['product'] && window.allProductAlert['product'][productData['entity']]) {
                        $parentUrl = window.allProductAlert['product'][productData['entity']]['url_cancel'];
                        btnText = window.allProductAlert['btn_text_stop'];
                    }

                    $aTag = '<a type="button" ' +
                        'href="' + $parentUrl +'" ' +
                        'title="' + $t(btnText) +
                        '" class="product_alert_notify action primary"' +
                        ' style="background-color: ' + btnColor + '">' +
                        '<span style="color: ' + btnTextColor + '">' + $t(btnText) + '</span>' +
                        '</a>';
                }

                var productItemSelector = $this.closest(self.productListItemElem);
                if ($status > 0 && $salenable !== 0) {
                    if (!self._isInProductDetailPage() && productItemSelector.length) {
                        productItemSelector.find(self.addToCartElem).removeAttr('disabled');
                        productItemSelector.find(self.btnNotifyElem).remove();
                    } else {
                        $(self.addToCartElemInPage).removeAttr('disabled');
                        $(self.productStatusElemInPage).find('.stock').remove('span').html("<span>" + self.inStockText + "</span>");
                    }
                } else {
                    if (!self._isInProductDetailPage() && productItemSelector.length) {
                        if (!$preorder) {
                            productItemSelector.find(self.addToCartElem).attr('disabled', 'disabled');
                        }

                        var productActionerGroupSelector = productItemSelector.find(self.productActionerGroupElem);
                        if (productActionerGroupSelector.find(self.btnNotifyElem).length) {
                            productActionerGroupSelector.find(self.btnNotifyElem).remove();
                        }

                        productActionerGroupSelector.append($aTag);
                    } else {
                        if (!$preorder) {
                            $(self.addToCartElemInPage).attr('disabled', 'disabled');
                        }
                        $(self.productStatusElemInPage).find('.stock').remove('span').html("<span>" + self.outStockText + "</span>");
                    }
                }
            },

            /**
             * Reset stock
             * @param $this
             * @private
             */
            _ResetStock: function ($this) {
                var self = this;
                var productItemSelector = $this.closest(self.productListItemElem);

                if (!self._isInProductDetailPage() && productItemSelector.length) {
                    productItemSelector.find(self.addToCartElem).removeAttr('disabled');
                    var productActionerGroupSelector = productItemSelector.find(self.productActionerGroupElem);
                    if (productActionerGroupSelector.find(self.btnNotifyElem).length) {
                        productActionerGroupSelector.find(self.btnNotifyElem).remove();
                    }
                } else {
                    $(self.addToCartElemInPage).removeAttr('disabled');
                    $(self.productStatusElemInPage).find('.stock').remove('span').html("<span>" + self.inStockText + "</span>");
                }
            },
            /**
             * @return {boolean}
             * @private
             */
            _isInProductDetailPage: function () {
                return this.options.jsonConfig.productStockAlert.controllerActionName == "catalog_product_view" ||
                    this.options.jsonConfig.productStockAlert.controllerActionName == "wishlist_index_configure";
            }
        });

        return $.mage.SwatchRenderer;
    }
});
