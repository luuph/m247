/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'domReady!'
], function ($, modal, alert) {
    'use strict';
    $.widget('mage.WkWalletsystemTransactionCancel', {
        _create: function () {
            var self = this;
            var textArea = $(".wk_ws_reasonbody .wk_ws_textarea_reason");
            var form = $("#wallet_reason_form"),transactionId = document.querySelector("#wk_ws_transaction_id");
            var modalContainer = $("#wk_ws_reasonbody");
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: [{
                    text: $.mage.__('Submit'),
                    class: 'wk_ws_reason_action',
                    click: function () {
                        if(textArea.val().trim() != "") {
                            form.submit();
                        } else {
                            alert({
                                content: $.mage.__('Reason should not be empty.')
                            });
                        }
                    }
                },{
                    text: $.mage.__('Submit'),
                    class: 'wk_ws_reason_massaction',
                    click: function () {
                        if(textArea.val().trim() != "") {
                            modalContainer.modal("closeModal");
                        } else {
                            alert({
                                content: $.mage.__('Reason should not be empty.')
                            });
                        }
                    }
                }]
            };
            var popup = modal(options, modalContainer);
            var massActionButton = $(".wk_ws_reason_massaction"), actionButton = $(".wk_ws_reason_action");
            $("body").delegate( ".wallet-transaction-view .wk_ws_cancel,li a[data-action='item-wk-ws-cancel-transaction']", "click", function() {
                textArea.val("");
                massActionButton.css("display","none");
                actionButton.css("display","inline-block");
                modalContainer.modal("openModal");
                if(transactionId && this.hasAttribute("href")) {
                    var entityId = this.getAttribute("href");
                    entityId = entityId.substring(1,entityId.length);
                    transactionId.setAttribute("value",entityId);
                }
            });
        }
    });
    return $.mage.WkWalletsystemTransactionCancel;
});
