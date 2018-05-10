let dashboardRecent = (function () {
    "use strict";

    const template = function () {
        return `
            <section class="panel chart">
                <p class="panel-heading">
                    <span class="icon">
                        <i class="fas fa-cog fa-spin fa-fw" v-if="loading"></i>
                        <i class="fas fa-exclamation-triangle" v-else-if="hasAPIErrors"></i>
                        <i class="far fa-clock" v-else></i>
                    </span>
                    <span>{{ title }}</span>
                    <a class="icon is-pulled-right" title="refresh data" v-on:click.prevent="load();"><i class="fas fa-redo fa-fw"></i></a>
                </p>
                <p class="panel-tabs">
                    <a v-bind:class="{ 'is-active' : isTrackEntity }" v-on:click.prevent="changeEntity('tracks');">Tracks</a>
                    <a v-bind:class="{ 'is-active' : isArtistEntity }" v-on:click.prevent="changeEntity('artists');">Artists</a>
                    <a v-bind:class="{ 'is-active' : isAlbumEntity }" v-on:click.prevent="changeEntity('albums');">Albums</a>
                </p>
                <div class="panel-block cut-text">
                    <ol v-if="hasItems">
                        <li class="is-small" v-if="isTrackEntity" v-for="item in items" v-bind:key="item.id">
                            <i class="cursor-pointer fa fa-play" title="play this track" v-on:click="playTrack(item);"></i>
                            <i class="cursor-pointer fa fa-plus-square" title="enqueue this track" v-on:click="enqueueTrack(item);"></i>
                            <span>{{ item.title }}</span>
                            <span v-if="item.artist"> / <a v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a></span>
                        </li>
                        <li class="is-small" v-if="isArtistEntity" v-for="item in items">
                            <a v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a>
                        </li>
                        <li class="is-small" v-if="isAlbumEntity" v-for="item in items">
                            <i class="cursor-pointer fa fa-play" title="play this album" v-on:click="playAlbum(item);" ></i>
                            <i class="cursor-pointer fa fa-plus-square" title="enqueue this album" v-on:click="enqueueAlbum(item);"></i>
                            <span>{{ item.album }}</span>
                            <span v-if="item.artist"> / <a v-on:click.prevent="navigateToArtistPage(item.artist);">{{ item.artist }}</a></span>
                        </li>
                    </ol>
                    <p v-else-if="! hasItems && ! loading && ! hasAPIErrors">not enough data for the stats</p>
                    <p v-else-if="hasAPIErrors">error loading data (invalid response from server)</p>
                </div>
            </section>
        `;
    };

    /* recent played (track/artist/album) component */
    let module = Vue.component('spieldose-dashboard-recent', {
        template: template(),
        mixins: [
            mixinAPIError, mixinTopRecentCharts, mixinNavigation, mixinPlayer
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
        }, methods: {
            loadRecentAddedTracks: function () {
                let self = this;
                spieldoseAPI.metrics.getRecentAddedTracks(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            loadRecentAddedArtists: function () {
                let self = this;
                spieldoseAPI.metrics.getRecentAddedArtists(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            loadRecentAddedAlbums: function () {
                let self = this;
                spieldoseAPI.metrics.getRecentAddedAlbums(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            loadRecentPlayedTracks: function () {
                let self = this;
                spieldoseAPI.metrics.getRecentPlayedTracks(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            loadRecentPlayedArtists: function () {
                let self = this;
                spieldoseAPI.metrics.getRecentPlayedArtists(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            loadRecentPlayedAlbums: function () {
                let self = this;
                spieldoseAPI.metrics.getRecentPlayedAlbums(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            load: function () {
                this.clearAPIErrors();
                this.loading = true;
                this.items = [];
                switch (this.type) {
                    case "recentlyAdded":
                        this.loadRecentAdded();
                        break;
                    case "recentlyPlayed":
                        this.loadRecentPlayed();
                        break;
                    default:
                        this.loading = false;
                        break;
                }
            },
            loadRecentAdded: function () {
                switch (this.actualEntity) {
                    case "tracks":
                        this.loadRecentAddedTracks();
                        break;
                    case "artists":
                        this.loadRecentAddedArtists();
                        break;
                    case "albums":
                        this.loadRecentAddedAlbums();
                        break;
                    default:
                        this.loading = false;
                        break;
                }
            },
            loadRecentPlayed: function () {
                switch (this.actualEntity) {
                    case "tracks":
                        this.loadRecentPlayedTracks();
                        break;
                    case "artists":
                        this.loadRecentPlayedArtists();
                        break;
                    case "albums":
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
    });

    return (module);
})();