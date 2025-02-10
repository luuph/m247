define([
    'jquery',
    'mage/backend/notification'
], function ($, notification) {
    'use strict';

    return {
        add: function (text, isError, selector) {
            var wrapper;

            const pageSelector = '.page-main-actions';
            notification().clear();
            notification().add({
                error: isError,
                message: text,
                insertMethod: function (message) {
                    var wrapper = $('<div/>').html(message);

                    $(selector || pageSelector).after(wrapper);
                }
            });
        },

        clear: function () {
            notification().clear();
        }
    };
});
