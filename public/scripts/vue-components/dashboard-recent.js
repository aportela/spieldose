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
                    <a v-bind:class="{ 'is-active' : isTrackEntity }" v-on:click.prevent="changeEntity('tracks')">Tracks</a>
                    <a v-bind:class="{ 'is-active' : isArtistEntity }" v-on:click.prevent="changeEntity('artists')">Artists</a>
                    <a v-bind:class="{ 'is-active' : isAlbumEntity }" v-on:click.prevent="changeEntity('albums')">Albums</a>
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
    var module = Vue.component('spieldose-dashboard-recent', {
        template: template(),
        mixins: [mixinAPIError, mixinPlayer],
        data: function () {
            return ({
                loading: false,
                items: [],
                actualEntity: 'tracks'
            });
        },
        props: ['type', 'title', 'listItemCount'],
        created: function () {
            this.load();
        }, computed: {
            isTrackEntity: function () {
                return (this.actualEntity == 'tracks');
            },
            isArtistEntity: function () {
                return (this.actualEntity == 'artists');
            },
            isAlbumEntity: function () {
                return (this.actualEntity == 'albums');
            },
            hasItems: function () {
                return (this.items && this.items.length > 0);
            }
        }, methods: {
            navigateToArtistPage: function (artist) {
                if (artist) {
                    this.$router.push({ name: 'artist', params: { artist: artist } });
                }
            },
            loadRecentAddedTracks: function () {
                var self = this;
                spieldoseAPI.getRecentAddedTracks(this.interval, function (response) {
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
                var self = this;
                spieldoseAPI.getRecentAddedArtists(this.interval, function (response) {
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
                var self = this;
                spieldoseAPI.getRecentAddedAlbums(this.interval, function (response) {
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
                var self = this;
                spieldoseAPI.getRecentPlayedTracks(this.interval, function (response) {
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
                var self = this;
                spieldoseAPI.getRecentPlayedArtists(this.interval, function (response) {
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
                var self = this;
                spieldoseAPI.getRecentPlayedAlbums(this.interval, function (response) {
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
                if (entity && entity != this.actualEntity) {
                    this.actualEntity = entity;
                    this.load();
                }
            }, playTrack: function (track) {
                this.playerData.replace([track]);
            }, enqueueTrack: function (track) {
                this.playerData.enqueue([track]);
            }, playAlbum: function (album) {
                // TODO
            }, enqueueAlbum: function (album) {
                // TODO
            }
        }
    });

    return (module);
})();