let search = (function () {
    "use strict";

    const template = function () {
        return `
            <div class="container is-fluid box is-marginless">
                <p class="title is-1 has-text-centered">{{ $t("search.labels.sectionName") }}</p>
                <div v-if="! hasAPIErrors">
                    <div class="field has-addons">
                        <div class="control is-expanded has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                            <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="$t('search.inputs.searchTextPlaceholder')" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                            <input type="text" class="input" v-bind:placeholder="$t('search.inputs.searchTextPlaceholder')" v-else v-bind:disabled="loading" v-model.trim="textFilter" v-on:keyup.enter="search();">
                            <span class="icon is-small is-left">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <p class="control" v-if="! liveSearch">
                            <a class="button is-info" v-on:click.prevent="search();">
                                <span class="icon">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                </span>
                                <span>{{ $t("search.buttons.search") }}</span>
                            </a>
                        </p>
                    </div>
                    <div class="columns is-desktop">
                        <div class="column is-one-quarter is-clipped">
                            <h1 class="title is-6 has-text-centered">{{ $t("search.tabs.artists") }}</h1>
                            <hr class="dropdown-divider">
                            <article class="media" v-for="artist, i in artists" v-bind:key="i">
                                <div class="media-left">
                                    <figure class="image is-48x48">
                                        <img class="border-radius-50" v-bind:src="artist.image | getArtistImageUrl" v-on:error="artist.image = null;">
                                    </figure>
                                </div>
                                <div class="media-content">
                                    <div class="content">
                                        <p class="subtitle is-6"><a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(artist.name);">{{ artist.name }}</a></p>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <div class="column is-one-quarter is-clipped">
                            <h1 class="title is-6 has-text-centered">{{ $t("search.tabs.albums") }}</h1>
                            <hr class="dropdown-divider">
                            <article class="media" v-for="album, i in albums" v-bind:key="i">
                                <div class="media-left">
                                    <figure class="image is-48x48">
                                        <img class="border-radius-50" v-bind:src="album.image | getAlbumImageUrl" v-on:error="album.image = null;"/>
                                    </figure>
                                </div>
                                <div class="media-content">
                                    <div class="content cut-text">
                                        <p class="subtitle is-6">
                                            <i class="cursor-pointer fa fa-play" v-bind:title="$t('commonLabels.playThisAlbum')" v-on:click.prevent="playAlbumTracks(album.name, album.artist, album.year);"></i>
                                            <i class="cursor-pointer fa fa-plus-square" v-bind:title="$t('commonLabels.enqueueThisAlbum')" v-on:click.prevent="enqueueAlbumTracks(album.name, album.artist, album.year);"></i>
                                            <span>{{ album.name }}</span>
                                            <br>
                                            <span v-if="album.artist">{{ $t("commonLabels.by" )}} <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(album.artist);">{{ album.artist }}</a></span>
                                        </p>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <div class="column is-one-quarter is-clipped">
                            <h1 class="title is-6 has-text-centered">{{ $t("search.tabs.tracks") }}</h1>
                            <hr class="dropdown-divider">
                            <article class="media" v-for="track, i in tracks" v-bind:key="track.id">
                                <div class="media-left">
                                </div>
                                <div class="media-content">
                                    <div class="content cut-text">
                                        <i class="cursor-pointer fa fa-play" v-bind:title="$t('commonLabels.playThisTrack')" v-on:click.prevent="playTrack(track);"></i>
                                        <i class="cursor-pointer fa fa-plus-square" v-bind:title="$t('commonLabels.enqueueThisTrack')" v-on:click.prevent="enqueueTrack(track);"></i>
                                        <span>{{ track.title }}</span>
                                        <br >
                                        <span v-if="track.artist">{{ $t("commonLabels.by" )}} <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(track.artist);">{{ track.artist }}</a></span> <span v-if="track.album"> / {{ track.album }}</span>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <div class="column is-one-quarter is-clipped">
                            <h1 class="title is-6 has-text-centered">{{ $t("search.tabs.playlists") }}</h1>
                            <hr class="dropdown-divider">
                            <article class="media" v-for="playlist in playlists" v-bind:key="playlist.id">
                                <div class="media-left">
                                </div>
                                <div class="media-content">
                                    <div class="content cut-text">
                                        <p class="subtitle is-6">
                                        <i v-on:click="playPlaylistTracks(playlist.id);" class="cursor-pointer fa fa-play" v-bind:title="$t('commonLabels.playThisPlaylist')"></i>
                                        <i v-on:click="enqueuePlaylistTracks(playlist.id);" class="cursor-pointer fa fa-plus-square" v-bind:title="$t('commonLabels.enqueueThisPlaylist')"></i>
                                        {{ playlist.name }} ({{ playlist.trackCount}} {{$t("commonLabels.tracksCount")}})
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
            mixinAPIError, mixinNavigation, mixinLiveSearches, mixinPlayer, mixinAlbums, mixinArtists
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