import { default as spieldoseAPI } from '../api.js';
import Chart from 'chart.js/auto';

const template = function () {
    return `
        <section class="panel">
            <p class="panel-heading">
                <span class="icon">
                    <i class="fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                    <i class="fas fa-exclamation-triangle" v-else-if="errors"></i>
                    <i class="fas fa-chart-line" v-else></i>
                </span>
                <span>{{ $t("dashboard.labels.playStatistics") }}</span>
                <a class="icon is-pulled-right" v-bind:title="$t('commonMessages.refreshData')" @click.prevent="loadChart();"><i class="fas fa-redo fa-fw"></i></a>
            </p>
            <p class="panel-tabs">
                <a v-bind:class="{ 'is-active': isHourInterval }" @click.prevent="changeInterval('hour');">{{ $t("dashboard.labels.byHour") }}</a>
                <a v-bind:class="{ 'is-active': isWeekDayInterval }" @click.prevent="changeInterval('weekDay');">{{ $t("dashboard.labels.byWeekday") }}</a>
                <a v-bind:class="{ 'is-active': isMonthInterval }" @click.prevent="changeInterval('month');">{{ $t("dashboard.labels.byMonth") }}</a>
                <a v-bind:class="{ 'is-active': isYearInterval }" @click.prevent="changeInterval('year');">{{ $t("dashboard.labels.byYear") }}</a>
            </p>
            <div class="panel-block" v-if="! errors">
                <canvas v-show="isHourInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-hour" height="200"></canvas>
                <canvas v-show="isWeekDayInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-weekday" height="200"></canvas>
                <canvas v-show="isMonthInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-month" height="200"></canvas>
                <canvas v-show="isYearInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-year" height="200"></canvas>
            </div>
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
    data: function () {
        return ({
            loading: false,
            errors: false,
            items: [],
            activeInterval: 'hour',
            chartHours: null,
            chartWeekDays: null,
            chartMonths: null,
            chartYears: null
        });
    },
    mounted: function () {
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
        changeInterval: function (interval) {
            if (!this.loading) {
                if (interval && interval != this.activeInterval) {
                    this.activeInterval = interval;
                    this.$nextTick(() => this.loadChart());
                }
            }
        },
        loadChart: function () {
            this.errors = false;
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
        loadMetricsByHourChart: function () {
            spieldoseAPI.metrics.getPlayStatMetricsByHour((response) => {
                if (response) {
                    if (response.status == 200) {
                        const hourNames = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
                        let data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                        for (let i = 0; i < response.data.metrics.length; i++) {
                            data[response.data.metrics[i].hour] = response.data.metrics[i].total;
                        }
                        const element = document.getElementById('playcount-metrics-chart-hour');
                        if (element && ! this.chartHours) {
                            this.chartHours = new Chart(element, {
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
                                },
                                options: commonChartOptions
                            });
                        } else {
                            console.error("error loading chart: canvas not found");
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
        },
        loadMetricsByWeekDayChart: function () {
            spieldoseAPI.metrics.getPlayStatMetricsByWeekDay((response) => {
                if (response) {
                    if (response.status == 200) {
                        const weekDayNames = [
                            this.$t('dashboard.labels.sunday'),
                            this.$t('dashboard.labels.monday'),
                            this.$t('dashboard.labels.tuesday'),
                            this.$t('dashboard.labels.wednesday'),
                            this.$t('dashboard.labels.thursday'),
                            this.$t('dashboard.labels.friday'),
                            this.$t('dashboard.labels.saturday')
                        ];
                        let weekDays = [];
                        let data = [];
                        for (let i = 0; i < response.data.metrics.length; i++) {
                            data.push(response.data.metrics[i].total);
                            weekDays.push(weekDayNames[response.data.metrics[i].weekDay]);
                        }
                        const element = document.getElementById('playcount-metrics-chart-weekday');
                        if (element && ! this.chartWeekDays) {
                            this.chartWeekDays = new Chart(element, {
                                type: 'bar',
                                data: {
                                    labels: weekDays,
                                    datasets: [
                                        {
                                            'label': this.$t('dashboard.labels.playStatsByWeekday'),
                                            'data': data,
                                            'backgroundColor': '#3273dc'
                                        }
                                    ]
                                },
                                options: commonChartOptions
                            });
                        } else {
                            console.error("error loading chart: canvas not found");
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
        },
        loadMetricsByMonthChart: function () {
            spieldoseAPI.metrics.getPlayStatMetricsByMonth((response) => {
                if (response) {
                    if (response.status == 200) {
                        const monthNames = [
                            this.$t('dashboard.labels.january'),
                            this.$t('dashboard.labels.february'),
                            this.$t('dashboard.labels.march'),
                            this.$t('dashboard.labels.april'),
                            this.$t('dashboard.labels.may'),
                            this.$t('dashboard.labels.june'),
                            this.$t('dashboard.labels.july'),
                            this.$t('dashboard.labels.august'),
                            this.$t('dashboard.labels.september'),
                            this.$t('dashboard.labels.october'),
                            this.$t('dashboard.labels.november'),
                            this.$t('dashboard.labels.december')
                        ];
                        let months = [];
                        let data = [];
                        for (let i = 0; i < response.data.metrics.length; i++) {
                            data.push(response.data.metrics[i].total);
                            months.push(monthNames[response.data.metrics[i].month - 1]);
                        }
                        const element = document.getElementById('playcount-metrics-chart-month');
                        if (element && ! this.chartMonths) {
                            this.chartMonths = new Chart(element, {
                                type: 'bar',
                                data: {
                                    labels: months,
                                    datasets: [
                                        {
                                            'label': this.$t('dashboard.labels.playStatsByMonth'),
                                            'data': data,
                                            'backgroundColor': '#3273dc'
                                        }
                                    ]
                                },
                                options: commonChartOptions
                            });
                        } else {
                            console.error("error loading chart: canvas not found");
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
        },
        loadMetricsByYearChart: function () {
            spieldoseAPI.metrics.getPlayStatMetricsByYear((response) => {
                if (response) {
                    if (response.status == 200) {
                        let years = [];
                        let data = [];
                        for (let i = 0; i < response.data.metrics.length; i++) {
                            data.push(response.data.metrics[i].total);
                            years.push(response.data.metrics[i].year);
                        }
                        const element = document.getElementById('playcount-metrics-chart-year');
                        if (element && ! this.chartYears) {
                            this.chartYears = new Chart(element, {
                                type: 'bar',
                                data: {
                                    labels: years,
                                    datasets: [
                                        {
                                            'label': this.$t('dashboard.labels.playStatsByYear'),
                                            'data': data,
                                            'backgroundColor': '#3273dc'
                                        }
                                    ]
                                },
                                options: commonChartOptions
                            });
                        } else {
                            console.error("error loading chart: canvas not found");
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
        }
    }
}