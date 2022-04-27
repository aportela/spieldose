import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPlayer, mixinPagination, mixinLiveSearches, mixinArtists, mixinAlbums } from '../mixins.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
            <p v-if="loading" class="title is-1 has-text-centered">Loading <i v-if="loading" class="fas fa-cog fa-spin fa-fw"></i></p>
            <p v-else="! loading" class="title is-1 has-text-centered">{{ $t("browseArtist.labels.sectionName") }}</p>
            <div class="media" v-if="! hasAPIErrors && ! loading">
                <figure class="image media-left">
                    <img class="artist_avatar" v-bind:src="artist.image | getArtistImageUrl" v-on:error="artist.image = null;">
                </figure>
                <div class="media-content is-light">
                    <p class="title is-1">{{ artist.name }}</p>
                    <p class="subtitle is-6" v-if="artist.playCount > 0">{{ artist.playCount }} {{ $t("browseArtist.labels.plays") }}</p>
                    <p class="subtitle is-6" v-else>{{ $t("browseArtist.labels.notPlayedYet") }}</p>
                    <div class="tabs is-medium">
                        <ul>
                            <li v-bind:class="{ 'is-active' : activeTab == 'overview' }"><a v-on:click.prevent="$router.push({ name: 'artist', params: { 'artist': $route.params.artist } })">{{ $t("browseArtist.tabs.overview") }}</a></li>
                            <li v-bind:class="{ 'is-active' : activeTab == 'bio' }"><a v-on:click.prevent="$router.push({ name: 'artistBio' })">{{ $t("browseArtist.tabs.bio") }}</a></li>
                            <li v-bind:class="{ 'is-active' : activeTab == 'tracks' }"><a v-on:click.prevent="$router.push({ name: 'artistTracks' })">{{ $t("browseArtist.tabs.tracks") }}</a></li>
                            <li v-bind:class="{ 'is-active' : activeTab == 'albums' }"><a v-on:click.prevent="$router.push({ name: 'artistAlbums' })">{{ $t("browseArtist.tabs.albums") }}</a></li>
                            <li v-bind:class="{ 'is-active' : activeTab == 'update' }"><a v-on:click.prevent="$router.push({ name: 'artistUpdate' })">{{ $t("browseArtist.tabs.updateArtist") }}</a></li>
                        </ul>
                    </div>
                    <div class="panel" v-if="activeTab == 'overview'">
                        <div class="content is-clearfix" id="bio" v-if="artist.bio" v-html="truncatedBio"></div>
                        <div class="content is-clearfix" id="bio" v-else>Artist biography not found / not scraped</div>
                        <div class="columns">
                            <div class="column is-half is-full-mobile">
                                <spieldose-dashboard-toplist v-if="activeTab == 'overview' && artist.name" v-bind:type="'topTracks'" v-bind:title="$t('dashboard.labels.topPlayedTracks')" v-bind:listItemCount="10" v-bind:showPlayCount="true" :key="$route.params.artist" v-bind:artist="$route.params.artist"></spieldose-dashboard-toplist>
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
                                    <span>{{ $t("browseArtist.buttons.search") }}</span>
                                </a>
                            </p>
                        </div>
                        <spieldose-pagination v-bind:loading="loadingTracks" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
                        <table class="table is-bordered is-striped is-narrow is-fullwidth">
                            <thead>
                                    <tr class="is-unselectable">
                                        <th>{{ $t("browseArtist.labels.tracksAlbumTableHeader") }}</th>
                                        <th>{{ $t("browseArtist.labels.tracksSectionYearTableHeader") }}</th>
                                        <th>{{ $t("browseArtist.labels.tracksSectionNumberTableHeader") }}</th>
                                        <th>{{ $t("browseArtist.labels.tracksSectionTitleTableHeader") }}</th>
                                        <th>{{ $t("browseArtist.labels.tracksSectionActionTableHeader") }}</th>
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
                                        <i class="cursor-pointer fa-fw fa fa-play" v-bind:title="$t('commonLabels.playThisTrack')" v-on:click.prevent="playerData.currentPlaylist.replace([track]);"></i>
                                        <i class="cursor-pointer fa-fw fa fa-plus-square"  v-bind:title="$t('commonLabels.enqueueThisTrack')" v-on:click.prevent="playerData.currentPlaylist.enqueue([track]);"></i>
                                        <i class="cursor-pointer fa-fw fa fa-save"  v-bind:title="$t('commonLabels.downloadThisTrack')" v-on:click.prevent="playerData.download(track.id);"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="panel" v-if="activeTab == 'albums'">
                        <div class="browse-album-item" v-show="! loading" v-for="album, i in artist.albums" v-bind:key="i">
                        <a class="play-album" v-bind:title="$t('commonLabels.playThisAlbum')" v-on:click.prevent="playAlbumTracks(album.name, album.artist, album.year);">
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
                        <div class="panel-block">
                            <div class="content is-clearfix">
                                <div class="field is-horizontal has-addons">
                                    <div class="field-label is-normal">
                                        <label class="label">{{ $t("browseArtist.labels.musicBrainzSearchArtistName") }}</label>
                                    </div>
                                    <div class="field-body">
                                        <div class="field is-expanded has-addons">
                                            <div class="control has-icons-left is-expanded" v-bind:class="loading ? 'is-loading': ''">
                                                <input class="input" :disabled="loading" v-model.trim="artist.name" type="text" v-bind:placeholder="$t('browseArtist.labels.musicBrainzSearchArtistNamePlaceholder')">
                                                <span class="icon is-small is-left">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                            </div>
                                            <div class="control">
                                                <a class="button is-info" v-on:click.prevent="searchMusicBrainz();">{{ $t("browseArtist.buttons.searchOnMusicBrainz") }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="field is-horizontal has-addons">
                                    <div class="field-label is-normal">
                                        <label class="label">{{ $t("browseArtist.labels.musicBrainzSearchArtistMBId") }}</label>
                                    </div>
                                    <div class="field-body">
                                        <div class="field is-expanded has-addons">
                                            <div class="control has-icons-left is-expanded" v-bind:class="loading ? 'is-loading': ''">
                                                <input class="input" :disabled="loading" v-model.trim="artist.mbid" type="text" v-bind:placeholder="$t('browseArtist.labels.musicBrainzSearchArtistMBIdPlaceholder')">
                                                <span class="icon is-small is-left">
                                                    <i class="fas fa-search"></i>
                                                </span>
                                            </div>
                                            <div class="control">
                                                <a class="button is-info" :disabled="! (artist.name && artist.mbid)" v-on:click.prevent="overwriteMusicBrainzArtist(artist.name, artist.mbid);">{{ $t("browseArtist.buttons.save") }}</a>
                                            </div>
                                            <div class="control">
                                                <a class="button is-danger" v-on:click.prevent="clearMusicBrainzArtist(artist.name, artist.mbid);">{{ $t("browseArtist.buttons.clear") }}</a>
                                            </div>
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

export default {
    name: 'spieldose-browse-artist',
    template: template(),
    mixins: [
        mixinAPIError, mixinPlayer, mixinPagination, mixinLiveSearches, mixinArtists, mixinAlbums
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
                case 'artistBio':
                    this.getArtist(to.params.artist);
                    this.activeTab = 'bio';
                    break;
                case 'artistTracks':
                case 'artistTracksPaged':
                    this.pager.actualPage = parseInt(to.params.page);
                    this.searchArtistTracks(to.params.artist);
                    this.activeTab = 'tracks';
                    break;
                case 'artistAlbums':
                    this.getArtist(to.params.artist);
                    this.activeTab = 'albums';
                    break;
                case 'artist':
                    this.getArtist(to.params.artist);
                    this.activeTab = 'overview';
                    break;
                case 'artistUpdate':
                    this.getArtist(to.params.artist);
                    this.activeTab = 'update';
                    break;
            }
        }
    },
    created: function () {
        this.getArtist(this.$route.params.artist);
        if (this.$route.name == 'artistTracks' || this.$route.name == 'artistTracksPaged') {
            if (this.$route.params.page) {
                this.pager.actualPage = parseInt(this.$route.params.page);
            }
            this.searchArtistTracks(this.$route.params.artist);
            this.activeTab = 'tracks';
        } else {
            switch (this.$route.name) {
                case 'artistBio':
                    this.activeTab = 'bio';
                    break;
                case 'artistAlbums':
                    this.activeTab = 'albums';
                    break;
                case 'artistUpdate':
                    this.activeTab = 'update';
                    break;
                default:
                    this.activeTab = 'overview';
                    break;
            }
        }
    },
    methods: {
        onPaginationChanged: function (currentPage) {
            this.$router.push({ name: 'artistTracksPaged', params: { page: currentPage } });
        },
        getArtist: function (artist) {
            this.loading = true;
            this.errors = false;
            spieldoseAPI.artist.get(artist).then(response => {
                this.artist = response.data.artist;
                if (this.artist.bio) {
                    this.artist.bio = this.artist.bio.replace(/(?:\r\n|\r|\n)/g, '<br />');
                    this.truncatedBio = this.truncate(this.artist.bio);
                    //this.activeTab = "overview";
                }
                this.loading = false;
            }).catch(error => {
                this.errors = true;
                this.apiError = response.getApiErrorData();
                this.loading = false;
            });
        },
        abortInstantSearch: function () {
            this.nameFilter = null;
            clearTimeout(this.timeout);
        },
        instantSearch: function () {
            if (this.timeout) {
                clearTimeout(this.timeout);
            }
            this.timeout = setTimeout(function () {
                this.pager.actualPage = 1;
                this.searchArtistTracks(this.$route.params.artist);
            }, 256);
        },
        searchTracks: function () {
            this.searchArtistTracks(this.$route.params.artist);
        },
        searchArtistTracks: function (artist) {
            this.loading = true;
            this.loadingTracks = true;
            this.clearAPIErrors();
            let text = this.nameFilter ? this.nameFilter : '';
            spieldoseAPI.track.searchTracks(text, artist, '', false, this.pager.actualPage, this.pager.resultsPage, '').then(response => {
                this.pager.actualPage = response.data.actualPage;
                this.pager.totalPages = response.data.totalPages;
                this.pager.totalResults = response.data.totalResults;
                if (response.data.tracks && response.data.tracks.length > 0) {
                    this.tracks = response.data.tracks;
                } else {
                    this.tracks = [];
                }
                this.loadingTracks = false;
                this.loading = false;
            }).catch(error => {
                this.setAPIError(error.getApiErrorData());
                this.loadingTracks = false;
                this.loading = false;
            });
        },
        changeTab: function (tab) {
            this.activeTab = tab;
        }, truncate: function (text) {
            return (text.substring(0, 500));
        },
        enqueueAlbumTracks: function (album, artist, year) {
            this.loading = true;
            this.clearAPIErrors();
            spieldoseAPI.track.getAlbumTracks(album || null, artist || null, year || null).then(response => {
                this.playerData.currentPlaylist.empty();
                if (response.data.tracks && response.data.tracks.length > 0) {
                    this.playerData.tracks = response.data.tracks;
                    this.playerData.playback.play();
                }
                this.loading = false;
            }).catch(error => {
                this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        searchMusicBrainz(artistName) {
            window.open('https://musicbrainz.org/search?query=' + encodeURI(this.artist.name) + '&type=artist&limit=16&method=indexed');
        },
        overwriteMusicBrainzArtist(name, mbid) {
            this.loading = true;
            this.errors = false;
            spieldoseAPI.artist.overwriteMusicBrainz(name, mbid).then(response => {
                this.loading = false;
            }).catch(error => {
                this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        },
        clearMusicBrainzArtist: function (name, mbid) {
            this.loading = true;
            this.errors = false;
            spieldoseAPI.artist.clearMusicBrainz(name, mbid).then(response => {
                this.loading = false;
            }).catch(error => {
                this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        }
    }
}