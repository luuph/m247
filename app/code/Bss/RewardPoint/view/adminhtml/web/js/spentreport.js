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

    $.widget('mage.spentreportChart', {
        options: {
            dataChart: []
        },
        chart: null,

        /**
         * @private
         */
        _create: function () {
            var xValues = ["Spent", "Earned"];
            var yValues= [
                this.options.dataChart['total_spent_point'],
                this.options.dataChart['total_earn_point']
            ];
            var barColors = this.listColor();

            new Chart("myChart", {
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
                        text: "Earned Points vs Spent Points (%)"
                    }
                }
            });

        },

        listColor:function () {
            return [
                "#00ade2",
                "#ffA500"
            ];
        }
    });

    return $.mage.spentreportChart;
});
