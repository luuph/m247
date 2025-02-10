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
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(
    [
        'ko',
        'jquery',
        'mage/template',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Bss_RewardPoint/js/model/payment/spendpoint_messages',
        'mage/storage',
        'mage/translate',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (ko, $, mageTemplate, urlManager, errorProcessor, messageContainer, storage, $t, getPaymentInformationAction, totals, fullScreenLoader) {
        'use strict';

        return function (spendPoint, pointLeft) {
            var urls = {
                        'guest': '',
                        'customer': '/carts/mine/bss-reward-point/apply/' + spendPoint()
                },
                url = urlManager.getUrl(urls, {}),
                message = '';

            fullScreenLoader.startLoader();
            $('.payment-option-content.bss-reward-point').find('.message').remove();
            return storage.put(
                url,
                {},
                false
            ).done(
                function (response) {
                    var res = JSON.parse(response),
                    template =  '<div class="messages">' +
                                '<div class="message-<%- data.status_message %> <%- data.status_message %> message">'+
                                '<div><%- data.content_message %></div>' +
                                '</div></div>',
                    template = mageTemplate(template),
                    html_mess = '';
                    if (res.status) {
                        /* @deprecated use location.reload() */
                        //var deferred = $.Deferred();
                        // totals.isLoading(true);
                        // getPaymentInformationAction(deferred);
                        // $.when(deferred).done(function () {
                        //     fullScreenLoader.stopLoader();
                        //     totals.isLoading(false);
                        // });

                        message = res.message;
                        if (res.pointLeft) {
                            pointLeft(res.pointLeft);
                        }
                        if (res.spend_point) {
                            $('#bss-reward-point-value').val(parseInt(res.spend_point)).trigger('input');
                        }

                        html_mess = template({
                                data: {
                                    status_message: res.status_message,
                                    content_message: res.message
                                }
                            });
                        $('.payment-option-content.bss-reward-point').prepend(html_mess);
                        setTimeout(function(){
                            $('.payment-option-content.bss-reward-point').find('.message').fadeOut('slow');
                        }, 4000)
                    } else {
                        message = res.message;
                        fullScreenLoader.stopLoader();
                        messageContainer.addErrorMessage({
                            'message': message
                        });
                    }
                    location.reload();
                }
            ).fail(
                function (response) {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                    location.reload();
                }
            );
        };
    }
);
