/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_RewardPoint
 * @author     Extension Team
 * @copyright  Copyright (c) 2019-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'chartJs',
    'jquery-ui-modules/widget',
    'chartjs/chartjs-adapter-moment',
    'chartjs/es6-shim.min',
    'moment'
], function ($, Chart) {
    'use strict';

    $.widget('mage.earnreportChart', {
        options: {
            dataChart: []
        },
        chart: null,

        /**
         * @private
         */
        _create: function () {
            var xValues = ["ADMIN CHANGE", "REGISTRATION", "BIRTHDAY", "FIRST REVIEW", "REVIEW", "FIRST ORDER",
                "ORDER", "ORDER REFUND", "IMPORT", "SUBSCRIBE NEWSLETTERS"];
            var yValues = [
                this.options.dataChart['earn_report_admin_change'],
                this.options.dataChart['earn_report_registration'],
                this.options.dataChart['earn_report_birthday'],
                this.options.dataChart['earn_report_first_review'],
                this.options.dataChart['earn_report_review'],
                this.options.dataChart['earn_report_first_order'],
                this.options.dataChart['earn_report_order'],
                this.options.dataChart['earn_report_order_refund'],
                this.options.dataChart['earn_report_import'],
                this.options.dataChart['earn_report_subscribe']
            ];
            var barColors = this.listColor();

            this.chart = new Chart(this.element, {
                type: "pie",
                data: {
                    labels: xValues,
                    datasets: [{
                        backgroundColor: barColors,
                        data: yValues
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: "Points Earned by Events (%)"
                    }
                }
            });

        },

        listColor: function () {
            return [
                "#0000ff",
                "#8a2be2",
                "#ee82ee",
                "#c71585",
                "#ff0000",
                "#ff4500",
                "#ffA500",
                "#ffff00",
                "#9acd32",
                "#008000"
            ];
        }
    });

    return $.mage.earnreportChart;
});
