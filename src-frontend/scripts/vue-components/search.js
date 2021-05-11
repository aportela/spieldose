import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinNavigation, mixinLiveSearches, mixinPlayer } from '../mixins.js';
import imageAlbum from './image-album.js';
import imageArtist from './image-artist.js';
import { default as inputTypeAHead } from './input-typeahead.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
            <p class="title is-1 has-text-centered">{{ $t("search.labels.sectionName") }}</p>
            <div v-if="! hasAPIErrors">
                <div class="field has-addons">
                    <div class="control is-expanded has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                        <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="$t('search.inputs.searchTextPlaceholder')" @on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" ref="inputSearch" v-bind:placeholder="$t('search.inputs.searchTextPlaceholder')" v-else v-bind:disabled="loading" v-model.trim="textFilter" @keyup.enter="search();">
                        <span class="icon is-small is-left">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <p class="control" v-if="! liveSearch">
                        <button type="button" class="button is-dark" @click.prevent="search();" :disabled="! textFilter">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("search.buttons.search") }}</span>
                        </button>
                    </p>
                </div>
                <div class="columns is-desktop">
                    <div class="column is-one-quarter is-clipped">
                        <h1 class="title is-6 has-text-centered">{{ $t("search.tabs.artists") }}</h1>
                        <hr class="dropdown-divider">
                        <article class="media" v-for="artist, i in artists" v-bind:key="i">
                            <div class="media-left">
                                <figure class="image is-48x48">
                                    <spieldose-image-artist :src="artist.image" :extraClass="'is-rounded'"></spieldose-image-artist>
                                </figure>
                            </div>
                            <div class="media-content">
                                <div class="content">
                                    <p class="subtitle is-6"><router-link :title="$t('commonLabels.navigateToArtistPage')" :to="{ name: 'artist', params: { artist: artist.name }}">{{ artist.name }}</router-link></p>
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
                                    <spieldose-image-album :src="album.image" :extraClass="'is-rounded'"></spieldose-image-album>
                                </figure>
                            </div>
                            <div class="media-content">
                                <div class="content cut-text">
                                    <p class="subtitle is-6">
                                        <span class="icon cursor-pointer" v-bind:title="$t('commonLabels.playThisAlbum')" @click.prevent="playAlbumTracks(album.name, album.artist, album.year)">
                                            <i class="fa fa-play"></i>
                                        </span>
                                        <span class="icon cursor-pointer" v-bind:title="$t('commonLabels.enqueueThisAlbum')" @click.prevent="enqueueAlbumTracks(album.name, album.artist, album.year)">
                                            <i class="fa fa-plus-square"></i>
                                        </span>
                                        <span>{{ album.name }}</span>
                                        <br>
                                        <span v-if="album.artist">{{ $t("commonLabels.by" )}} <router-link :title="$t('commonLabels.navigateToArtistPage')" :to="{ name: 'artist', params: { artist: album.artist }}">{{ album.artist }}</router-link></span>
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
                                    <span class="icon cursor-pointer" v-bind:title="$t('commonLabels.playThisTrack')" @click.prevent="playTrack(track)">
                                        <i class="fa fa-play"></i>
                                    </span>
                                    <span class="icon cursor-pointer" v-bind:title="$t('commonLabels.enqueueThisTrack')" @click.prevent="enqueueTrack(track)">
                                        <i class="fa fa-plus-square"></i>
                                    </span>
                                    <span>{{ track.title }}</span>
                                    <br >
                                    <span v-if="track.artist">{{ $t("commonLabels.by" )}} <router-link :title="$t('commonLabels.navigateToArtistPage')" :to="{ name: 'artist', params: { artist: track.artist }}">{{ track.artist }}</router-link></span> <span v-if="track.album"> / {{ track.album }}</span>
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
                                    <span class="icon cursor-pointer" v-bind:title="$t('commonLabels.playThisPlaylist')" @click="playPlaylistTracks(playlist.id);">
                                        <i class="fa fa-play"></i>
                                    </span>
                                    <span class="icon cursor-pointer" v-bind:title="$t('commonLabels.enqueueThisPlaylist')" @click="enqueuePlaylistTracks(playlist.id)">
                                        <i class="fa fa-plus-square"></i>
                                    </span>
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

export default {
    name: 'spieldose-search',
    template: template(),
    mixins: [
        mixinAPIError, mixinNavigation, mixinLiveSearches, mixinPlayer
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
    },
    mounted: function() {
        if (! this.liveSearch) {
            this.$nextTick(() => this.$refs.inputSearch.focus());
        }
    },
    components: {
        'spieldose-input-typeahead': inputTypeAHead,
        'spieldose-image-artist': imageArtist,
        'spieldose-image-album': imageAlbum
    },
    methods: {
        onTypeahead: function (text) {
            this.textFilter = text;
            this.search();
        },
        search: function () {
            this.loading = true;
            this.clearAPIErrors();
            spieldoseAPI.globalSearch(this.textFilter, 1, 8, (response) => {
                if (response.status == 200) {
                    if (response.data.artists && response.data.artists.length > 0) {
                        this.artists = response.data.artists;
                    } else {
                        this.artists = [];
                    }
                    if (response.data.albums && response.data.albums.length > 0) {
                        this.albums = response.data.albums;
                    } else {
                        this.albums = [];
                    }
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.tracks = response.data.tracks;
                    } else {
                        this.tracks = [];
                    }
                    if (response.data.playlists && response.data.playlists.length > 0) {
                        this.playlists = response.data.playlists;
                    } else {
                        this.playlists = [];
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
                this.loading = false;
            });
        }
    }
}