define([
    'jquery',
    'Magento_Ui/js/grid/data-storage',
    'uiRegistry'
], function ($, storage, registry) {
    'use strict';

    return storage.extend({
        defaults: {
            saveUrl: ''
        },
        cacheRequest: function (data, params) {
            var formData = registry.get('index = amrmarep_report_overview_form').source.data;

            $.ajax({
                url: this.saveUrl,
                data: {
                    reason_id: formData.reason,
                    resolution_id: formData.resolution
                },
                method: 'POST',
                global: false,
                dataType: 'json'
            });
            return this;
        }
    });
});
