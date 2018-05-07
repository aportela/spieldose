let search = (function () {
    "use strict";

    const template = function () {
        return `
            <div class="container is-fluid box is-marginless">
                <p class="title is-1 has-text-centered">Search artists, albums, tracks, playlists</p>
                <div v-if="! hasAPIErrors">
                    <div class="field has-addons">
                        <div class="control is-expanded has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                            <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="'search tracks/artists/albums/playlists...'" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                            <input type="text" class="input" placeholder="search tracks/artists/albums/playlists...." v-else v-bind:disabled="loading"  v-focus v-model.trim="textFilter" v-on:keyup.enter="search();">
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
                    <div class="columns is-desktop">
                        <div class="column is-one-quarter is-clipped">
                            <h1 class="title is-6 has-text-centered">Artists</h1>
                            <hr class="dropdown-divider">
                            <article class="media" v-for="artist, i in artists" v-bind:key="i">
                                <div class="media-left">
                                    <figure class="image is-48x48">
                                        <!-- TODO -->
                                        <img class="border-radius-50" v-if="artist.image" v-bind:src="artist.image" v-on:error="artist.image = null;"/>
                                        <img class="border-radius-50" v-else src="https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png" />
                                    </figure>
                                </div>
                                <div class="media-content">
                                    <div class="content">
                                        <p class="subtitle is-6"><a title="click to open artist section" v-on:click.prevent="navigateToArtistPage(artist.name);">{{ artist.name }}</a></p>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <div class="column is-one-quarter is-clipped">
                            <h1 class="title is-6 has-text-centered">Albums</h1>
                            <hr class="dropdown-divider">
                            <article class="media" v-for="album, i in albums" v-bind:key="i">
                                <div class="media-left">
                                    <figure class="image is-48x48">
                                        <img class="border-radius-50" v-if="album.image" v-bind:src="album.image | albumThumbnailUrlToCacheUrl" v-on:error="album.image = null;"/>
                                        <img class="border-radius-50" v-else src="images/image-album-not-set.png"/>
                                    </figure>
                                </div>
                                <div class="media-content">
                                    <div class="content cut-text">
                                        <p class="subtitle is-6">
                                            <i class="cursor-pointer fa fa-play" title="play this album" v-on:click.prevent="playAlbumTracks(album.name, album.artist, album.year);"></i>
                                            <i class="cursor-pointer fa fa-plus-square" title="enqueue this album" v-on:click.prevent="enqueueAlbumTracks(album.name, album.artist, album.year);"></i>
                                            <span>{{ album.name }}</span>
                                            <br>
                                            <span v-if="album.artist">by <a v-on:click.prevent="navigateToArtistPage(album.artist);">{{ album.artist }}</a></span>
                                        </p>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <div class="column is-one-quarter is-clipped">
                            <h1 class="title is-6 has-text-centered">Tracks</h1>
                            <hr class="dropdown-divider">
                            <article class="media" v-for="track, i in tracks" v-bind:key="track.id">
                                <div class="media-left">
                                </div>
                                <div class="media-content">
                                    <div class="content cut-text">
                                        <i class="cursor-pointer fa fa-play" title="play this track" v-on:click.prevent="playTrack(track);"></i>
                                        <i class="cursor-pointer fa fa-plus-square" title="enqueue this track" v-on:click.prevent="enqueueTrack(track);"></i>
                                        <span>{{ track.title }}</span>
                                        <br >
                                        <span v-if="track.artist">by <a v-on:click.prevent="navigateToArtistPage(track.artist);">{{ track.artist }}</a></span> <span v-if="track.album"> / {{ track.album }}</span>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <div class="column is-one-quarter is-clipped">
                            <h1 class="title is-6 has-text-centered">Playlists</h1>
                            <hr class="dropdown-divider">
                            <article class="media" v-for="playlist in playlists" v-bind:key="playlist.id">
                                <div class="media-left">
                                </div>
                                <div class="media-content">
                                    <div class="content cut-text">
                                        <p class="subtitle is-6">
                                        <i v-on:click="playPlaylistTracks(playlist.id);" class="cursor-pointer fa fa-play" title="play this playlist"></i>
                                        <i v-on:click="enqueuePlaylistTracks(playlist.id);" class="cursor-pointer fa fa-plus-square" title="enqueue this playlist"></i>
                                        {{ playlist.name }} ({{ playlist.trackCount}} tracks)
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
    };

    let module = Vue.component('spieldose-search', {
        template: template(),
        mixins: [
            mixinAPIError, mixinFocus, mixinNavigation, mixinLiveSearches, mixinPlayer, mixinAlbums
        ],
        data: function () {
            return ({
                loading: false,
                textFilter: null,
                artists: [],
                albums: [],
                tracks: [],
                playlists: []
            });
        }, methods: {
            onTypeahead: function (text) {
                this.textFilter = text;
                this.search();
            },
            search: function () {
                let self = this;
                self.loading = true;
                self.clearAPIErrors();
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