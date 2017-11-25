var browseArtist = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p v-if="loading" class="title is-1 has-text-centered">Loading <i v-if="loading" class="fa fa-cog fa-spin fa-fw"></i></p>
        <p v-else="! loading" class="title is-1 has-text-centered">Artist details</p>
        <div class="media" v-if="! errors && ! loading">
            <figure class="image media-left">
                <img v-bind:src="artist.image" alt="Image" class="artist_avatar" v-if="artist.image">
                <img alt="Image" class="artist_avatar" src="https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png" v-else />
            </figure>
            <div class="media-content is-light">
                <p class="title is-1">{{ artist.name }}</p>
                <p class="subtitle is-6" v-if="artist.playCount > 0">{{ artist.playCount }} plays</p>
                <p class="subtitle is-6" v-else>not played yet</p>
                <div class="tabs is-medium">
                    <ul>
                        <li v-bind:class="{ 'is-active' : activeTab == 'overview' }"><a v-on:click.prevent="$router.push({ name: 'artist', params: { 'artist': $route.params.artist } })">Overview</a></li>
                        <li v-bind:class="{ 'is-active' : activeTab == 'bio' }"><a v-on:click.prevent="$router.push({ name: 'artistBio' })">Bio</a></li>
                        <li v-bind:class="{ 'is-active' : activeTab == 'tracks' }"><a v-on:click.prevent="$router.push({ name: 'artistTracks' })">Tracks</a></li>
                        <li v-bind:class="{ 'is-active' : activeTab == 'albums' }"><a v-on:click.prevent="$router.push({ name: 'artistAlbums' })">Albums</a></li>
                    </ul>
                </div>
                <div class="panel" v-if="activeTab == 'overview'">
                    <div class="content is-clearfix" id="bio" v-if="artist.bio" v-html="truncatedBio"></div>
                    <div class="columns">
                        <div class="column is-half is-full-mobile">
                            <spieldose-dashboard-toplist v-if="activeTab == 'overview' && artist.name" v-bind:type="'topTracks'" v-bind:title="'Top played tracks'" v-bind:listItemCount="10" v-bind:showPlayCount="true" :key="$route.params.artist" v-bind:artist="$route.params.artist"></spieldose-dashboard-toplist>
                        </div>
                    </div>
                </div>
                <div class="panel" v-if="activeTab == 'bio'">
                    <div class="content is-clearfix" id="bio" v-html="artist.bio"></div>
                </div>
                <div class="panel" v-if="activeTab == 'tracks'">
                    <div class="field">
                        <div class="control has-icons-left" v-bind:class="loadingTracks ? 'is-loading': ''">
                            <input class="input" :disabled="loadingTracks" v-focus v-model.trim="nameFilter" type="text" placeholder="search by text..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                            <span class="icon is-small is-left">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                    </div>
                    <spieldose-pagination v-bind:loading="loadingTracks" v-bind:data="pager"></spieldose-pagination>
                    <table class="table is-bordered is-striped is-narrow is-fullwidth">
                        <thead>
                                <tr class="is-unselectable">
                                    <th>Album</th>
                                    <th>Year</th>
                                    <th>Number</th>
                                    <th>Track</th>
                                    <th>Actions</th>
                                </tr>
                        </thead>
                        <tbody>
                            <tr v-for="track, i in tracks">
                                <td><span>{{ track.album }}</span></td>
                                <td><span>{{ track.year }}</span></td>
                                <td>{{ track.number }}</td>
                                <td>
                                    <span> {{ track.title}}</span>
                                </td>
                                <td>
                                    <i v-on:click.prevent="playerData.replace([track]);" class="cursor-pointer fa fa-play" title="play this track"></i>
                                    <i v-on:click.prevent="playerData.enqueue([track]);" class="cursor-pointer fa fa-plus-square" title="enqueue this track"></i>
                                    <i v-on:click.prevent="playerData.download(track.id);" class="cursor-pointer fa fa-save" title="download this track"></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="panel" v-if="activeTab == 'albums'">
                    <div class="browse-album-item" v-for="album in artist.albums" v-show="! loading">
                        <a class="play-album" v-on:click="enqueueAlbumTracks(album.name, album.artist, album.year)" v-bind:title="'click to play album'">
                            <img class="album-thumbnail" v-if="album.image" v-bind:src="album.image"/>
                            <img class="album-thumbnail" v-else="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
                            <i class="fa fa-play fa-4x"></i>
                            <img class="vinyl no-cover" src="http://fc08.deviantart.net/fs17/f/2007/170/9/8/Vinyl_Disc_Icon_Updated_by_jordygreen.png" />
                        </a>
                        <div class="album-info">
                            <p class="album-name">{{ album.name }}</p>
                            <p class="album-year" v-show="album.year">({{ album.year }})</p>
                        </div>
                    </div>
                    <div class="is-clearfix"></div>
                </div>
            </div>
        </div>
        <spieldose-api-error-component v-if="errors" v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-browse-artist', {
        template: template(),
        data: function () {
            return ({
                loading: false,
                loadingTracks: false,
                errors: false,
                apiError: null,
                artist: {},
                activeTab: 'overview',
                truncatedBio: null,
                detailedView: false,
                playerData: sharedPlayerData,
                pager: getPager(),
                tracks: [],
                nameFilter: null,
                timeout: null
            });
        },
        watch: {
            '$route'(to, from) {
                switch(to.name) {
                    case "artistBio":
                        this.activeTab = "bio";
                    break;
                    case "artistTracks":
                    case "artistTracksPaged":
                        this.pager.actualPage = parseInt(to.params.page);
                        this.searchArtistTracks(to.params.artist);
                        this.activeTab = "tracks";
                    break;
                    case "artistAlbums":
                        this.activeTab = "albums";
                    break;
                    default:
                        this.activeTab = "overview";
                    break;
                }
            }
        }
        , created: function () {
            this.getArtist(this.$route.params.artist);
            if (this.$route.name == "artistTracks" || this.$route.name == "artistTracksPaged") {
                var self = this;
                this.pager.refresh = function () {
                    self.$router.push({ name: 'artistTracksPaged', params: { page: self.pager.actualPage } });
                }
                if (this.$route.params.page) {
                    this.pager.actualPage = parseInt(this.$route.params.page);
                }
                this.searchArtistTracks(this.$route.params.artist);
                this.activeTab = "tracks";
            } else {
                switch(this.$route.name) {
                    case "artistBio":
                        this.activeTab = "bio";
                    break;
                    case "artistAlbums":
                        this.activeTab = "albums";
                    break;
                    default:
                        this.activeTab = "overview";
                    break;
                }
            }
        }, directives: {
            focus: {
                update: function (el) {
                    el.focus();
                }
            }
        }, methods: {
            getArtist: function (artist) {
                var self = this;
                self.loading = true;
                self.errors = false;
                var d = {};
                spieldoseAPI.getArtist(artist, function (response) {
                    if (response.ok) {
                        self.artist = response.body.artist;
                        if (self.artist.bio) {
                            self.artist.bio = self.artist.bio.replace(/(?:\r\n|\r|\n)/g, '<br />');
                            self.truncatedBio = self.truncate(self.artist.bio);
                            //self.activeTab = "overview";
                        }
                        self.loading = false;
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                        self.loading = false;
                    }
                });
            },
            abortInstantSearch: function () {
                this.nameFilter = null;
                clearTimeout(this.timeout);
            },
            instantSearch: function () {
                var self = this;
                if (self.timeout) {
                    clearTimeout(self.timeout);
                }
                self.timeout = setTimeout(function () {
                    self.pager.actualPage = 1;
                    self.searchArtistTracks(self.$route.params.artist);
                }, 256);
            },
            searchArtistTracks: function (artist) {
                var self = this;
                self.loadingTracks = true;
                self.errors = false;
                var text = this.nameFilter ? this.nameFilter : "";
                spieldoseAPI.searchTracks(text, artist, text, self.pager.actualPage, self.pager.resultsPage, "", function (response) {
                    if (response.ok) {
                        self.pager.actualPage = response.body.actualPage;
                        self.pager.totalPages = response.body.totalPages;
                        self.pager.totalResults = response.body.totalResults;
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.tracks = response.body.tracks;
                        } else {
                            self.tracks = [];
                        }
                        self.loadingTracks = false;
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                        self.loadingTracks = false;
                    }
                });
            },
            changeTab: function (tab) {
                this.activeTab = tab;
            }, truncate: function (text) {
                return (text.substring(0, 500));
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
            }
        }
    });

    return (module);
})();