var browseAlbums = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Browse albums</i></p>
        <div v-if="! errors">
            <div class="field has-addons">
                <div class="control is-expanded has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-model.trim="nameFilter" type="text" placeholder="search album name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <span class="icon is-small is-left">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
                <p class="control">
                    <a class="button is-info" v-on:click.prevent="advancedSearch = ! advancedSearch;">
                        <span class="icon">
                            <i v-if="advancedSearch" class="fa fa-search-minus" aria-hidden="true"></i>
                            <i v-else="advancedSearch" class="fa fa-search-plus" aria-hidden="true"></i>
                        </span>
                        <span>toggle advanced search</span>
                    </a>
                </p>
            </div>
            <div class="field has-addons" v-if="advancedSearch">
                <p class="control has-icons-left">
                    <input v-model.number="filterByYear" class="input" :disabled="loading" type="text" pattern="[0-9]*" placeholder="year (4 digits)" maxlength="4">
                    <span class="icon is-small is-left">
                        <i class="fa fa-calendar"></i>
                    </span>
                </p>
                <p class="control is-expanded has-icons-left">
                    <input v-model.trim="filterByArtist" class="input" :disabled="loading" type="text" placeholder="search album artist name...">
                    <span class="icon is-small is-left">
                        <i class="fa fa-user"></i>
                    </span>
                </p>
                <p class="control">
                    <a class="button is-info" v-on:click="search();">
                        <span class="icon">
                            <i class="fa fa-search" aria-hidden="true"></i>
                        </span>
                        <span>search</span>
                    </a>
                </p>
            </div>
            <spieldose-pagination v-bind:loading="loading" v-bind:data="pager"></spieldose-pagination>
            <!--
                Vinyl disc icon credits: Jordan Green (http://www.jordangreenphoto.com/)
                https://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
            -->
            <div class="browse-album-item" v-for="album in albums" v-show="! loading">
                <a class="play-album" v-on:click.prevent="enqueueAlbumTracks(album.name, album.artist, album.year)" v-bind:title="'click to play album'">
                    <img class="album-thumbnail" v-if="album.image" v-bind:src="'api/thumbnail?url=' + album.image"/>
                    <img class="album-thumbnail" v-else="" src="images/image-album-not-set.png"/>
                    <i class="fa fa-play fa-4x"></i>
                    <img class="vinyl no-cover" src="images/vinyl.png" />
                </a>
                <div class="album-info">
                    <p class="album-name">{{ album.name }}</p>
                    <p v-if="album.artist" class="artist-name"><a v-bind:title="'click to open artist section'" v-on:click.prevent="$router.push({ name: 'artist', params: { artist: album.artist } })">by {{ album.artist }}</a><span v-show="album.year"> ({{ album.year }})</span></p>
                    <p v-else class="artist-name">unknown artist</p>
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
                errors: false,
                apiError: null,
                nameFilter: null,
                timeout: null,
                albums: [],
                pager: getPager(),
                advancedSearch: false,
                playerData: sharedPlayerData,
                filterByArtist: null,
                filterByYear: null
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
        }, methods: {
            abortInstantSearch: function () {
                this.nameFilter = null;
            },
            instantSearch: function () {
                var self = this;
                if (self.timeout) {
                    clearTimeout(self.timeout);
                }
                if (! this.advancedSearch) {
                    self.timeout = setTimeout(function () {
                        self.pager.actualPage = 1;
                        self.search();
                    }, 256);
                }
            },
            search: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                spieldoseAPI.searchAlbums(self.nameFilter, self.filterByArtist, self.filterByYear, self.pager.actualPage, self.pager.resultsPage, function (response) {
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