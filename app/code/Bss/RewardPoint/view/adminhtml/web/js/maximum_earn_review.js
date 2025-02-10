define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.bss_maximum_earn_review',{
        options: {
            disabled_element:false,
            htmlId:'',
        },
        /** @inheritdoc */
        _create: function () {
            var $widget = this;
            if ($widget.options.disabled_element) {
                $('#grid' + $widget.options.htmlId).parent().find('select,input').addClass('disabled').prop('disabled', true);
            }
        }
    });
    return $.mage.bss_maximum_earn_review;
});
