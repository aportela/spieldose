var browsePlaylists = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box is-marginless">
        <p class="title is-1 has-text-centered">Browse playlists</i></p>
        <div v-if="! errors">
            <div class="field has-addons">
                <div class="control is-expanded has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-if="liveSearch" v-model.trim="nameFilter" type="text" placeholder="search playlist name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <input class="input" :disabled="loading" v-else v-model.trim="nameFilter" type="text" placeholder="search playlist name..." v-on:keyup.enter="search();">
                    <span class="icon is-small is-left">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                <p class="control" v-if="! liveSearch">
                    <a class="button is-info" v-on:click.prevent="search();">
                        <span class="icon">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </span>
                        <span>search</span>
                    </a>
                </p>
            </div>
            <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
            <div class="playlist-item box has-text-centered" v-for="playlist in playlists" v-show="! loading">
                <p class="playlist-item-icon">
                    <span class="icon has-text-light">
                        <i class="fas fa-list-alt fa-5x"></i>
                    </span>
                </p>
                <p class="playlist-info">
                    <strong>“{{ playlist.name }}”</strong>
                </p>
                <p class="content is-small">{{ playlist.trackCount }} tracks</p>
                <div class="field has-addons">
                    <p class="control">
                        <a class="button is-small is-link" v-on:click.prevent="loadPlayList(playlist.id);">
                            <span class="icon is-small"><i class="fas fa-play"></i></span>
                            <span>play</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-small is-danger" v-bind:disabled="! playlist.id" v-on:click.prevent="removePlayList(playlist.id);">
                            <span class="icon is-small"><i class="fas fa-times"></i></span>
                            <span>remove</span>
                        </a>
                    </p>
                </div>
            </div>
            <div class="is-clearfix"></div>
        </div>
        <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-browse-playlists', {
        template: template(),
        mixins: [mixinPagination, mixinLiveSearches],
        data: function () {
            return ({
                loading: false,
                errors: false,
                apiError: null,
                nameFilter: null,
                timeout: null,
                playlists: [],
                playerData: sharedPlayerData
            });
        },
        watch: {
            '$route'(to, from) {
                if (to.name == "playlists" || to.name == "playlistsPaged") {
                    this.pager.actualPage = parseInt(to.params.page);
                    this.search();
                }
            }
        }, methods: {
            onPaginationChanged: function(currentPage) {
                this.$router.push({ name: 'playlistsPaged', params: { page: currentPage } });
            },
            abortInstantSearch: function () {
                this.nameFilter = null;
            },
            instantSearch: function () {
                var self = this;
                if (self.timeout) {
                    clearTimeout(self.timeout);
                }
                self.timeout = setTimeout(function () {
                    self.pager.actualPage = 1;
                    self.search();
                }, 256);
            },
            search: function () {
                var self = this;
                this.loading = true;
                spieldoseAPI.searchPlaylists(self.nameFilter, self.pager.actualPage, self.pager.resultsPage, function (response) {
                    if (response.ok) {
                        self.pager.actualPage = response.body.pagination.actualPage;
                        self.pager.totalPages = response.body.pagination.totalPages;
                        self.pager.totalResults = response.body.pagination.totalResults;
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
            loadPlayList: function (playListId) {
                var self = this;
                if (playListId) {
                    spieldoseAPI.getPlayList(playListId, function (response) {
                        if (response.ok) {
                            self.playerData.replace(response.body.playlist.tracks);
                            self.playerData.setCurrentPlayList(playListId, response.body.playlist.name);
                            self.$router.push({ name: 'nowPlaying' });
                        } else {
                            self.errors = true;
                            self.apiError = response.getApiErrorData();
                            self.loading = false;
                        }
                    });
                } else {
                    spieldoseAPI.searchTracks("", "", "", true, 1, 1024, "random", function (response) {
                        if (response.ok) {
                            self.playerData.replace(response.body.tracks);
                            self.$router.push({ name: 'nowPlaying' });
                        } else {
                            self.errors = true;
                            self.apiError = response.getApiErrorData();
                            self.loading = false;
                        }
                    });
                }
            },
            removePlayList: function (playListId) {
                if (playListId) {
                    var self = this;
                    spieldoseAPI.removePlaylist(playListId, function (response) {
                        if (response.ok) {
                            if (self.playerData.currentPlaylistId == playListId) {
                                self.playerData.unsetCurrentPlayList();

                            }
                            self.search();
                        } else {
                            self.errors = true;
                            self.apiError = response.getApiErrorData();
                            self.loading = false;
                        }
                    });
                }
            }
        }
    });

    return (module);
})();