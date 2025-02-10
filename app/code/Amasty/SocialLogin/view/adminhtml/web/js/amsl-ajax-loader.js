define([
    'jquery',
], function ($) {
    'use strict';

    return function (config, element) {
        $.get({
            url: config.url,
            loaderContext: element,
            success: function (html) {
                $(element).html(html);
                $(element).trigger('contentUpdated');
            }
        });
    };
});
