"use strict";

var vTemplateSearch = function () {
    return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Search artists, albums, tracks, playlists</p>
        <div v-if="! errors">
            <div class="field">
                <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-focus v-model="textFilter" type="text" placeholder="search..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <span class="icon is-small is-left">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
            </div>
            <div class="columns">
                <div class="column is-one-quarter is-clipped">
                    <h1 class="title is-6 has-text-centered">Artists</h1>
                    <hr class="dropdown-divider">
                    <article class="media" v-for="item, i in artists">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <img class="border-radius-50" v-if="item.image" v-bind:src="item.image" />
                                <img class="border-radius-50" v-else src="https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png" />
                            </figure>
                        </div>
                        <div class="media-content">
                            <div class="content">
                                <p class="subtitle is-6"><a v-bind:title="'click to open artist section'" v-bind:href="'/#/app/artist/' + $router.encodeSafeName(item.name)">{{ item.name }}</a></p>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="column is-one-quarter is-clipped">
                    <h1 class="title is-6 has-text-centered">Albums</h1>
                    <hr class="dropdown-divider">
                    <article class="media" v-for="item, i in albums">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <img class="border-radius-50" v-if="item.image" v-bind:src="item.image"/>
                                <img class="border-radius-50" v-else="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
                            </figure>
                        </div>
                        <div class="media-content">
                            <div class="content cut-text">
                                <p class="subtitle is-6"><a v-on:click="enqueueAlbumTracks(item.name, item.artist, item.year);" v-bind:title="'click to enqueue album'">{{ item.name }}</a></p>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="column is-one-quarter is-clipped">
                    <h1 class="title is-6 has-text-centered">Tracks</h1>
                    <hr class="dropdown-divider">
                    <article class="media" v-for="item, i in tracks">
                        <div class="media-left">
                        </div>
                        <div class="media-content">
                            <div class="content cut-text">
                                <a><i v-on:click="playTrack(item);" class="cursor-pointer fa fa-play" title="play this track"></i> <i v-on:click="enqueueTrack(item);" class="cursor-pointer fa fa-cog fa-plus-square" title="enqueue this track"></i> {{ item.title }}</a>
                                <br >
                                <span v-if="item.artist">by <a v-bind:href="'/#/app/artist/' + $router.encodeSafeName(item.artist)">{{ item.artist }} <span v-if="item.album"> / {{ item.album }}</span></a></span>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="column is-one-quarter is-clipped">
                    <h1 class="title is-6 has-text-centered">Playlists</h1>
                    <hr class="dropdown-divider">
                    <article class="media" v-for="item, i in playlists">
                        <div class="media-left">
                        </div>
                        <div class="media-content">
                            <div class="content cut-text">
                                <p class="subtitle is-6">
                                <i v-on:click="playPlaylist(item);" class="cursor-pointer fa fa-play" title="play this playlist"></i> <i v-on:click="enqueuePlaylist(item);" class="cursor-pointer fa fa-cog fa-plus-square" title="enqueue this playlist"></i>
                                {{ item.name }} ({{ item.trackCount}} tracks)
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
        <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
}

var search = Vue.component('spieldose-search', {
    template: vTemplateSearch(),
    data: function () {
        return ({
            loading: false,
            errors: false,
            apiError: null,
            textFilter: null,
            timeout: null,
            artists: [],
            albums: [],
            tracks: [],
            playlists: [],
            playerData: sharedPlayerData,
        });
    }, directives: {
        focus: {
            update: function (el) {
                el.focus();
            }
        }
    }, methods: {
        abortInstantSearch: function () {
            this.textFilter = null;
            this.artists = [];
            this.albums = [];
            this.tracks = [];
            this.playlists = [];
        },
        instantSearch: function () {
            if (this.textFilter) {
                var self = this;
                if (self.timeout) {
                    clearTimeout(self.timeout);
                }
                self.timeout = setTimeout(function () {
                    self.search();
                }, 256);
            } else {
                this.abortInstantSearch();
            }
        },
        search: function () {
            var self = this;
            self.loading = true;
            self.errors = false;
            spieldoseAPI.globalSearch(self.textFilter, 1, 8, function (response) {
                if (response.ok) {
                    if (response.body.artists && response.body.artists.length > 0) {
                        self.artists = response.body.artists;
                    } else {
                        self.artists = [];
                    }
                    if (response.body.albums && response.body.albums.length > 0) {
                        self.albums = response.body.albums;
                    } else {
                        self.albums = [];
                    }
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        self.tracks = response.body.tracks;
                    } else {
                        self.tracks = [];
                    }
                    if (response.body.playlists && response.body.playlists.length > 0) {
                        self.playlists = response.body.playlists;
                    } else {
                        self.playlists = [];
                    }
                    self.loading = false;
                } else {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                    self.loading = false;
                }
            });
        },
        highlight: function (text) {
            if (text && this.textFilter) {
                return text.replace(new RegExp("(" + this.textFilter + ")", 'gi'), '<span class="highlight">$1</span>');
            } else {
                return (null);
            }
        },
        enqueueAlbumTracks: function (album, artist, year) {
            var self = this;
            spieldoseAPI.getAlbumTracks(album || null, artist || null, year || null, function (response) {
                self.playerData.emptyPlayList();
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        self.playerData.tracks = response.body.tracks;
                        self.playerData.play();
                    }
                } else {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                }
            });
        }, playTrack: function (track) {
            this.playerData.replace([track]);
        }, enqueueTrack: function (track) {
            this.playerData.enqueue([track]);
        },
        playPlaylist: function(playlist) {
            var self = this;
            spieldoseAPI.getPlayList(playlist, function (response) {
                self.playerData.emptyPlayList();
                if (response.ok) {
                    if (response.body.playlist.tracks && response.body.playlist.tracks.length > 0) {
                        self.playerData.tracks = response.body.playlist.tracks;
                        self.playerData.play();
                    }
                } else {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                }
            });
        },
        enqueuePlaylist: function(playlist) {
            var self = this;
            spieldoseAPI.getPlayList(playlist, function (response) {
                if (response.ok) {
                    if (response.body.playlist.tracks && response.body.playlist.tracks.length > 0) {
                        for (var i = 0; i < response.body.playlist.tracks.length; i++) {
                            self.playerData.tracks.push(response.body.playlist.tracks[i]);
                        }
                        self.playerData.play();
                    }
                } else {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                }
            });
        }
    }
});