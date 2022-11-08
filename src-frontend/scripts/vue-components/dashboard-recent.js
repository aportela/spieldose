import { default as dashboardBlock } from './dashboard-block.js';
import { mixinTopRecentCharts } from '../mixins.js';

const template = function () {
    return `
        <spieldose-dashboard-block :extraClass="'chart height-100'":loading="loading" :errors="errors" :reloadFunction="load">
            <template #icon><i class="fa-fw far fa-clock"></i></template>
            <template #title>{{ title }}</template>
            <template #body>
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
                            <span v-if="item.artist"> / <router-link :to="{ name: 'artistPage', params: { name: item.artist }}" :title="$t('commonLabels.navigateToArtistPage')">{{ item.artist }}</router-link></span>
                        </li>
                        <li class="is-size-6-5" v-if="isArtistEntity" v-for="item in items">
                            <router-link :to="{ name: 'artistPage', params: { name: item.artist }}" :title="$t('commonLabels.navigateToArtistPage')">{{ item.artist }}</router-link>
                        </li>
                        <li class="is-size-6-5" v-if="isAlbumEntity" v-for="item in items">
                            <i class="cursor-pointer fa-fw fa fa-play" v-bind:title="$t('commonLabels.playThisAlbum')" v-on:click="playAlbum(item);" ></i>
                            <i class="cursor-pointer fa-fw fa fa-plus-square mr-1" v-bind:title="$t('commonLabels.enqueueThisAlbum')" v-on:click="enqueueAlbum(item);"></i>
                            <span>{{ item.album }}</span>
                            <span v-if="item.artist"> / <router-link :to="{ name: 'artistPage', params: { name: item.artist }}" :title="$t('commonLabels.navigateToArtistPage')">{{ item.artist }}</router-link></span>
                        </li>
                    </ol>
                    <p v-else-if="! hasItems && ! loading">{{ $t("dashboard.errors.notEnoughData") }}</p>
                </div>
            </template>
        </spieldose-dashboard-block>
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
            loading: false,
            errors: false,
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
    components: {
        'spieldose-dashboard-block': dashboardBlock
    },
    methods: {
        loadRecentAddedTracks: function () {
            this.$api.metrics.getRecentAddedTracks(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log("error loading recent added tracks metrics");
                console.error(error);
                this.loading = false;
                this.errors = true;
            });
        },
        loadRecentAddedArtists: function () {
            this.$api.metrics.getRecentAddedArtists(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log("error loading recent added artists metrics");
                console.error(error);
                this.loading = false;
                this.errors = true;
            });
        },
        loadRecentAddedAlbums: function () {
            this.$api.metrics.getRecentAddedAlbums(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log("error loading recent added albums metrics");
                console.error(error);
                this.loading = false;
                this.errors = true;
            });
        },
        loadRecentPlayedTracks: function () {
            this.$api.metrics.getRecentPlayedTracks(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log("error loading recent played tracks metrics");
                console.error(error);
                this.loading = false;
                this.errors = true;
            });
        },
        loadRecentPlayedArtists: function () {
            this.$api.metrics.getRecentPlayedArtists(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log("error loading recent played artists metrics");
                console.error(error);
                this.loading = false;
                this.errors = true;
            });
        },
        loadRecentPlayedAlbums: function () {
            this.$api.metrics.getRecentPlayedAlbums(this.interval).then(response => {
                if (response.data.metrics && response.data.metrics.length > 0) {
                    this.items = response.data.metrics;
                }
                this.loading = false;
            }).catch(error => {
                console.log("error loading recent played albums metrics");
                console.error(error);
                this.loading = false;
                this.errors = true;
            });
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
