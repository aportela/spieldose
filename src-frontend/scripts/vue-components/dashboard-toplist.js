import { mixinAPIError, mixinTopRecentCharts, mixinNavigation } from '../mixins.js';
const template = function () {
    return `
        <section class="panel chart height-100">
            <p class="panel-heading">
                <span class="icon mr-1">
                    <i class="fa-fw fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                    <i class="fa-fw fas fa-exclamation-triangle" v-else-if="hasAPIErrors"></i>
                    <i class="fa-fw fas fa-list" v-else></i>
                </span>
                <span>{{ title }}</span>
                <a class="icon is-pulled-right" v-bind:title="$t('commonMessages.refreshData')" v-on:click.prevent="load();"><i class="fas fa-redo fa-fw2"></i></a>
            </p>
            <p class="panel-tabs">
                <a v-bind:class="{ 'is-active' : isAllTimeInterval }" v-on:click.prevent="changeInterval(0);">{{ $t("dashboard.labels.allTimeInterval") }}</a>
                <a v-bind:class="{ 'is-active' : isPastWeekInterval }" v-on:click.prevent="changeInterval(1);">{{ $t("dashboard.labels.pastWeekInterval") }}</a>
                <a v-bind:class="{ 'is-active' : isPastMonthInterval }" v-on:click.prevent="changeInterval(2);">{{ $t("dashboard.labels.pastMonthInterval") }}</a>
                <a v-bind:class="{ 'is-active' : isPastSemesterInterval }" v-on:click.prevent="changeInterval(3);">{{ $t("dashboard.labels.pastSemesterInterval") }}</a>
                <a v-bind:class="{ 'is-active' : isPastYearInterval }" v-on:click.prevent="changeInterval(4);">{{ $t("dashboard.labels.pastYearInterval") }}</a>
            </p>
            <div class="panel-block cut-text">
                <ol class="pl-5 is-size-6" v-if="items.length > 0">
                    <li class="is-size-7" v-if="isTopTracksType" v-for="item, i in items" v-bind:key="i">
                        <i class="cursor-pointer fa-fw fa fa-play" v-on:click="playTrack(item);" v-bind:title="$t('commonLabels.playThisTrack')"></i>
                        <i class="cursor-pointer fa-fw fa fa-plus-square mr-1" v-on:click="enqueueTrack(item);" v-bind:title="$t('commonLabels.enqueueThisTrack')"></i>
                        <span>{{ item.title }}</span>
                        <span v-if="item.artist"> / <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a></span>
                        <span v-if="showPlayCount"> ({{ item.total }} {{ $t('dashboard.labels.playCount') }})</span>
                    </li>
                    <li class="is-size-7" v-if="isTopArtistsType" v-for="item in items">
                        <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a>
                        <span v-if="showPlayCount"> ({{ item.total }} {{ $t('dashboard.labels.playCount') }})</span>
                    </li>
                    <li class="is-size-7" v-if="isTopGenresType" v-for="item in items">
                        <span>{{ item.genre }}</span>
                        <span v-if="showPlayCount"> ({{ item.total }} {{ $t('dashboard.labels.playCount') }})</span>
                    </li>
                </ol>
                <p v-else-if="! hasItems && ! loading && ! hasAPIErrors">{{ $t("dashboard.errors.notEnoughData") }}</p>
                <p v-else-if="hasAPIErrors">{{ $t("commonErrors.invalidAPIResponse") }}</p>
            </div>
        </section>
    `;
};

export default {
    name: 'spieldose-dashboard-toplist',
    template: template(),
    mixins: [
        mixinAPIError, mixinTopRecentCharts, mixinNavigation
    ],
    data: function () {
        return ({
            loading: false,
            activeInterval: 0,
        });
    },
    props: [
        'type', 'title', 'listItemCount', 'showPlayCount', 'artist'
    ],
    computed: {
        isAllTimeInterval: function () {
            return (this.activeInterval == 0);
        },
        isPastWeekInterval: function () {
            return (this.activeInterval == 1);
        },
        isPastMonthInterval: function () {
            return (this.activeInterval == 2);
        },
        isPastSemesterInterval: function () {
            return (this.activeInterval == 3);
        },
        isPastYearInterval: function () {
            return (this.activeInterval == 4);
        },
        isTopTracksType: function () {
            return (this.type == 'topTracks');
        },
        isTopArtistsType: function () {
            return (this.type == 'topArtists');
        },
        isTopGenresType: function () {
            return (this.type == 'topGenres');
        }
    },
    methods: {
        loadTopPlayedTracks: function () {
            this.$api.metrics.getTopPlayedTracks(this.activeInterval, this.artist).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        }, loadTopPlayedArtists: function () {
            this.$api.metrics.getTopPlayedArtists(this.activeInterval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        }, loadTopPlayedGenres: function () {
            this.$api.metrics.getTopPlayedGenres(this.activeInterval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        }, changeInterval: function (interval) {
            if (!this.loading) {
                if (this.activeInterval != interval) {
                    this.activeInterval = interval;
                    this.load();
                }
            }
        }, load: function () {
            this.clearAPIErrors();
            this.loading = true;
            this.items = [];
            switch (this.type) {
                case 'topTracks':
                    this.loadTopPlayedTracks();
                    break;
                case 'topArtists':
                    this.loadTopPlayedArtists();
                    break;
                case 'topGenres':
                    this.loadTopPlayedGenres();
                    break;
                default:
                    this.loading = false;
                    break;
            }
        }
    }
}