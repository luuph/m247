/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AffiliateUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

/**
 * @returns {string}
 */
function getCreateChart() {
    var prefix = document.getElementsByClassName("mp_menu").length ? '' : 'fake-';
    return 'Mageplaza_AffiliateUltimate/js/' + prefix + 'create-chart';
}

define([
    'jquery',
    'Magento_Ui/js/grid/provider',
    getCreateChart()
], function ($, Provider, createChart) {
    'use strict';

    return Provider.extend({
        reload: function (options) {
            this.addParamsToFilter();
            this._super();
        },
        /**
         * Compatible with Mageplaza Reportspro
         */
        addParamsToFilter: function () {
            if (this.isEnableReportMenu()) {
                $('.admin__form-field:has(input[name="created_at[from]"])').hide();
                $('.admin__form-field:has(select[name="store_id"])').hide();
                $('.admin__form-field:has(select[name="period"])').hide();
                var mpFilter = (typeof this.params.mpFilter === "undefined") ? {} : this.params.mpFilter;
                if (typeof mpFilter.startDate === "undefined") {
                    mpFilter.startDate = $('#daterange').data().startDate.format('Y-MM-DD');
                }
                if (typeof mpFilter.endDate === "undefined") {
                    mpFilter.endDate = $('#daterange').data().endDate.format('Y-MM-DD');
                }

                if (typeof mpFilter.store === "undefined") {
                    mpFilter.store = $('#store_switcher').val();
                }
                if(typeof mpFilter.orderStatus === "undefined") {
                    mpFilter.orderStatus = $('#order_status :input').serializeArray();
                }
                this.params.mpFilter = mpFilter;
            }
        },
        /**
         * @param data
         * @returns {*}
         */
        processData: function (data) {
            if ($(".affiliate-transaction-index").length > 0) {
                this.buildChart(data);
            }

            return this._super();
        },
        /**
         * Build chart when Mp Reports enable
         */
        buildChart: function (data) {
            if (this.isEnableReportMenu()) {
                var items = data.items;
                if (Object.keys(items).length) {
                    var rewardData = [0, 0, 0];
                    _.each(items, function (record, index) {
                        var status = record.status;
                        if (status) {
                            var key = Number(status) - 2;
                            rewardData[key] += parseFloat(record.amount_report)
                        }
                    });

                    _.each(rewardData, function (value, index) {
                        if (value < 0) {
                            rewardData[index] = 0;
                        }
                    });

                    createChart({
                        chartData: {
                            labelColor: this.labelColor,
                            priceFormat: this.priceFormat,
                            data: rewardData,
                            maintainAspectRatio: true
                        },
                        chartElement: 'transaction-chart'
                    });
                    $('#transaction-chart').show();
                } else {
                    $('#transaction-chart').hide();
                }
            }
        },
        /**
         * @returns {jQuery}
         */
        isEnableReportMenu: function () {
            return $('.mp_menu').length;
        }
    });
});
