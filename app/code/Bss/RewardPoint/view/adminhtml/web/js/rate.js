define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.RewardPoint_Rate',{
        options: {
            data_json:{}
        },
        /** @inheritdoc */
        _create: function () {
            var $widget = this;
            $('body').on('change', 'select[name="website_id"]', function(){
                $widget.setBaseCurrencyCode($(this).val());
            })
            $widget.setBaseCurrencyCode(false);
        },

        /**
         * AsetBaseCurrencyCode
         */
        setBaseCurrencyCode: function (website_id) {
            var data = this.options.data_json;
            var website_id = website_id ? website_id : $('select[name="website_id"]').val();
            $.each(data, function(_websiteId, baseCurrencyCode){
                if (_websiteId == website_id) {
                    $('input[name="base_currrency_code"]').val(baseCurrencyCode);
                    return false;
                }
            });
        }
    });
    return $.mage.RewardPoint_Rate;
});
