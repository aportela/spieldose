import { Chart, registerables } from 'Chart';
import { mixinAPIError } from '../mixins.js';
import Chartist from 'chartist-webpack';

const template = function () {
    return `
        <section class="panel">
            <p class="panel-heading">
                <span class="icon mr-1">
                    <i class="fa-fw fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                    <i class="fa-fw fas fa-exclamation-triangle" v-else-if="hasAPIErrors"></i>
                    <i class="fa-fw fas fa-chart-line" v-else></i>
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
                <canvas v-if="isHourInterval_" class="play-stats-metrics-graph" id="playcount-metrics-chart-hour" height="200"></canvas>
                <canvas v-else-if="isWeekDayInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-weekday" height="200"></canvas>
                <canvas v-else-if="isMonthInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-month" height="200"></canvas>
                <canvas v-else-if="isYearInterval" class="play-stats-metrics-graph" id="playcount-metrics-chart-year" height="200"></canvas>
                <div class="ct-chart ct-minor-seventh" class="play-stats-metrics-graph" style="height: 20em;"></div>
            </div>
            <div class="panel-block" v-else>{{ $t("commonErrors.invalidAPIResponse") }}</div>
        </section>
    `;
};

const commonChartOptions = {
    /*
    maintainAspectRatio: false,
    legend: {
        display: false
    },
    // https://stackoverflow.com/a/71239566
    scales: {
        y: {
            ticks: {
                precision: 0
            }
        }
    }
    */
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
            this.$api.metrics.getPlayStatMetricsByHour().then(response => {
                const hourNames = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
                let data = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                for (let i = 0; i < response.data.metrics.length; i++) {
                    data[response.data.metrics[i].hour] = response.data.metrics[i].total;
                }
                /*
                if (this.chart) {
                    this.chart.destroy();
                }
                let el = document.getElementById('playcount-metrics-chart-hour');
                if (el) {
                    this.chart = new Chart(el, {
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
                        }
                        , options: commonChartOptions
                    });
                }
                */
                var options = {
                    // If high is specified then the axis will display values explicitly up to this value and the computed maximum from the data is ignored
                    //high: 100,
                    // If low is specified then the axis will display values explicitly down to this value and the computed minimum from the data is ignored
                    low: 0,
                    // This option will be used when finding the right scale division settings. The amount of ticks on the scale will be determined so that as many ticks as possible will be displayed, while not violating this minimum required space (in pixel).
                    scaleMinSpace: 20,
                    // Can be set to true or false. If set to true, the scale will be generated with whole numbers only.
                    onlyInteger: true,
                    // The reference value can be used to make sure that this value will always be on the chart. This is especially useful on bipolar charts where the bipolar center always needs to be part of the chart.
                    //referenceValue: 5,
                    fullWidth: true,
                    showArea: true

                };
                new Chartist.Line('.ct-chart', {
                    labels: hourNames,
                    series: [
                        data
                    ]
                }, options

                );
                this.loading = false;
            }).catch(error => {
                console.log("error");
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        loadMetricsByWeekDayChart: function () {
            this.$api.metrics.getPlayStatMetricsByWeekDay().then(response => {
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
                if (this.chart) {
                    this.chart.destroy();
                }
                this.chart = new Chart(document.getElementById('playcount-metrics-chart-weekday'), {
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
                    }, options: commonChartOptions
                });
                this.loading = false;
            }).catch(error => {
                console.log(error);
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        loadMetricsByMonthChart: function () {
            this.$api.metrics.getPlayStatMetricsByMonth().then(response => {
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
                if (this.chart) {
                    this.chart.destroy();
                }
                this.chart = new Chart(document.getElementById('playcount-metrics-chart-month'), {
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
                    }, options: commonChartOptions
                });
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        loadMetricsByYearChart: function () {
            this.$api.metrics.getPlayStatMetricsByYear().then(response => {
                let years = [];
                let data = [];
                for (let i = 0; i < response.data.metrics.length; i++) {
                    data.push(response.data.metrics[i].total);
                    years.push(response.data.metrics[i].year);
                }
                if (this.chart) {
                    this.chart.destroy();
                }
                this.chart = new Chart(document.getElementById('playcount-metrics-chart-year'), {
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
                    }, options: commonChartOptions
                });
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
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