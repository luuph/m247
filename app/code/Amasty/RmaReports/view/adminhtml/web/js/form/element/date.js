define([
    'Magento_Ui/js/form/element/date',
    'uiRegistry',
    'moment',
], function (date, registry, moment) {
    'use strict';

    return date.extend({
        defaults: {
            endDate: '',
            startDate: ''
        },

        onShiftedValueChange: function (shiftedValue) {
            var value,
                formattedValue,
                momentValue;

            if (shiftedValue) {
                momentValue = moment(shiftedValue, this.pickerDateTimeFormat);

                if (this.options.showsTime) {
                    formattedValue = moment(momentValue).format(this.timezoneFormat);
                    value = moment.tz(formattedValue, this.storeTimeZone).tz('UTC').toISOString();
                } else {
                    value = momentValue.format(this.outputDateFormat);
                }
            } else {
                value = '';
            }

            if (value !== this.value()) {
                this.value(value);
                this.reloadGraph();
            }
        },

        reloadGraph: function () {
            if (Date.parse(this.startDate) <= Date.parse(this.endDate) && this.ns != 'amrmarep_report_details_form') {
                registry.async("index = requests-stat")(function (reports) {
                    reports.getGraphData()
                }.bind(this));
            }
        }
    });
});
