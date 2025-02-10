define([
    'jquery'
], function ($) {
    'use strict';

    var modalWidgetMixin = {
        _UpdateSelected: function ($options, $widget) {
            this.mediaInit = true;
        },
        _UpdateActiveTab: function () {
            $('.data.item.title').removeClass("active");
            $('.data.item.content').css('display', 'none');
            // if ($(window.location).attr('hash') == '') {
            //     $('.data.item.title:not(.' +this.options.sdcp_classes.hiddenTab+ ')').first().addClass('active');
            //     $('.data.item.content:not(.' +this.options.sdcp_classes.hiddenTab+ ')').first().css('display', 'block');
            // }
        },
        _preselectByConfig: function(config, data) {
            var _this = this
                , defaultMagentoVals = this.options.jsonConfig.defaultValues
                , defaultVal = {};
            if (config['preselect'] > 0 && data['preselect']['enabled'] > 0) {
                defaultVal = data['preselect']['data'];
            }
            if (undefined !== defaultMagentoVals) {
                defaultVal = defaultMagentoVals;
            }
            $.each(defaultVal, function($index, $vl) {
                if ($index === Object.keys(defaultVal).pop()) {
                    _this.mediaLoadFirst = true;
                }
                try {
                    if ($('.swatch-attribute[attribute-id=' + $index + '] .swatch-attribute-options').children().is('div')) {
                        if ($('.swatch-attribute[attribute-id=' + $index + '] .swatch-attribute-options [option-id=' + $vl + ']').hasClass('selected') == false) {
                            $('.swatch-attribute[attribute-id=' + $index + '] .swatch-attribute-options [option-id=' + $vl + ']').trigger('click');
                        }
                    } else {
                        if ($('.swatch-attribute[attribute-id=' + $index + '] .swatch-attribute-options select').hasClass('selected') == false) {
                            $('.swatch-attribute[attribute-id=' + $index + '] .swatch-attribute-options select').val($vl).trigger('change');
                        }
                    }
                    if ($('.swatch-attribute[data-attribute-id=' + $index + '] .swatch-attribute-options').children().is('div')) {
                        if ($('.swatch-attribute[data-attribute-id=' + $index + '] .swatch-attribute-options [data-option-id=' + $vl + ']').hasClass('selected') == false) {
                            $('.swatch-attribute[data-attribute-id=' + $index + '] .swatch-attribute-options [data-option-id=' + $vl + ']').trigger('click');
                        }
                    } else {
                        if ($('.swatch-attribute[data-attribute-id=' + $index + '] .swatch-attribute-options select').hasClass('selected') == false) {
                            $('.swatch-attribute[data-attribute-id=' + $index + '] .swatch-attribute-options select').val($vl).trigger('change');
                        }
                    }
                } catch (e) {
                    console.log($.mage.__('Error when applied preselect product'));
                }
            });
            return true;
        },
    };

    return function (targetWidget) {
        $.widget('mage.modal', targetWidget, modalWidgetMixin);
        return $.mage.modal;
    };
});
