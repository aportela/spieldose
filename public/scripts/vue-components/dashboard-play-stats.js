"use strict";

var vTemplateDashboardPlayStats = function () {
    return `
    <section class="panel">
        <p class="panel-heading"><span class="icon"><i v-if="loading" class="fa fa-cog fa-spin fa-fw"></i><i v-else class="fa fa-line-chart"></i></span> Play stadistics</p>
        <p class="panel-tabs">
            <a v-bind:class="interval == 'day' ? 'is-active': ''" v-on:click.prevent="changeInterval('day');">by hour</a>
            <a v-bind:class="interval == 'weekday' ? 'is-active': ''" v-on:click.prevent="changeInterval('weekday');">by weekday</a>
            <a v-bind:class="interval == 'month' ? 'is-active': ''" v-on:click.prevent="changeInterval('month');">by month</a>
            <a v-bind:class="interval == 'year' ? 'is-active': ''" v-on:click.prevent="changeInterval('year');">by year</a>
        </p>
        <div class="panel-block">
            <canvas id="playcount-metrics-chart" height="200"></canvas>
        </div>
    </section>
    `;
}

/* app chart (test) component */
var dashboardPlayStats = Vue.component('spieldose-dashboard-play-stats', {
    template: vTemplateDashboardPlayStats(),
    data: function () {
        return ({
            loading: false,
            errors: false,
            interval: "day",
            items: []
        });
    },
    created: function () {
        this.loadChartData();
    }, methods: {
        loadChartData: function () {
            var self = this;
            var d = {};
            self.loading = true;
            self.errors = false;
            spieldoseAPI.getPlayStatMetrics(function (response) {
                if (response.ok) {
                    var d = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                    for (var i = 0; i < response.body.metrics.length; i++) {
                        d[response.body.metrics[i].hour] = response.body.metrics[i].total;
                    }
                    var ctx = document.getElementById("playcount-metrics-chart");
                    var myLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'],
                            datasets: [
                                {
                                    "label": "by hour",
                                    "data": d,
                                    "fill": true,
                                    "borderColor": "rgb(75, 192, 192)",
                                    "lineTension": 0.1
                                }
                            ]
                        }, options: {}
                    });
                    self.loading = false;
                } else {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                    self.loading = false;
                }
            });
        }, changeInterval: function (i) {
            if (i && i != this.interval) {
                this.interval = i;
                this.loadChartData();
            }
        }
    }
});
