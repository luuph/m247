define([
    'jquery'
], function ($) {
    'use strict';

    var modalWidgetMixin = {
        _UpdateActiveTab: function () {
            $('.data.item.title').removeClass("active");
            $('.data.item.content').css('display', 'none');
            // if ($(window.location).attr('hash') == '') {
            //     $('.data.item.title:not(.' +this.options.sdcp_classes.hiddenTab+ ')').first().addClass('active');
            //     $('.data.item.content:not(.' +this.options.sdcp_classes.hiddenTab+ ')').first().css('display', 'block');
            // }
        },
    };

    return function (targetWidget) {
        $.widget('mage.modal', targetWidget, modalWidgetMixin);

        return $.mage.modal;
    };
});
