define([
    'jquery', 
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
    ],function ($, alert, confirm, $t) {
        'use strict';
        var mixin = {

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

                action   = this.getAction(actionIndex);
                callback = this._getCallback(action, data);

                if (action.identifier === 'massaction_product_translate') {
                    $.ajax({
                        url: action.identifier_url,
                        type: 'GET',
                        showLoader: true
                    })
                    .done(function(response) {
                        if (response.status == 1) {
                            action.confirm ?
                            mixin.bizConfirm(action, callback, 1) :
                            callback();
                        } else {
                            action.confirm ?
                            mixin.bizConfirm(action, callback, 0) :
                            callback();        
                        }
                    });
                } else {
                    action.confirm ?
                    this._confirm(action, callback) :
                    callback();
                }

                return this;
            },

            bizConfirm : function (action, callback, flag = 0) {
                var confirmData = action.confirm;

                if (flag == 1) {
                    confirm({
                        title: confirmData.title,
                        content: $t('Do you want to abort existing cron?'),
                        actions: {
                            confirm: callback
                        }
                    });
                } else {
                    confirm({
                        title: confirmData.title,
                        content: confirmData.message,
                        actions: {
                            confirm: callback
                        }
                    });
                }
            },
        };

    return function (target) { // target == Result that Magento_Ui/.../columns returns.
        return target.extend(mixin); // new result that all other modules receive
    };
});