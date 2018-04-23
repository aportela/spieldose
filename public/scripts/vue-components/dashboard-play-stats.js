var dashboardPlayStats = (function () {
    "use strict";

    var template = function () {
        return `
    <section class="panel">
        <p class="panel-heading">
            <span class="icon"><i v-if="loading" class="fas fa-cog fa-spin fa-fw"></i><i v-else-if="errors" class="fas fa-exclamation-triangle"></i><i v-else class="fas fa-chart-line"></i></span> Play statistics
            <a v-on:click.prevent="loadChart();" title="refresh data" class="icon is-pulled-right"><i class="fas fa-redo fa-fw"></i></a>
        </p>
        <p class="panel-tabs">
            <a v-bind:class="interval == 'hour' ? 'is-active': ''" v-on:click.prevent="changeInterval('hour');">by hour</a>
            <a v-bind:class="interval == 'weekDay' ? 'is-active': ''" v-on:click.prevent="changeInterval('weekDay');">by weekday</a>
            <a v-bind:class="interval == 'month' ? 'is-active': ''" v-on:click.prevent="changeInterval('month');">by month</a>
            <a v-bind:class="interval == 'year' ? 'is-active': ''" v-on:click.prevent="changeInterval('year');">by year</a>
        </p>
        <div class="panel-block" v-if="! errors">
            <canvas v-if="interval == 'hour'" class="play-stats-metrics-graph" id="playcount-metrics-chart-hour" height="200"></canvas>
            <canvas v-if="interval == 'weekDay'" class="play-stats-metrics-graph" id="playcount-metrics-chart-weekday" height="200"></canvas>
            <canvas v-if="interval == 'month'" class="play-stats-metrics-graph" id="playcount-metrics-chart-month" height="200"></canvas>
            <canvas v-if="interval == 'year'" class="play-stats-metrics-graph" id="playcount-metrics-chart-year" height="200"></canvas>
        </div>
        <div class="panel-block" v-else>error loading data (invalid response from server)</div>
    </section>
    `;
    };

    const commonChartOptions = {
        maintainAspectRatio: false,
        legend: {
            display: false
        },
        // https://stackoverflow.com/a/38945591
        scales: {
            yAxes: [
                {
                    ticks: {
                        beginAtZero: true,
                        callback: function (value) {
                            if (value % 1 === 0) {
                                return value;
                            }
                        }
                    }
                }
            ]
        }
    };

    /* app chart (test) component */
    var module = Vue.component('spieldose-dashboard-play-stats', {
        template: template(),
        data: function () {
            return ({
                chart: null,
                loading: false,
                errors: false,
                interval: "hour",
                items: []
            });
        },
        created: function () {
            this.loadChart();
        }, methods: {
            randomColorGenerator: function () {
                return '#' + (Math.random().toString(16) + '0000000').slice(2, 8);
            },
            loadMetricsByHourChart: function () {
                var self = this;
                var d = {};
                self.loading = true;
                self.errors = false;
                spieldoseAPI.getPlayStatMetricsByHour(function (response) {
                    if (response.ok) {
                        var hourNames = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
                        var data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                        for (var i = 0; i < response.body.metrics.length; i++) {
                            data[response.body.metrics[i].hour] = response.body.metrics[i].total;
                        }
                        if (self.chart) {
                            self.chart.destroy();
                        }
                        self.chart = new Chart(document.getElementById("playcount-metrics-chart-hour"), {
                            type: 'line',
                            data: {
                                labels: hourNames,
                                datasets: [
                                    {
                                        "label": "play stats by hour",
                                        "data": data,
                                        "fill": true,
                                        "borderColor": "#3273dc",
                                        "lineTension": 0.1
                                    }
                                ]
                            }, options: commonChartOptions
                        });
                        self.loading = false;
                    } else {
                        self.errors = true;
                        self.loading = false;
                    }
                });
            },
            loadMetricsByWeekDayChart: function () {
                var self = this;
                var d = {};
                self.loading = true;
                self.errors = false;
                spieldoseAPI.getPlayStatMetricsByWeekDay(function (response) {
                    if (response.ok) {
                        var weekDayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        var weekDays = [];
                        var data = [];
                        for (var i = 0; i < response.body.metrics.length; i++) {
                            data.push(response.body.metrics[i].total);
                            weekDays.push(weekDayNames[response.body.metrics[i].weekDay ]);
                        }
                        if (self.chart) {
                            self.chart.destroy();
                        }
                        self.chart = new Chart(document.getElementById("playcount-metrics-chart-weekday"), {
                            type: 'bar',
                            data: {
                                labels: weekDays,
                                datasets: [
                                    {
                                        "label": "play stats by weekday",
                                        "data": data,
                                        "backgroundColor": "#3273dc"
                                    }
                                ]
                            }, options: commonChartOptions
                        });
                        self.loading = false;
                    } else {
                        self.errors = true;
                        self.loading = false;
                    }
                });
            },
            loadMetricsByMonthChart: function () {
                var self = this;
                var d = {};
                self.loading = true;
                self.errors = false;
                spieldoseAPI.getPlayStatMetricsByMonth(function (response) {
                    if (response.ok) {
                        var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                        var months = [];
                        var data = [];
                        for (var i = 0; i < response.body.metrics.length; i++) {
                            data.push(response.body.metrics[i].total);
                            months.push(monthNames[response.body.metrics[i].month - 1]);
                        }
                        if (self.chart) {
                            self.chart.destroy();
                        }
                        self.chart = new Chart(document.getElementById("playcount-metrics-chart-month"), {
                            type: 'bar',
                            data: {
                                labels: months,
                                datasets: [
                                    {
                                        "label": "play stats by month",
                                        "data": data,
                                        "backgroundColor": "#3273dc"
                                    }
                                ]
                            }, options: commonChartOptions
                        });
                        self.loading = false;
                    } else {
                        self.errors = true;
                        self.loading = false;
                    }
                });
            },
            loadMetricsByYearChart: function () {
                var self = this;
                var d = {};
                self.loading = true;
                self.errors = false;
                spieldoseAPI.getPlayStatMetricsByYear(function (response) {
                    if (response.ok) {
                        var years = [];
                        var data = [];
                        for (var i = 0; i < response.body.metrics.length; i++) {
                            data.push(response.body.metrics[i].total);
                            years.push(response.body.metrics[i].year);
                        }
                        if (self.chart) {
                            self.chart.destroy();
                        }
                        self.chart = new Chart(document.getElementById("playcount-metrics-chart-year"), {
                            type: 'bar',
                            data: {
                                labels: years,
                                datasets: [
                                    {
                                        "label": "play stats by year",
                                        "data": data,
                                        "backgroundColor": "#3273dc"
                                    }
                                ]
                            }, options: commonChartOptions
                        });
                        self.loading = false;
                    } else {
                        self.errors = true;
                        self.loading = false;
                    }
                });
            },
            loadChart: function () {
                switch (this.interval) {
                    case "hour":
                        this.loadMetricsByHourChart();
                        break;
                    case "weekDay":
                        this.loadMetricsByWeekDayChart();
                        break;
                    case "month":
                        this.loadMetricsByMonthChart();
                        break;
                    case "year":
                        this.loadMetricsByYearChart();
                        break;
                }
            },
            changeInterval: function (i) {
                if (i && i != this.interval) {
                    this.interval = i;
                    this.loadChart();
                }
            }
        }
    });

    return (module);
})();