import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinTopRecentCharts, mixinNavigation, mixinPlayer } from '../mixins.js';

const template = function () {
    return `
        <section class="panel chart height-100">
            <p class="panel-heading">
                <span class="icon">
                    <i class="fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                    <i class="fas fa-exclamation-triangle" v-else-if="hasAPIErrors"></i>
                    <i class="fas fa-list" v-else></i>
                </span>
                <span>{{ title }}</span>
                <a class="icon is-pulled-right" v-bind:title="$t('commonMessages.refreshData')" @click.prevent="load();"><i class="fas fa-redo fa-fw2"></i></a>
            </p>
            <p class="panel-tabs">
                <a v-bind:class="{ 'is-active' : isAllTimeInterval }" @click.prevent="changeInterval(0);">{{ $t("dashboard.labels.allTimeInterval") }}</a>
                <a v-bind:class="{ 'is-active' : isPastWeekInterval }" @click.prevent="changeInterval(1);">{{ $t("dashboard.labels.pastWeekInterval") }}</a>
                <a v-bind:class="{ 'is-active' : isPastMonthInterval }" @click.prevent="changeInterval(2);">{{ $t("dashboard.labels.pastMonthInterval") }}</a>
                <a v-bind:class="{ 'is-active' : isPastSemesterInterval }" @click.prevent="changeInterval(3);">{{ $t("dashboard.labels.pastSemesterInterval") }}</a>
                <a v-bind:class="{ 'is-active' : isPastYearInterval }" @click.prevent="changeInterval(4);">{{ $t("dashboard.labels.pastYearInterval") }}</a>
            </p>
            <div class="panel-block cut-text">
                <ol v-if="items.length > 0">
                    <li class="is-small" v-if="isTopTracksType" v-for="item, i in items" v-bind:key="i">
                        <span class="icon"><i class="cursor-pointer fa fa-play" @click="playTrack(item);" v-bind:title="$t('commonLabels.playThisTrack')"></i></span>
                        <span class="icon"><i class="cursor-pointer fa fa-plus-square" @click="enqueueTrack(item);" v-bind:title="$t('commonLabels.enqueueThisTrack')"></i></span>
                        <span>{{ item.title }}</span>
                        <span v-if="item.artist"> / <a v-bind:title="$t('commonLabels.navigateToArtistPage')" @click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a></span>
                        <span v-if="showPlayCount"> ({{ item.total }} {{ $t('dashboard.labels.playCount') }})</span>
                    </li>
                    <li class="is-small" v-if="isTopArtistsType" v-for="item in items">
                        <span class="icon"><i class="fas fa-compact-disc"></i></span><a v-bind:title="$t('commonLabels.navigateToArtistPage')" @click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a>
                        <span v-if="showPlayCount"> ({{ item.total }} {{ $t('dashboard.labels.playCount') }})</span>
                    </li>
                    <li class="is-small" v-if="isTopAlbumsType" v-for="item in items">
                        <span class="icon"><i class="cursor-pointer fa fa-play" @click="playAlbumTracks(item.album, item.artist);" v-bind:title="$t('commonLabels.playThisTrack')"></i></span>
                        <span class="icon"><i class="cursor-pointer fa fa-plus-square" @click="enqueueAlbumTracks(item.album, item.artist);" v-bind:title="$t('commonLabels.enqueueThisTrack')"></i></span>
                        <span>{{ item.album }}</span> / <a v-bind:title="$t('commonLabels.navigateToArtistPage')" @click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a>
                        <span v-if="showPlayCount"> ({{ item.total }} {{ $t('dashboard.labels.playCount') }})</span>
                    </li>
                    <li class="is-small" v-if="isTopGenresType" v-for="item in items">
                        <span class="icon"><i class="fas fa-compact-disc"></i></span><span>{{ item.genre }}</span>
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
        mixinAPIError, mixinTopRecentCharts, mixinNavigation, mixinPlayer
    ],
    data: function () {
        return ({
            loading: false,
            activeInterval: 0
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
        isTopAlbumsType: function () {
            return (this.type == 'topAlbums');
        },
        isTopGenresType: function () {
            return (this.type == 'topGenres');
        }
    },
    methods: {
        loadTopPlayedTracks: function () {
            spieldoseAPI.metrics.getTopPlayedTracks(this.activeInterval, this.artist, (response) => {
                if (response.status == 200) {
                    if (response.data.metrics && response.data.metrics.length > 0) {
                        this.items = response.data.metrics;
                    }
                } else {
                    //this.setAPIError(response.getApiErrorData());
                }
                this.loading = false;
            });
        }, loadTopPlayedArtists: function () {
            spieldoseAPI.metrics.getTopPlayedArtists(this.activeInterval, (response) => {
                if (response.status == 200) {
                    if (response.data.metrics && response.data.metrics.length > 0) {
                        this.items = response.data.metrics;
                    }
                } else {
                    //this.setAPIError(response.getApiErrorData());
                }
                this.loading = false;
            });
        }, loadTopPlayedAlbums: function () {
            spieldoseAPI.metrics.getTopPlayedAlbums(this.activeInterval, (response) => {
                if (response.status == 200) {
                    if (response.data.metrics && response.data.metrics.length > 0) {
                        this.items = response.data.metrics;
                    }
                } else {
                    //this.setAPIError(response.getApiErrorData());
                }
                this.loading = false;
            });
        }, loadTopPlayedGenres: function () {
            spieldoseAPI.metrics.getTopPlayedGenres(this.activeInterval, (response) => {
                if (response.status == 200) {
                    if (response.data.metrics && response.data.metrics.length > 0) {
                        this.items = response.data.metrics;
                    }
                } else {
                    //this.setAPIError(response.getApiErrorData());
                }
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