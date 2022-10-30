import { mixinAPIError, mixinTopRecentCharts, mixinNavigation } from '../mixins.js';

const template = function () {
    return `
        <section class="panel chart height-100">
            <p class="panel-heading">
                <span class="icon mr-1">
                    <i class="fa-fw fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                    <i class="fa-fw fas fa-exclamation-triangle" v-else-if="hasAPIErrors"></i>
                    <i class="fa-fw far fa-clock" v-else></i>
                </span>
                <span>{{ title }}</span>
                <a class="icon is-pulled-right" v-bind:title="$t('commonMessages.refreshData')" v-on:click.prevent="load();"><i class="fas fa-redo fa-fw"></i></a>
            </p>
            <p class="panel-tabs">
                <a v-bind:class="{ 'is-active' : isTrackEntity }" v-on:click.prevent="changeEntity('tracks');">{{ $t("dashboard.labels.entityTracks") }}</a>
                <a v-bind:class="{ 'is-active' : isArtistEntity }" v-on:click.prevent="changeEntity('artists');">{{ $t("dashboard.labels.entityArtists") }}</a>
                <a v-bind:class="{ 'is-active' : isAlbumEntity }" v-on:click.prevent="changeEntity('albums');">{{ $t("dashboard.labels.entityAlbums") }}</a>
            </p>
            <div class="panel-block cut-text">
                <ol class="pl-5" v-if="hasItems">
                    <li class="is-size-6-5" v-if="isTrackEntity" v-for="item, i in items" v-bind:key="i">
                        <i class="cursor-pointer fa-fw fa fa-play" v-on:click="playTrack(item);" v-bind:title="$t('commonLabels.playThisTrack')"></i>
                        <i class="cursor-pointer fa-fw fa fa-plus-square mr-1" v-on:click="enqueueTrack(item);" v-bind:title="$t('commonLabels.enqueueThisTrack')"></i>
                        <span>{{ item.title }}</span>
                        <span v-if="item.artist"> / <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a></span>
                    </li>
                    <li class="is-size-6-5" v-if="isArtistEntity" v-for="item in items">
                        <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a>
                    </li>
                    <li class="is-size-6-5" v-if="isAlbumEntity" v-for="item in items">
                        <i class="cursor-pointer fa-fw fa fa-play" v-bind:title="$t('commonLabels.playThisAlbum')" v-on:click="playAlbum(item);" ></i>
                        <i class="cursor-pointer fa-fw fa fa-plus-square mr-1" v-bind:title="$t('commonLabels.enqueueThisAlbum')" v-on:click="enqueueAlbum(item);"></i>
                        <span>{{ item.album }}</span>
                        <span v-if="item.artist"> / <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a></span>
                    </li>
                </ol>
                <p v-else-if="! hasItems && ! loading && ! hasAPIErrors">{{ $t("dashboard.errors.notEnoughData") }}</p>
                <p v-else-if="hasAPIErrors">{{ $t("commonErrors.invalidAPIResponse") }}</p>
            </div>
        </section>
    `;
};

export default {
    name: 'spieldose-dashboard-recent',
    template: template(),
    mixins: [
        mixinAPIError, mixinTopRecentCharts, mixinNavigation
    ],
    data: function () {
        return ({
            loading: false,
            actualEntity: 'tracks'
        });
    },
    props: [
        'type', 'title', 'listItemCount'
    ],
    computed: {
        isTrackEntity: function () {
            return (this.actualEntity == 'tracks');
        },
        isArtistEntity: function () {
            return (this.actualEntity == 'artists');
        },
        isAlbumEntity: function () {
            return (this.actualEntity == 'albums');
        },
    },
    methods: {
        loadRecentAddedTracks: function () {
            this.$api.metrics.getRecentAddedTracks(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        loadRecentAddedArtists: function () {
            this.$api.metrics.getRecentAddedArtists(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        loadRecentAddedAlbums: function () {
            this.$api.metrics.getRecentAddedAlbums(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        loadRecentPlayedTracks: function () {
            this.$api.metrics.getRecentPlayedTracks(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        loadRecentPlayedArtists: function () {
            this.$api.metrics.getRecentPlayedArtists(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        loadRecentPlayedAlbums: function () {
            this.$api.metrics.getRecentPlayedAlbums(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log(error); // this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        load: function () {
            this.clearAPIErrors();
            this.loading = true;
            this.items = [];
            switch (this.type) {
                case 'recentlyAdded':
                    this.loadRecentAdded();
                    break;
                case 'recentlyPlayed':
                    this.loadRecentPlayed();
                    break;
                default:
                    this.loading = false;
                    break;
            }
        },
        loadRecentAdded: function () {
            switch (this.actualEntity) {
                case 'tracks':
                    this.loadRecentAddedTracks();
                    break;
                case 'artists':
                    this.loadRecentAddedArtists();
                    break;
                case 'albums':
                    this.loadRecentAddedAlbums();
                    break;
                default:
                    this.loading = false;
                    break;
            }
        },
        loadRecentPlayed: function () {
            switch (this.actualEntity) {
                case 'tracks':
                    this.loadRecentPlayedTracks();
                    break;
                case 'artists':
                    this.loadRecentPlayedArtists();
                    break;
                case 'albums':
                    this.loadRecentPlayedAlbums();
                    break;
                default:
                    this.loading = false;
                    break;
            }
        },
        changeEntity: function (entity) {
            if (!this.loading) {
                if (entity && entity != this.actualEntity) {
                    this.actualEntity = entity;
                    this.load();
                }
            }
        },
        playAlbum: function (album) {
            // TODO
        },
        enqueueAlbum: function (album) {
            // TODO
        }
    }
}