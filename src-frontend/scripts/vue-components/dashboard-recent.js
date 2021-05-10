import { default as spieldoseAPI } from '../api.js';
import { mixinTopRecentCharts } from '../mixins.js';

const template = function () {
    return `
        <section class="panel chart height-100">
            <p class="panel-heading">
                <span class="icon">
                    <i class="fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                    <i class="fas fa-exclamation-triangle" v-else-if="errors"></i>
                    <i class="far fa-clock" v-else></i>
                </span>
                <span>{{ title }}</span>
                <a class="icon is-pulled-right" v-bind:title="$t('commonMessages.refreshData')" @click.prevent="load();"><i class="fas fa-redo fa-fw"></i></a>
            </p>
            <p class="panel-tabs">
                <a v-bind:class="{ 'is-active' : isTrackEntity }" @click.prevent="changeEntity('tracks');">{{ $t("dashboard.labels.entityTracks") }}</a>
                <a v-bind:class="{ 'is-active' : isArtistEntity }" @click.prevent="changeEntity('artists');">{{ $t("dashboard.labels.entityArtists") }}</a>
                <a v-bind:class="{ 'is-active' : isAlbumEntity }" @click.prevent="changeEntity('albums');">{{ $t("dashboard.labels.entityAlbums") }}</a>
            </p>
            <div class="panel-block cut-text">
                <ol v-if="hasItems">
                    <li class="is-small" v-if="isTrackEntity" v-for="item, i in items" v-bind:key="i">
                        <span class="icon"><i class="cursor-pointer fa fa-play" @click="onPlayTrack(item);" v-bind:title="$t('commonLabels.playThisTrack')"></i></span>
                        <span class="icon"><i class="cursor-pointer fa fa-plus-square" @click="onEnqueueTrack(item);" v-bind:title="$t('commonLabels.enqueueThisTrack')"></i></span>
                        <span>{{ item.title }}</span>
                        <span v-if="item.artist"> / <router-link :to="{ name: 'artist', params: { artist: item.artist }}" :title="$t('commonLabels.navigateToArtistPage')">{{ item.artist }}</router-link></span>
                    </li>
                    <li class="is-small" v-if="isArtistEntity" v-for="item in items">
                        <span class="icon"><i class="fas fa-compact-disc"></i></span>
                        <router-link :to="{ name: 'artist', params: { artist: item.artist }}" :title="$t('commonLabels.navigateToArtistPage')">{{ item.artist }}</router-link>
                    </li>
                    <li class="is-small" v-if="isAlbumEntity" v-for="item in items">
                        <span class="icon"><i class="cursor-pointer fa fa-play" v-bind:title="$t('commonLabels.playThisAlbum')" @click="onPlayAlbum(item);" ></i></span>
                        <span class="icon"><i class="cursor-pointer fa fa-plus-square" v-bind:title="$t('commonLabels.enqueueThisAlbum')" @click="onEnqueueAlbum(item);"></i></span>
                        <span>{{ item.album }}</span>
                        <span v-if="item.artist"> / <router-link :to="{ name: 'artist', params: { artist: item.artist }}" :title="$t('commonLabels.navigateToArtistPage')">{{ item.artist }}</router-link></span>
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
        mixinTopRecentCharts
    ],
    data: function () {
        return ({
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
        changeEntity: function (entity) {
            if (!this.loading) {
                if (entity && entity != this.actualEntity) {
                    this.actualEntity = entity;
                    this.load();
                }
            }
        },
        load: function () {
            this.errors = false;
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
        loadRecentAddedTracks: function () {
            spieldoseAPI.metrics.getRecentAddedTracks(this.interval, (response) => {
                if (response) {
                    if (response.status == 200) {
                        if (response.data.metrics && response.data.metrics.length > 0) {
                            this.items = response.data.metrics;
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
        },
        loadRecentAddedArtists: function () {
            spieldoseAPI.metrics.getRecentAddedArtists(this.interval, (response) => {
                if (response) {
                    if (response.status == 200) {
                        if (response.data.metrics && response.data.metrics.length > 0) {
                            this.items = response.data.metrics;
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
        },
        loadRecentAddedAlbums: function () {
            spieldoseAPI.metrics.getRecentAddedAlbums(this.interval, (response) => {
                if (response) {
                    if (response.status == 200) {
                        if (response.data.metrics && response.data.metrics.length > 0) {
                            this.items = response.data.metrics;
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
        },
        loadRecentPlayedTracks: function () {
            spieldoseAPI.metrics.getRecentPlayedTracks(this.interval, (response) => {
                if (response) {
                    if (response.status == 200) {
                        if (response.data.metrics && response.data.metrics.length > 0) {
                            this.items = response.data.metrics;
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
        },
        loadRecentPlayedArtists: function () {
            spieldoseAPI.metrics.getRecentPlayedArtists(this.interval, (response) => {
                if (response) {
                    if (response.status == 200) {
                        if (response.data.metrics && response.data.metrics.length > 0) {
                            this.items = response.data.metrics;
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
        },
        loadRecentPlayedAlbums: function () {
            spieldoseAPI.metrics.getRecentPlayedAlbums(this.interval, (response) => {
                if (response) {
                    if (response.status == 200) {
                        if (response.data.metrics && response.data.metrics.length > 0) {
                            this.items = response.data.metrics;
                        }
                    } else {
                        this.errors = true;
                    }
                    this.loading = false;
                }
            });
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
    }
}