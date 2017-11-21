var dashboardRecent = (function () {
    "use strict";

    var template = function () {
        return `
    <section class="panel chart">
        <p class="panel-heading"><span class="icon"><i v-if="loading" class="fa fa-cog fa-spin fa-fw"></i><i v-else-if="errors" class="fa fa-cog fa-exclamation-triangle"></i><i v-else class="fa fa-clock-o"></i></span> {{ title }}</p>
        <p class="panel-tabs">
            <a v-bind:class="{ 'is-active' : entity == 'tracks' }" v-on:click.prevent="changeEntity('tracks')" href="#">Tracks</a>
            <a v-bind:class="{ 'is-active' : entity == 'artists' }" v-on:click.prevent="changeEntity('artists')" href="#">Artists</a>
            <a v-bind:class="{ 'is-active' : entity == 'albums' }" v-on:click.prevent="changeEntity('albums')" href="#">Albums</a>
        </p>
        <div class="panel-block cut-text">
            <ol v-if="items.length > 0">
                <li class="is-small" v-if="entity == 'tracks'" v-for="item, i in items"><i v-on:click="playTrack(item);" class="cursor-pointer fa fa-play" title="play this track"></i> <i v-on:click="enqueueTrack(item);" class="cursor-pointer fa fa-cog fa-plus-square" title="enqueue this track"></i> {{ item.title }}<span v-if="item.artist"> / <a v-on:click.prevent="$router.push({ name: 'artist', params: { artist: item.artist } })">{{ item.artist }}</a></span></li>
                <li class="is-small" v-if="entity == 'artists'" v-for="item, i in items"><a v-on:click.prevent="$router.push({ name: 'artist', params: { artist: item.artist } })">{{ item.artist }}</a></li>
                <li class="is-small" v-if="entity == 'albums'" v-for="item, i in items"><i v-on:click="playAlbum(item);" class="cursor-pointer fa fa-play" title="play this album"></i> <i v-on:click="enqueueAlbum(item);" class="cursor-pointer fa fa-cog fa-plus-square" title="enqueue this album"></i> {{ item.album }}<span v-if="item.artist"> / <a v-on:click.prevent="$router.push({ name: 'artist', params: { artist: item.artist } })">{{ item.artist }}</a></span></li>
            </ol>
            <p v-else-if="items.length == 0 && ! loading && ! errors">not enough data for the stats</p>
            <p v-else-if="errors">error loading data (invalid response from server)</p>
        </div>
    </section>
    `;
    };

    /* app chart (test) component */
    var module = Vue.component('spieldose-dashboard-recent', {
        template: template(),
        data: function () {
            return ({
                loading: false,
                errors: false,
                entity: 'tracks',
                items: [],
                playerData: sharedPlayerData,
            });
        },
        created: function () {
            this.load();
        }, methods: {
            loadRecentAddedTracks: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                self.items = [];
                spieldoseAPI.getRecentAddedTracks(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                        self.loading = false;
                    } else {
                        self.loading = false;
                        self.errors = false;
                    }
                });
            },
            loadRecentAddedArtists: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                self.items = [];
                spieldoseAPI.getRecentAddedArtists(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                        self.loading = false;
                    } else {
                        self.loading = false;
                        self.errors = false;
                    }
                });
            },
            loadRecentAddedAlbums: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                self.items = [];
                spieldoseAPI.getRecentAddedAlbums(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                        self.loading = false;
                    } else {
                        self.loading = false;
                        self.errors = false;
                    }
                });
            },
            loadRecentPlayedTracks: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                self.items = [];
                spieldoseAPI.getRecentPlayedTracks(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                        self.loading = false;
                    } else {
                        self.loading = false;
                        self.errors = false;
                    }
                });
            },
            loadRecentPlayedArtists: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                self.items = [];
                spieldoseAPI.getRecentPlayedArtists(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                        self.loading = false;
                    } else {
                        self.loading = false;
                        self.errors = false;
                    }
                });
            },
            loadRecentPlayedAlbums: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                self.items = [];
                spieldoseAPI.getRecentPlayedAlbums(this.interval, function (response) {
                    if (response.ok) {
                        if (response.body.metrics && response.body.metrics.length > 0) {
                            self.items = response.body.metrics;
                        }
                        self.loading = false;
                    } else {
                        self.loading = false;
                        self.errors = false;
                    }
                });
            },
            load: function () {
                switch (this.type) {
                    case "recentlyAdded":
                        this.loadRecentAdded();
                        break;
                    case "recentlyPlayed": {
                        this.loadRecentPlayed();
                    }
                }
            },
            loadRecentAdded: function () {
                switch (this.entity) {
                    case "tracks":
                        this.loadRecentAddedTracks();

                        break;
                    case "artists":
                        this.loadRecentAddedArtists();
                        break;
                    case "albums":
                        this.loadRecentAddedAlbums();
                        break;
                }
            },
            loadRecentPlayed: function () {
                switch (this.entity) {
                    case "tracks":
                        this.loadRecentPlayedTracks();
                        break;
                    case "artists":
                        this.loadRecentPlayedArtists();
                        break;
                    case "albums":
                        this.loadRecentPlayedAlbums();
                        break;
                }
            },
            changeEntity: function (e) {
                if (e && e != this.entity) {
                    this.entity = e;
                    this.load();
                }
            }, playTrack: function (track) {
                this.playerData.replace([track]);
            }, enqueueTrack: function (track) {
                this.playerData.enqueue([track]);
            }, playAlbum: function (album) {
            }, enqueueAlbum: function (album) {
            }
        },
        props: ['type', 'title', 'listItemCount']
    });

    return (module);
})();