define([
    'jquery',
    'Magento_Ui/js/form/element/select',
    'uiRegistry'
], function ($, Select, registry) {
    'use strict';

    return Select.extend({
        /**
         * Change currently selected option
         *
         * @param {String} id
         */
        selectOption: function (id) {
            var value = $(id).val(),
                customDateId = '4';

            if (!value || this.ns == 'amrmarep_report_details_form') {
                return;
            }

            registry.async("index = requests-stat")(function (reports) {
                if (value === customDateId && !reports.endDate() && !reports.startDate()) {
                    return;
                }
                reports.getGraphData(value)
            }.bind(this));
        }
    });
});
