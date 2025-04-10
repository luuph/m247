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
    'underscore',
    'uiRegistry',
    'mageUtils',
    'Magento_Ui/js/grid/massactions',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'jquery',
], function (_, registry, utils, Massactions, confirm, alert, $t,$) {
    'use strict';
    return Massactions.extend({
        defaults: {
            template: 'ui/grid/actions',
            stickyTmpl: 'ui/grid/sticky/actions',
            selectProvider: 'ns = ${ $.ns }, index = ids',
            actions: [],
            noItemsMsg: $t('You haven\'t selected any items!'),
            modules: {
                selections: '${ $.selectProvider }'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Massactions} Chainable.
         */
        initObservable: function () {
            var self = this;
            this._super();
            $(".wk_ws_reason_massaction").click(function(){
                if($(".wk_ws_reasonbody .wk_ws_textarea_reason").val().trim() != "") {
                    self.applyAction("banktransfercancel_for_webkul");
                }
            })
            return this;
        },

        /**
         * Applies specified action.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @returns {Massactions} Chainable.
         */
        applyAction: function (actionIndex) {
            var data = this.getSelections(),
                action,
                callback;
            if (!data.total) {
                alert({
                    content: this.noItemsMsg
                });

                return this;
            }
            var textArea = $(".wk_ws_reasonbody .wk_ws_textarea_reason");
            if(actionIndex && actionIndex == "banktransfercancel") {
                $(".wk_ws_reason_massaction").css("display","inline-block");
                $(".wk_ws_reason_action").css("display","none");
                var modalContainer = $("#wk_ws_reasonbody");
                textArea.val("");
                modalContainer.modal("openModal");
                return this;
            } else if (actionIndex && actionIndex == "banktransfercancel_for_webkul"){
                data.params.reason = textArea.val();
                actionIndex = "banktransfercancel";
            }

            action   = this.getAction(actionIndex);
            callback = this._getCallback(action, data);

            action.confirm ?
                this._confirm(action, callback) :
                callback();

            return this;
        },
    });
});
