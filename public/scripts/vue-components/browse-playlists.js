var browsePlaylists = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Browse playlists</i></p>
        <div v-if="! errors">
            <div class="field">
                <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-focus v-model="nameFilter" type="text" placeholder="search playlist name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <span class="icon is-small is-left">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
            </div>
            <spieldose-pagination v-bind:data="pager" v-show="playlists.length > 0"></spieldose-pagination>
            <div class="playlist-item box" v-for="playlist in playlists" v-show="! loading" v-on:click.prevent="loadPlayList(playlist.id);">
                <p class="playlist-info has-text-centered">
                    <strong>“{{ playlist.name }}”</strong>
                    <br>13 tracks
                </p>
            </div>
            <div class="is-clearfix"></div>
        </div>
        <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-browse-playlists', {
        template: template(),
        data: function () {
            return ({
                loading: false,
                errors: false,
                apiError: null,
                nameFilter: null,
                timeout: null,
                playlists: [],
                pager: getPager(),
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
        },
        created: function () {
            var self = this;
            this.pager.refresh = function () {
                self.$router.push({ name: 'playlistsPaged', params: { page: self.pager.actualPage } });
            }
            if (this.$route.params.page) {
                self.pager.actualPage = parseInt(this.$route.params.page);
            }
            this.search();
        }, directives: {
            focus: {
                update: function (el) {
                    el.focus();
                }
            }
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
                        self.currentPlaylistId = playListId;
                        self.currentPlaylistName = response.body.playlist.name;
                        self.playerData.replace(response.body.playlist.tracks);
                        self.$router.push({ name: 'nowPlaying' });
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