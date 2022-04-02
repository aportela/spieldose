import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError } from '../mixins.js';

const template = function () {
    return `
        <section class="panel">
            <p class="panel-heading">
                <span class="icon">
                    <i class="fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                    <i class="fas fa-exclamation-triangle" v-else-if="hasAPIErrors"></i>
                    <i class="fas fa-chart-line" v-else></i>
                </span>
                <span>{{ $t("dashboard.labels.playStatistics") }}</span>
                <a class="icon is-pulled-right" v-bind:title="$t('commonMessages.refreshData')" v-on:click.prevent="loadChart();"><i class="fas fa-redo fa-fw"></i></a>
            </p>
            <p class="panel-tabs">
                <a v-bind:class="{ 'is-active': isHourInterval }" v-on:click.prevent="changeInterval('hour');">{{ $t("dashboard.labels.byHour") }}</a>
                <a v-bind:class="{ 'is-active': isWeekDayInterval }" v-on:click.prevent="changeInterval('weekDay');">{{ $t("dashboard.labels.byWeekday") }}</a>
                <a v-bind:class="{ 'is-active': isMonthInterval }" v-on:click.prevent="changeInterval('month');">{{ $t("dashboard.labels.byMonth") }}</a>
                <a v-bind:class="{ 'is-active': isYearInterval }" v-on:click.prevent="changeInterval('year');">{{ $t("dashboard.labels.byYear") }}</a>
            </p>
            <div class="panel-block" v-if="! hasAPIErrors">
                <canvas v-if="isHourInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-hour" height="200"></canvas>
                <canvas v-else-if="isWeekDayInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-weekday" height="200"></canvas>
                <canvas v-else-if="isMonthInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-month" height="200"></canvas>
                <canvas v-else-if="isYearInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-year" height="200"></canvas>
            </div>
            <div class="panel-block" v-else>{{ $t("commonErrors.invalidAPIResponse") }}</div>
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

export default {
    name: 'spieldose-dashboard-play-stats',
    template: template(),
    mixins: [mixinAPIError],
    data: function () {
        return ({
            chart: null,
            loading: false,
            activeInterval: 'hour',
            items: []
        });
    },
    created: function () {
        this.loadChart();
    },
    computed: {
        isHourInterval: function () {
            return (this.activeInterval == 'hour');
        },
        isWeekDayInterval: function () {
            return (this.activeInterval == 'weekDay');
        },
        isMonthInterval: function () {
            return (this.activeInterval == 'month');
        },
        isYearInterval: function () {
            return (this.activeInterval == 'year');
        }
    },
    methods: {
        loadMetricsByHourChart: function () {
            let self = this;
            spieldoseAPI.metrics.getPlayStatMetricsByHour(function (response) {
                if (response.status == 200) {
                    const hourNames = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
                    let data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                    for (let i = 0; i < response.data.metrics.length; i++) {
                        data[response.data.metrics[i].hour] = response.data.metrics[i].total;
                    }
                    if (self.chart) {
                        self.chart.destroy();
                    }
                    self.chart = new Chart(document.getElementById('playcount-metrics-chart-hour'), {
                        type: 'line',
                        data: {
                            labels: hourNames,
                            datasets: [
                                {
                                    'label': 'play stats by hour',
                                    'data': data,
                                    'fill': true,
                                    'borderColor': '#3273dc',
                                    'lineTension': 0.1
                                }
                            ]
                        }, options: commonChartOptions
                    });
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
                self.loading = false;
            });
        },
        loadMetricsByWeekDayChart: function () {
            let self = this;
            spieldoseAPI.metrics.getPlayStatMetricsByWeekDay(function (response) {
                if (response.status == 200) {
                    const weekDayNames = [
                        self.$t('dashboard.labels.sunday'),
                        self.$t('dashboard.labels.monday'),
                        self.$t('dashboard.labels.tuesday'),
                        self.$t('dashboard.labels.wednesday'),
                        self.$t('dashboard.labels.thursday'),
                        self.$t('dashboard.labels.friday'),
                        self.$t('dashboard.labels.saturday')
                    ];
                    let weekDays = [];
                    let data = [];
                    for (let i = 0; i < response.data.metrics.length; i++) {
                        data.push(response.data.metrics[i].total);
                        weekDays.push(weekDayNames[response.data.metrics[i].weekDay]);
                    }
                    if (self.chart) {
                        self.chart.destroy();
                    }
                    self.chart = new Chart(document.getElementById('playcount-metrics-chart-weekday'), {
                        type: 'bar',
                        data: {
                            labels: weekDays,
                            datasets: [
                                {
                                    'label': self.$t('dashboard.labels.playStatsByWeekday'),
                                    'data': data,
                                    'backgroundColor': '#3273dc'
                                }
                            ]
                        }, options: commonChartOptions
                    });
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
                self.loading = false;
            });
        },
        loadMetricsByMonthChart: function () {
            let self = this;
            spieldoseAPI.metrics.getPlayStatMetricsByMonth(function (response) {
                if (response.status == 200) {
                    const monthNames = [
                        self.$t('dashboard.labels.january'),
                        self.$t('dashboard.labels.february'),
                        self.$t('dashboard.labels.march'),
                        self.$t('dashboard.labels.april'),
                        self.$t('dashboard.labels.may'),
                        self.$t('dashboard.labels.june'),
                        self.$t('dashboard.labels.july'),
                        self.$t('dashboard.labels.august'),
                        self.$t('dashboard.labels.september'),
                        self.$t('dashboard.labels.october'),
                        self.$t('dashboard.labels.november'),
                        self.$t('dashboard.labels.december')
                    ];
                    let months = [];
                    let data = [];
                    for (let i = 0; i < response.data.metrics.length; i++) {
                        data.push(response.data.metrics[i].total);
                        months.push(monthNames[response.data.metrics[i].month - 1]);
                    }
                    if (self.chart) {
                        self.chart.destroy();
                    }
                    self.chart = new Chart(document.getElementById('playcount-metrics-chart-month'), {
                        type: 'bar',
                        data: {
                            labels: months,
                            datasets: [
                                {
                                    'label': self.$t('dashboard.labels.playStatsByMonth'),
                                    'data': data,
                                    'backgroundColor': '#3273dc'
                                }
                            ]
                        }, options: commonChartOptions
                    });
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
                self.loading = false;
            });
        },
        loadMetricsByYearChart: function () {
            let self = this;
            spieldoseAPI.metrics.getPlayStatMetricsByYear(function (response) {
                if (response.status == 200) {
                    let years = [];
                    let data = [];
                    for (let i = 0; i < response.data.metrics.length; i++) {
                        data.push(response.data.metrics[i].total);
                        years.push(response.data.metrics[i].year);
                    }
                    if (self.chart) {
                        self.chart.destroy();
                    }
                    self.chart = new Chart(document.getElementById('playcount-metrics-chart-year'), {
                        type: 'bar',
                        data: {
                            labels: years,
                            datasets: [
                                {
                                    'label': self.$t('dashboard.labels.playStatsByYear'),
                                    'data': data,
                                    'backgroundColor': '#3273dc'
                                }
                            ]
                        }, options: commonChartOptions
                    });
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
                self.loading = false;
            });
        },
        loadChart: function () {
            this.clearAPIErrors();
            this.loading = true;
            switch (this.activeInterval) {
                case 'hour':
                    this.loadMetricsByHourChart();
                    break;
                case 'weekDay':
                    this.loadMetricsByWeekDayChart();
                    break;
                case 'month':
                    this.loadMetricsByMonthChart();
                    break;
                case 'year':
                    this.loadMetricsByYearChart();
                    break;
                default:
                    this.loading = false;
                    break;
            }
        },
        changeInterval: function (interval) {
            if (!this.loading) {
                if (interval && interval != this.activeInterval) {
                    this.activeInterval = interval;
                    this.loadChart();
                }
            }
        }
    }
}