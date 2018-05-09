let browseArtist = (function () {
    "use strict";

    const template = function () {
        return `
            <div class="container is-fluid box is-marginless">
                <p v-if="loading" class="title is-1 has-text-centered">Loading <i v-if="loading" class="fas fa-cog fa-spin fa-fw"></i></p>
                <p v-else="! loading" class="title is-1 has-text-centered">Artist details</p>
                <div class="media" v-if="! hasAPIErrors && ! loading">
                    <figure class="image media-left">
                        <img class="artist_avatar" v-bind:src="artist.image | getArtistImageUrl" v-on:error="artist.image = null;">
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
                                <li v-bind:class="{ 'is-active' : activeTab == 'update' }"><a v-on:click.prevent="$router.push({ name: 'artistUpdate' })">Update artist</a></li>
                            </ul>
                        </div>
                        <div class="panel" v-if="activeTab == 'overview'">
                            <div class="content is-clearfix" id="bio" v-if="artist.bio" v-html="truncatedBio"></div>
                            <div class="columns">
                                <div class="column is-half is-full-mobile">
                                    <spieldose-dashboard-toplist v-if="activeTab == 'overview' && artist.name" v-bind:type="'topTracks'" title="Top played tracks" v-bind:listItemCount="10" v-bind:showPlayCount="true" :key="$route.params.artist" v-bind:artist="$route.params.artist"></spieldose-dashboard-toplist>
                                </div>
                            </div>
                        </div>
                        <div class="panel" v-if="activeTab == 'bio'">
                            <div class="content is-clearfix" id="bio" v-html="artist.bio"></div>
                        </div>
                        <div class="panel" v-if="activeTab == 'tracks'">
                            <div class="field has-addons">
                                <div class="control is-expanded has-icons-left" v-bind:class="loadingTracks ? 'is-loading': ''">
                                    <input class="input" :disabled="loadingTracks" v-if="liveSearch" v-model.trim="nameFilter" type="text" placeholder="search by text..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                                    <input class="input" :disabled="loadingTracks" v-else v-model.trim="nameFilter" type="text" placeholder="search by text..." v-on:keyup.enter="searchTracks();">
                                    <span class="icon is-small is-left">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <p class="control" v-if="! liveSearch">
                                    <a class="button is-info" v-on:click.prevent="searchTracks();">
                                        <span class="icon">
                                            <i class="fas fa-search" aria-hidden="true"></i>
                                        </span>
                                        <span>search</span>
                                    </a>
                                </p>
                            </div>
                            <spieldose-pagination v-bind:loading="loadingTracks" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
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
                                            <i class="cursor-pointer fa fa-play" title="play this track" v-on:click.prevent="playerData.replace([track]);"></i>
                                            <i class="cursor-pointer fa fa-plus-square" title="enqueue this track" v-on:click.prevent="playerData.enqueue([track]);"></i>
                                            <i class="cursor-pointer fa fa-save" title="download this track" v-on:click.prevent="playerData.download(track.id);"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="panel" v-if="activeTab == 'albums'">
                            <div class="browse-album-item" v-show="! loading" v-for="album, i in artist.albums" v-bind:key="i">
                            <a class="play-album" title="click to play album" v-on:click.prevent="playAlbumTracks(album.name, album.artist, album.year);">
                                    <img class="album-thumbnail" v-bind:src="album.image | getAlbumImageUrl" v-on:error="album.image = null;">
                                    <i class="fas fa-play fa-4x"></i>
                                    <img class="vinyl no-cover" src="images/vinyl.png" />
                                </a>
                                <div class="album-info">
                                    <p class="album-name">{{ album.name }}</p>
                                    <p class="album-year" v-show="album.year">({{ album.year }})</p>
                                </div>
                            </div>
                            <div class="is-clearfix"></div>
                        </div>
                        <div class="panel" v-if="activeTab == 'update'">
                            <div class="content is-clearfix">
                                <div class="field is-horizontal has-addons">
                                    <div class="field-label is-normal">
                                        <label class="label">Artist name:</label>
                                    </div>
                                    <div class="field-body">
                                        <div class="field is-expanded has-addons">
                                            <div class="control has-icons-left is-expanded" v-bind:class="loading ? 'is-loading': ''">
                                                <input class="input" :disabled="loading" v-model.trim="artist.name" type="text" placeholder="search artist name...">
                                                <span class="icon is-small is-left">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                            </div>
                                            <div class="control">
                                                <a class="button is-info" v-on:click.prevent="searchMusicBrainz();">Search on Music Brainz</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="field is-horizontal has-addons">
                                    <div class="field-label is-normal">
                                        <label class="label">Music Brainz id:</label>
                                    </div>
                                    <div class="field-body">
                                        <div class="field is-expanded has-addons">
                                            <div class="control has-icons-left is-expanded" v-bind:class="loading ? 'is-loading': ''">
                                                <input class="input" :disabled="loading" v-model.trim="artist.mbid" type="text" placeholder="set artist music brainz id">
                                                <span class="icon is-small is-left">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                            </div>
                                            <div class="control">
                                                <a class="button is-info" :disabled="! (artist.name && artist.mbid)" v-on:click.prevent="overwriteMusicBrainzArtist(artist.name, artist.mbid);">Save</a>
                                            </div>
                                            <div class="control">
                                                <a class="button is-danger" v-on:click.prevent="clearMusicBrainzArtist(artist.name, artist.mbid);">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <spieldose-api-error-component v-if="hasAPIErrors" v-bind:apiError="apiError"></spieldose-api-error-component>
            </div>
        `;
    };

    let module = Vue.component('spieldose-browse-artist', {
        template: template(),
        mixins: [
            mixinAPIError, mixinPlayer, mixinPagination, mixinLiveSearches, mixinArtists, mixinAlbums, mixinPlayer
        ],
        data: function () {
            return ({
                loading: false,
                loadingTracks: false,
                artist: {},
                activeTab: 'overview',
                truncatedBio: null,
                detailedView: false,
                tracks: [],
                nameFilter: null,
                timeout: null,
                updateArtistName: null,
                updateArtistMBId: null
            });
        },
        watch: {
            '$route'(to, from) {
                switch (to.name) {
                    case "artistBio":
                        this.getArtist(to.params.artist);
                        this.activeTab = "bio";
                        break;
                    case "artistTracks":
                    case "artistTracksPaged":
                        this.pager.actualPage = parseInt(to.params.page);
                        this.searchArtistTracks(to.params.artist);
                        this.activeTab = "tracks";
                        break;
                    case "artistAlbums":
                        this.getArtist(to.params.artist);
                        this.activeTab = "albums";
                        break;
                    case "artist":
                        this.getArtist(to.params.artist);
                        this.activeTab = "overview";
                        break;
                    case "artistUpdate":
                        this.getArtist(to.params.artist);
                        this.activeTab = "update";
                        break;
                }
            }
        }, created: function () {
            this.getArtist(this.$route.params.artist);
            let self = this;
            if (this.$route.name == "artistTracks" || this.$route.name == "artistTracksPaged") {
                if (this.$route.params.page) {
                    this.pager.actualPage = parseInt(this.$route.params.page);
                }
                this.searchArtistTracks(this.$route.params.artist);
                this.activeTab = "tracks";
            } else {
                switch (this.$route.name) {
                    case "artistBio":
                        this.activeTab = "bio";
                        break;
                    case "artistAlbums":
                        this.activeTab = "albums";
                        break;
                    case "artistUpdate":
                        this.activeTab = "update";
                        break;
                    default:
                        this.activeTab = "overview";
                        break;
                }
            }
        }, methods: {
            onPaginationChanged: function (currentPage) {
                this.$router.push({ name: 'artistTracksPaged', params: { page: currentPage } });
            },
            getArtist: function (artist) {
                let self = this;
                self.loading = true;
                self.errors = false;
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
                let self = this;
                if (self.timeout) {
                    clearTimeout(self.timeout);
                }
                self.timeout = setTimeout(function () {
                    self.pager.actualPage = 1;
                    self.searchArtistTracks(self.$route.params.artist);
                }, 256);
            },
            searchTracks: function () {
                this.searchArtistTracks(this.$route.params.artist);
            },
            searchArtistTracks: function (artist) {
                let self = this;
                self.loading = true;
                self.loadingTracks = true;
                self.clearAPIErrors();
                let text = this.nameFilter ? this.nameFilter : "";
                spieldoseAPI.searchTracks(text, artist, "", false, self.pager.actualPage, self.pager.resultsPage, "", function (response) {
                    if (response.ok) {
                        self.pager.actualPage = response.body.actualPage;
                        self.pager.totalPages = response.body.totalPages;
                        self.pager.totalResults = response.body.totalResults;
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.tracks = response.body.tracks;
                        } else {
                            self.tracks = [];
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loadingTracks = false;
                    self.loading = false;
                });
            },
            changeTab: function (tab) {
                this.activeTab = tab;
            }, truncate: function (text) {
                return (text.substring(0, 500));
            },
            enqueueAlbumTracks: function (album, artist, year) {
                let self = this;
                self.loading = true;
                self.clearAPIErrors();
                spieldoseAPI.getAlbumTracks(album || null, artist || null, year || null, function (response) {
                    self.playerData.emptyPlayList();
                    if (response.ok) {
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.playerData.tracks = response.body.tracks;
                            self.playerData.play();
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            searchMusicBrainz(artistName) {
                window.open('https://musicbrainz.org/search?query=' + encodeURI(this.artist.name) + '&type=artist&limit=16&method=indexed');
            },
            overwriteMusicBrainzArtist(name, mbid) {
                let self = this;
                self.loading = true;
                self.errors = false;
                spieldoseAPI.overwriteMusicBrainzArtist(name, mbid, function (response) {
                    if (response.ok) {
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            clearMusicBrainzArtist: function (name, mbid) {
                let self = this;
                self.loading = true;
                self.errors = false;
                spieldoseAPI.clearMusicBrainzArtist(name, mbid, function (response) {
                    if (response.ok) {
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            }
        }
    });

    return (module);
})();