var browseAlbums = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Browse albums</i></p>
        <div v-if="! errors">
            <div class="field">
                <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-focus v-model="nameFilter" type="text" placeholder="search album name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <span class="icon is-small is-left">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
            </div>
            <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-show="albums.length > 0"></spieldose-pagination>
            <div class="browse-album-item" v-for="album in albums" v-show="! loading">
                <a class="play-album" v-on:click="enqueueAlbumTracks(album.name, album.artist, album.year)" v-bind:title="'click to play album'">
                    <img class="album-thumbnail" v-if="album.image" v-bind:src="album.image"/>
                    <img class="album-thumbnail" v-else="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
                    <i class="fa fa-play fa-4x"></i>
                    <img class="vinyl no-cover" src="http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png" />
                </a>
                <div class="album-info">
                    <p class="album-name">{{ album.name }}</p>
                    <p class="artist-name"><a v-bind:title="'click to open artist section'" v-on:click.prevent="$router.push({ name: 'artist', params: { artist: album.artist } })">by {{ album.albumartist ? album.albumartist: album.artist }}</a><span v-show="album.year"> ({{ album.year }})</span></p>
                </div>
            </div>
            <div class="is-clearfix"></div>
        </div>
        <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-browse-albums', {
        template: template(),
        data: function () {
            return ({
                loading: false,
                errors: true,
                apiError: null,
                nameFilter: null,
                timeout: null,
                albums: [],
                pager: getPager(),
                playerData: sharedPlayerData,
            });
        },
        watch: {
            '$route'(to, from) {
                if (to.name == "albums" || to.name == "albumsPaged") {
                    this.pager.actualPage = parseInt(to.params.page);
                    this.search();
                }
            }
        },
        created: function () {
            var self = this;
            this.pager.refresh = function () {
                self.$router.push({ name: 'albumsPaged', params: { page: self.pager.actualPage } });
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
                self.loading = true;
                self.errors = false;
                var d = {};
                if (self.nameFilter) {
                    d.text = self.nameFilter;
                }
                spieldoseAPI.searchAlbums(self.nameFilter, self.pager.actualPage, self.pager.resultsPage, function (response) {
                    if (response.ok) {
                        self.pager.actualPage = response.body.pagination.actualPage;
                        self.pager.totalPages = response.body.pagination.totalPages;
                        self.pager.totalResults = response.body.pagination.totalResults;
                        if (response.body.albums && response.body.albums.length > 0) {
                            self.albums = response.body.albums;
                        } else {
                            self.albums = [];
                        }
                        self.loading = false;
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                        self.loading = false;
                    }
                });
            },
            enqueueAlbumTracks: function (album, artist, year) {
                var self = this;
                spieldoseAPI.getAlbumTracks(album || null, artist || null, year || null, function (response) {
                    self.playerData.emptyPlayList();
                    if (response.ok) {
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.playerData.unsetCurrentPlayList();
                            self.playerData.tracks = response.body.tracks;
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

    return (module);
})();