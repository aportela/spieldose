var browsePaths = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Browse paths</i></p>
        <div v-if="! errors">
            <div class="field">
                <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-focus v-model.trim="nameFilter" type="text" placeholder="search path name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <span class="icon is-small is-left">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
            </div>
            <nav class="breadcrumb has-arrow-separator" aria-label="breadcrumbs">
                <ul>
                    <li><a href="#"><span>Current path:</span></a></li>
                    <li v-for="item in breadcrumbPathItems"><a href="#"><span class="icon is-small"><i class="fa fa-folder-open"></i></span><span>{{ item }}</span></a></li>
                </ul>
            </nav>
            <div class="path-item box has-text-centered" v-for="item in currentPathFolders" v-show="! loading">
                <p class="path-item-icon">
                    <span class="icon has-text-light">
                        <i class="fa fa-folder fa-5x"></i>
                    </span>
                </p>
                <p class="path-info">
                    <strong>“{{ item.path }}”</strong>
                </p>
                <p class="content is-small">{{ item.totalTracks }} tracks</p>
                <div class="field has-addons">
                    <p class="control">
                        <a class="button is-small is-link">
                            <span class="icon is-small"><i class="fa fa-play"></i></span>
                            <span>play</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-small is-info">
                            <span class="icon is-small"><i class="fa fa-plus-square"></i></span>
                            <span>enqueue</span>
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

    var module = Vue.component('spieldose-browse-paths', {
        template: template(),
        data: function () {
            return ({
                loading: false,
                errors: false,
                apiError: null,
                nameFilter: null,
                timeout: null,
                breadcrumbPathItems: ["m:\\", "one", "two", "three"],
                currentPathFolders: [ ],
                pager: getPager(),
                playerData: sharedPlayerData
            });
        }, directives: {
            focus: {
                update: function (el) {
                    el.focus();
                }
            }
        }, created: function() {
            var self = this;
            spieldoseAPI.searchPaths("m:\\GAMES", function(response) {
                if (response.ok) {
                    self.currentPathFolders = response.body.paths;
                } else {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                    self.loading = false;
                }
            });
        }, methods: {
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
            },
            removePlayList: function(playListId) {
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
    });

    return (module);
})();