import { default as dashboardBlock } from './dashboard-block.js';
import { mixinTopRecentCharts } from '../mixins.js';

const template = function () {
    return `
        <spieldose-dashboard-block :extraClass="'chart height-100'":loading="loading" :errors="errors" :reloadFunction="load">
            <template #icon><i class="fa-fw fas fa-list"></i></template>
            <template #title>{{ title }}</template>
            <template #body>
                <p class="panel-tabs">
                    <a v-bind:class="{ 'is-active' : isAllTimeInterval }" v-on:click.prevent="changeInterval(0);">{{ $t("dashboard.labels.allTimeInterval") }}</a>
                    <a v-bind:class="{ 'is-active' : isPastWeekInterval }" v-on:click.prevent="changeInterval(1);">{{ $t("dashboard.labels.pastWeekInterval") }}</a>
                    <a v-bind:class="{ 'is-active' : isPastMonthInterval }" v-on:click.prevent="changeInterval(2);">{{ $t("dashboard.labels.pastMonthInterval") }}</a>
                    <a v-bind:class="{ 'is-active' : isPastSemesterInterval }" v-on:click.prevent="changeInterval(3);">{{ $t("dashboard.labels.pastSemesterInterval") }}</a>
                    <a v-bind:class="{ 'is-active' : isPastYearInterval }" v-on:click.prevent="changeInterval(4);">{{ $t("dashboard.labels.pastYearInterval") }}</a>
                </p>
                <div class="panel-block cut-text">
                    <ol class="pl-5 is-size-6-5" v-if="items.length > 0">
                        <li class="is-size-6-5" v-if="isTopTracksType" v-for="item, i in items" v-bind:key="i">
                            <i class="cursor-pointer fa-fw fa fa-play" v-on:click="playTrack(item);" v-bind:title="$t('commonLabels.playThisTrack')"></i>
                            <i class="cursor-pointer fa-fw fa fa-plus-square mr-1" v-on:click="enqueueTrack(item);" v-bind:title="$t('commonLabels.enqueueThisTrack')"></i>
                            <span>{{ item.title }}</span>
                            <span v-if="item.artist"> / <router-link :to="{ name: 'artistPage', params: { name: item.artist }}" :title="$t('commonLabels.navigateToArtistPage')">{{ item.artist }}</router-link></span>
                            <span v-if="showPlayCount"> ({{ item.total }} {{ $t('dashboard.labels.playCount') }})</span>
                        </li>
                        <li class="is-size-6-5" v-if="isTopAlbumsType" v-for="item in items">
                            <span>{{ item.album }}</span>
                            <span v-if="item.artist"> / <router-link :to="{ name: 'artistPage', params: { name: item.artist }}" :title="$t('commonLabels.navigateToArtistPage')">{{ item.artist }}</router-link></span>
                        </li>
                        <li class="is-size-6-5" v-if="isTopArtistsType" v-for="item in items">
                            <router-link :to="{ name: 'artistPage', params: { name: item.artist }}" :title="$t('commonLabels.navigateToArtistPage')">{{ item.artist }}</router-link>
                            <span v-if="showPlayCount"> ({{ item.total }} {{ $t('dashboard.labels.playCount') }})</span>
                        </li>
                        <li class="is-size-6-5" v-if="isTopGenresType" v-for="item in items">
                            <span>{{ item.genre }}</span>
                            <span v-if="showPlayCount"> ({{ item.total }} {{ $t('dashboard.labels.playCount') }})</span>
                        </li>
                    </ol>
                    <p v-else-if="! hasItems && ! loading">{{ $t("dashboard.errors.notEnoughData") }}</p>
                </div>
            </template>
        </section>
    `;
};

export default {
    name: 'spieldose-dashboard-toplist',
    template: template(),
    mixins: [
        mixinTopRecentCharts
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
        isTopAlbumsType: function () {
            return (this.type == 'topAlbums');
        },
        isTopArtistsType: function () {
            return (this.type == 'topArtists');
        },
        isTopGenresType: function () {
            return (this.type == 'topGenres');
        }
    },
    components: {
        'spieldose-dashboard-block': dashboardBlock
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
        }, loadTopPlayedAlbums: function () {
            this.$api.metrics.getTopPlayedAlbums(this.activeInterval).then(response => {
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
            this.loading = true;
            this.items = [];
            switch (this.type) {
                case 'topTracks':
                    this.loadTopPlayedTracks();
                    break;
                case 'topArtists':
                    this.loadTopPlayedArtists();
                    break;
                case 'topAlbums':
                    this.loadTopPlayedAlbums();
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
