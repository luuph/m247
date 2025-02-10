define([
    'uiElement',
    'underscore',
    'jquery',
    'mage/translate',
    'Amasty_Rma/vendor/amcharts4/charts',
    'Amasty_Rma/vendor/amcharts4/animated',
], function (Element, _, $) {
    'use strict';

    return Element.extend({
        defaults: {
            dataUrl: '',
            pageType: '',
            startDate: '',
            endDate: '',
            dateRange: 0,
            reasonId: null,
            resolutionId: null,
            requestsCount: null,
            returnsPercentage: null,
            leadTime: null,
            rating: null,
            storeDelivery: null,
            reasonChart: null,
            statChart: null,
            statChartLegend: null,
            hasEvents: false,
            totalData: {},
            items: {}
        },
        page: {
          type: 'overview'
        },
        css: {
            checked: '-checked'
        },
        filters: '[data-amrma-js="filter"]',
        chart: {
            statistics: 'amrma-chart-statistics',
            reasons: 'amrma-chart-reasons',
            reasonsLegend: 'amrma-reasons-legend'
        },

        initObservable: function () {
            this._super().observe([
                'reasonId',
                'resolutionId',
                'perc',
                'requestsCount',
                'returnsPercentage',
                'leadTime',
                'rating',
                'storeDelivery',
                'startDate',
                'endDate',
                'dateRange',
                'items'
            ]);

            return this;
        },

        getGraphData: function (dateRange) {
            if (dateRange) {
                this.dateRange(dateRange);
            }
            $.ajax({
                url: this.dataUrl,
                type: 'POST',
                showLoader: true,
                data: {
                  start_date: this.startDate(),
                  end_date: this.endDate(),
                  date_range: this.dateRange(),
                  items: this.items(),
                  namespace: this.ns
                },
                global: false,
                dataType: 'json',
                success: function (data) {
                    console.log(data);

                    this.init(data);
                }.bind(this)
            });
        },

        init: function (data) {
            if (!this.hasEvents) {
                this.addEvents();
            }

            this.totalData = data;
            this.requestsCount(data.totalData.requestsCount);
            this.returnsPercentage(data.percentageData.returnsPercentage);
            this.leadTime(data.leadTimeData.leadTime);
            this.rating(data.ratingData.rating);
            this.storeDelivery(data.storeDeliveryData.storeDelivery);

            this.initStatisticChart(data.totalData.data);

            if (this.pageType === this.page.type) {
                this.initTopReasonChart(data.reasonsData);
            }
        },

        addEvents: function () {
            $(this.filters).on('click', this.filterClick.bind(this));
            this.hasEvents = true;
        },

        filterClick: function (e) {
            var $elem = $(e.currentTarget),
                index;

            $(this.filters).removeClass(this.css.checked);
            $elem.addClass(this.css.checked);

            index = $elem.data('amrmaIndex');
            this.initStatisticChart(this.totalData[index].data);
        },

        sortFunction: function (a, b) {
            return (b.count - a.count);
        },

        initTopReasonChart: function (data) {
            var pieSeries,
                marker;

            if (this.reasonChart) {
                this.reasonChart.dispose();
                this.statChartLegend.dispose();
            }

            this.reasonChart = am4core.create(this.chart.reasons, am4charts.PieChart);

            am4core.useTheme(am4themes_animated);
            data.sort(this.sortFunction);
            if (data.length > 5) {
                data.length = 5;
            }

            this.reasonChart.invalidateData();
            this.reasonChart.data = data;
            this.reasonChart.innerRadius = am4core.percent(25);
            this.reasonChart.paddingBottom = 0;
            this.reasonChart.startAngle = 180;
            this.reasonChart.endAngle = 360;

            pieSeries = this.reasonChart.series.push(new am4charts.PieSeries());
            pieSeries.dataFields.value = "qty";
            pieSeries.dataFields.category = "title";
            pieSeries.labels.template.disabled = true;
            pieSeries.ticks.template.disabled = true;
            pieSeries.slices.template.tooltipText = "[fill:#fff]{category}:[/] [bold;fill:#fff]{value.value}[/]";
            pieSeries.hiddenState.properties.opacity = 1;
            pieSeries.hiddenState.properties.endAngle = -90;
            pieSeries.hiddenState.properties.startAngle = -90;

            this.statChartLegend = am4core.create(this.chart.reasonsLegend, am4core.Container);
            this.statChartLegend.width = am4core.percent(100);
            this.statChartLegend.height = 200;

            this.reasonChart.legend = new am4charts.Legend();
            this.reasonChart.legend.position = 'right';
            this.reasonChart.legend.width = 288;
            this.reasonChart.legend.labels.template.text = "[font-size:13px]{category}[/]";
            this.reasonChart.legend.valueLabels.template.text = "[bold;font-size:18px]{value.value}[/]";
            this.reasonChart.legend.valueLabels.position = "right";
            this.reasonChart.legend.labels.template.maxWidth = 270;
            this.reasonChart.legend.labels.template.truncate = false;
            this.reasonChart.legend.labels.template.wrap = true;
            this.reasonChart.legend.itemContainers.template.paddingTop = 3;
            this.reasonChart.legend.itemContainers.template.paddingBottom = 3;
            this.reasonChart.legend.parent = this.statChartLegend;

            marker = this.reasonChart.legend.markers.template.children.getIndex(0);
            marker.cornerRadius(9, 9, 9, 9);
            marker.width = 18;
            marker.height = 18;
        },

        initStatisticChart: function (data) {
            am4core.useTheme(am4themes_animated);
            var series,
                valueAxis,
                dateAxis;

            if (this.statChart) {
                this.statChart.dispose();
            }

            this.statChart = am4core.create(this.chart.statistics, am4charts.XYChart);
            this.statChart.data = data;

            dateAxis = this.statChart.xAxes.push(new am4charts.DateAxis());
            dateAxis.renderer.minGridDistance = 50;

            valueAxis = this.statChart.yAxes.push(new am4charts.ValueAxis());

            series = this.statChart.series.push(new am4charts.LineSeries());
            series.dataFields.valueY = "count";
            series.dataFields.dateX = "date";
            series.strokeWidth = 2;
            series.minBulletDistance = 10;
            series.tooltipText = "[fill:#fff]{valueY}[/]";
            series.tooltip.pointerOrientation = "vertical";
            series.tooltip.background.cornerRadius = 20;
            series.tooltip.background.fillOpacity = 0.5;
            series.tooltip.label.padding(12,12,12,12);

            var bullet = series.bullets.push(new am4charts.CircleBullet());
            bullet.circle.strokeWidth = 2;
            bullet.circle.radius = 4;
            bullet.circle.fill = am4core.color("#fff");

            if (this.pageType !== this.page.type) {
                // Create vertical scrollbar and place it before the value axis
                this.statChart.scrollbarY = new am4core.Scrollbar();
                this.statChart.scrollbarY.parent = this.statChart.leftAxesContainer;
                this.statChart.scrollbarY.toBack();
            }

            this.statChart.scrollbarX = new am4charts.XYChartScrollbar();
            this.statChart.scrollbarX.series.push(series);
            this.statChart.scrollbarX.parent = this.statChart.bottomAxesContainer;

            this.statChart.cursor = new am4charts.XYCursor();
            this.statChart.cursor.xAxis = dateAxis;
            this.statChart.cursor.snapToSeries = series;
        }
    });
});
