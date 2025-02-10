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
    'ko',
    'mage/template',
    'Bss_ProductStockAlert/js/helper/data',
    'Bss_ProductStockAlert/js/model/product/configurable-price'
], function ($, _, ko, mageTemplate, helper, configurablePriceModel) {
    'use strict';

    return {
        loading: false,
        formDataActionUrl: ko.observable(),
        /**
         * Ajax request form data
         * @param parent
         */
        requestFormData: function (parent) {
            var self = this;
            if (!self.loading) {
                $("body").trigger('processStart');
                $.ajax({
                    url: self.getFormDataActionUrl(),
                    dataType: 'json',
                    type: 'GET',
                    contentType: 'application/json; charset=UTF-8',
                    headers: {
                        'Content-Type':'application/json'
                    },
                    beforeSend: function () {
                        self.loading = true;
                    },
                    complete: function (res) {
                        // Do something
                        self.loading = false;
                        // Re-enable button slide
                        $('#bundle-slide').prop('disabled', false);
                        $("body").trigger('processStop');
                    }
                }).done(function (dataResponse) {
                    if (dataResponse && (dataResponse.length || _.size(dataResponse)) && undefined === dataResponse._error) {
                        var type = dataResponse.type,
                            productData = dataResponse.product_data,
                            templateId = parent.options.templateId,
                            templateCancelid = parent.options.templateCancelId;

                        if (type === 'configurable') {
                            configurablePriceModel(dataResponse);
                        } else {
                            var cleanDataResponse = _.omit(dataResponse, 'product_data'),
                                firstKey = _.keys(productData)[0],
                                dataRenderer = helper.mergeObject(cleanDataResponse, productData[firstKey]),
                                hasEmail = dataRenderer.has_email,
                                htmlForm;

                            if (!hasEmail) {
                                var template = mageTemplate(templateId);
                                htmlForm = template({
                                    data: dataRenderer
                                });
                            } else {
                                var templateCancel = mageTemplate(templateCancelid);
                                htmlForm = templateCancel({
                                    data: dataRenderer
                                });
                            }

                            $(parent.element).html(htmlForm).trigger('contentUpdated');
                        }

                        if ($(parent.element).find('.input-text.stockalert_email').length > 0) {
                            var email = dataResponse.default_email;
                            $(parent.element).find('.input-text.stockalert_email').attr("value", email);
                        }
                    }

                    return;
                });
            }
        },
        /**
         * Set ajax url
         * @param url
         * @returns {exports}
         */
        setFormDataActionUrl: function (url) {
            this.formDataActionUrl(url);
            return this;
        },
        /**
         * Get ajax url
         * @return {string}
         */
        getFormDataActionUrl: function () {
            return this.formDataActionUrl();
        }
    }
});
