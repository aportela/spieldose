import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPlayer, mixinPagination, mixinAlbums, mixinLiveSearches } from '../mixins.js';
import { default as imageArtist } from './image-artist.js';
import { default as imageAlbum } from './image-album.js';
import { default as dashboardTopList } from './dashboard-toplist.js';
import { default as pagination } from './pagination';
import Chart from 'chart.js/auto';

const template = function () {
    return `
        <div>
            <div>
                <div id="artist-header-block">
                    <div id="artist-header-block-background-image" v-if="artist && artist.image" :style="'background-image: url(api/thumbnail?url=' + artist.image + ')'"></div>
                    <div id="artist-header-block-background-overlay"></div>
                    <div id="artist-header-block-content">
                        <div class="p-6">
                            <p class="has-text-white title is-1">{{ artist.name }}</p>
                            <p class="has-text-white title is-6"><span class="has-text-grey"><i class="fas fa-users"></i> Listeners:</span> <span class="has-text-grey-lighter">204 users</span></p>
                            <p class="has-text-white title is-6"><span class="has-text-grey"><i class="fas fa-compact-disc"></i> Total plays:</span> <span class="has-text-grey-lighter">3945 times</span></p>
                            <div class="columns">
                                <div class="column is-half" v-if="latestAlbum">
                                    <figure class="image is-96x96 is-pulled-left">
                                        <spieldose-image-album :src="latestAlbum.image"></spieldose-image-album>
                                    </figure>
                                    <p style="margin-left: 110px; margin-top: 10px;">
                                        <span class="is-size-7 has-text-grey">LATEST RELEASE
                                        <br><strong class="is-size-6 has-text-grey-lighter">{{ latestAlbum.name }}</strong>
                                        <br><span class="is-size-6 has-text-grey-light" v-if="latestAlbum.year">{{ latestAlbum.year }}</span>
                                    </p>
                                </div>
                                <div class="column is-half" v-if="popularAlbum">
                                    <figure class="image is-96x96 is-pulled-left">
                                        <spieldose-image-album :src="popularAlbum.image"></spieldose-image-album>
                                    </figure>
                                    <p style="margin-left: 110px; margin-top: 10px;">
                                        <span class="is-size-7 has-text-grey">POPULAR
                                        <br><strong class="is-size-6 has-text-grey-lighter">{{ popularAlbum.name }}</strong>
                                        <br><span class="is-size-6 has-text-grey-light" v-if="popularAlbum.year">{{ popularAlbum.year }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div id="bottom">
                            <div class="tabs is-centered is-small">
                                <ul>
                                    <li class="is-active"><a class="has-text-grey-lighter">Overview</a></li>
                                    <li><a class="has-text-grey-lighter">Biography</a></li>
                                    <li><a class="has-text-grey-lighter">Albums</a></li>
                                    <li><a class="has-text-grey-lighter">Tracks</a></li>
                                    <li><a class="has-text-grey-lighter">Similar artists</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container is-fluid box mt-3">
                    <div class="columns">
                        <div class="column is-8">
                            <div class="content" id="bio" v-if="artist.bio" v-html="truncatedBio"></div>
                            <div class="is-clearfix">
                                <span class="title is-5 is-pulled-left">Top tracks</span>
                                <div class="dropdown is-pulled-right">
                                    <div class="dropdown-trigger">
                                        <button class="button is-small" aria-haspopup="true" aria-controls="dropdown-menu">
                                            <span>Last 7 days</span>
                                            <span class="icon is-small">
                                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="dropdown-menu" id="dropdown-menu" role="menu">
                                        <div class="dropdown-content">
                                            <a href="#" class="dropdown-item">
                                            Dropdown item
                                            </a>
                                            <a class="dropdown-item">
                                            Other dropdown item
                                            </a>
                                            <a href="#" class="dropdown-item is-active">
                                            Active dropdown item
                                            </a>
                                            <a href="#" class="dropdown-item">
                                            Other dropdown item
                                            </a>
                                            <hr class="dropdown-divider">
                                            <a href="#" class="dropdown-item">
                                            With a divider
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table is-unselectable is-clear-fix">
                                <tbody>
                                    <tr>
                                        <td class="is-vcentered">1</td>
                                        <td class="is-vcentered"><i class="fas fa-play cursor-pointer"></i></td>
                                        <td class="is-vcentered">
                                            <figure class="image is-32x32" v-if="latestAlbum">
                                                <spieldose-image-album :src="latestAlbum.image"></spieldose-image-album>
                                            </figure>
                                        </td>
                                        <td class="is-vcentered"><i class="fas fa-heart cursor-pointer"></i></td>
                                        <td class="is-vcentered">Enter sandman</td>
                                        <td class="is-vcentered">
                                            <span class="has-text-dark has-background-light" style="display: block: width: 90%;">11,763 listeners </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="is-clearfix">
                                <span class="title is-5 is-pulled-left">Albums</span>
                                <div class="dropdown is-pulled-right">
                                    <div class="dropdown-trigger">
                                        <button class="button is-small" aria-haspopup="true" aria-controls="dropdown-menu">
                                            <span>Most popular</span>
                                            <span class="icon is-small">
                                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="dropdown-menu" id="dropdown-menu" role="menu">
                                        <div class="dropdown-content">
                                            <a href="#" class="dropdown-item">
                                            Dropdown item
                                            </a>
                                            <a class="dropdown-item">
                                            Other dropdown item
                                            </a>
                                            <a href="#" class="dropdown-item is-active">
                                            Active dropdown item
                                            </a>
                                            <a href="#" class="dropdown-item">
                                            Other dropdown item
                                            </a>
                                            <hr class="dropdown-divider">
                                            <a href="#" class="dropdown-item">
                                            With a divider
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="is-clearfix">
                                <div class="browse-album-item" v-for="album, i in artist.albums" :key="album.name+album.artist+album.year" v-show="! loading && i < 4">
                                    <a class="play-album" v-bind:title="$t('commonLabels.playThisAlbum')" @click.prevent="playAlbumTracks(album.name, album.artist, album.year);">
                                        <spieldose-image-album :src="album.image"></spieldose-image-album>
                                        <i class="fas fa-play fa-4x"></i>
                                        <img class="vinyl no-cover" src="images/vinyl.png" />
                                    </a>
                                    <div class="album-info">
                                        <p class="album-name">{{ album.name }}</p>
                                        <p v-if="album.artist" class="artist-name">{{ $t("commonLabels.by") }}
                                            <router-link :title="$t('commonLabels.navigateToArtistPage')" :to="{ name: 'artist', params: { artist: album.artist }}">{{ album.artist }}</router-link>
                                            <span v-show="album.year"> ({{ album.year }})</span>
                                        </p>
                                        <p v-else class="artist-name">{{ $t("commonLabels.by") }} {{ $t("browseAlbums.labels.unknownArtist") }} <span v-show="album.year"> ({{ album.year }})</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="is-clearfix">
                                <span class="is-pulled-right">View all albums <i class="fas fa-angle-right"></i></span>
                            </div>
                        </div>
                        <div class="column is-4">
                            <div id="similar">
                                <p class="title is-6">Similar to</p>
                                <div class="columns is-size-6">
                                    <div class="column is-4 has-text-grey is-centered">
                                        <figure class="image is-96x96" style="margin: 0px auto;">
                                            <img class="is-rounded" src="api/thumbnail?url=https://lastfm-img2.akamaized.net/i/u/300x300/1a3adf2f20b642c3bc50b10048b980a6.png">
                                        </figure>
                                        <p class="has-text-centered has-text-grey">Artist1</p>
                                    </div>
                                    <div class="column is-4">
                                        <figure class="image is-96x96" style="margin: 0px auto;">
                                            <img class="is-rounded" src="api/thumbnail?url=https://lastfm-img2.akamaized.net/i/u/300x300/1a3adf2f20b642c3bc50b10048b980a6.png">
                                        </figure>
                                        <p class="has-text-centered has-text-grey">Artist</p>
                                    </div>
                                    <div class="column is-4">
                                        <figure class="image is-96x96" style="margin: 0px auto;">
                                            <img class="is-rounded" src="api/thumbnail?url=https://lastfm-img2.akamaized.net/i/u/300x300/1a3adf2f20b642c3bc50b10048b980a6.png">
                                        </figure>
                                        <p class="has-text-centered has-text-grey">Artist</p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="is-clearfix">
                                <span class="title is-5 is-pulled-left">Play stats</span>
                                <div class="dropdown is-pulled-right">
                                    <div class="dropdown-trigger">
                                        <button class="button is-small" aria-haspopup="true" aria-controls="dropdown-menu">
                                            <span>recent</span>
                                            <span class="icon is-small">
                                            <i class="fas fa-angle-down" aria-hidden="true"></i>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="dropdown-menu" id="dropdown-menu" role="menu">
                                        <div class="dropdown-content">
                                            <a href="#" class="dropdown-item">
                                            Dropdown item
                                            </a>
                                            <a class="dropdown-item">
                                            Other dropdown item
                                            </a>
                                            <a href="#" class="dropdown-item is-active">
                                            Active dropdown item
                                            </a>
                                            <a href="#" class="dropdown-item">
                                            Other dropdown item
                                            </a>
                                            <hr class="dropdown-divider">
                                            <a href="#" class="dropdown-item">
                                            With a divider
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <canvas width="100%" height="200" id="play-stats"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            -->


            <!--
            <p v-if="loading" class="title is-1 has-text-centered">Loading <i v-if="loading" class="fas fa-cog fa-spin fa-fw"></i></p>
            <p v-else="! loading" class="title is-1 has-text-centered">{{ $t("browseArtist.labels.sectionName") }}</p>
            <div class="media" v-if="! hasAPIErrors && ! loading">
                <figure class="image media-left">
                    <spieldose-image-artist :src="artist.image" :extraClass="'artist_avatar'"></spieldose-image-artist>
                </figure>
                <div class="media-content is-light">
                    <p class="title is-1">{{ artist.name }}</p>
                    <p class="subtitle is-6" v-if="artist.playCount > 0">{{ artist.playCount }} {{ $t("browseArtist.labels.plays") }}</p>
                    <p class="subtitle is-6" v-else>{{ $t("browseArtist.labels.notPlayedYet") }}</p>
                    <div class="tabs is-medium">
                        <ul>
                            <li v-bind:class="{ 'is-active' : activeTab == 'overview' }"><a @click.prevent="$router.push({ name: 'artist', params: { 'artist': $route.params.artist } })">{{ $t("browseArtist.tabs.overview") }}</a></li>
                            <li v-bind:class="{ 'is-active' : activeTab == 'bio' }"><a @click.prevent="$router.push({ name: 'artistBio' })">{{ $t("browseArtist.tabs.bio") }}</a></li>
                            <li v-bind:class="{ 'is-active' : activeTab == 'tracks' }"><a @click.prevent="$router.push({ name: 'artistTracks' })">{{ $t("browseArtist.tabs.tracks") }}</a></li>
                            <li v-bind:class="{ 'is-active' : activeTab == 'albums' }"><a @click.prevent="$router.push({ name: 'artistAlbums' })">{{ $t("browseArtist.tabs.albums") }}</a></li>
                            <li v-bind:class="{ 'is-active' : activeTab == 'update' }"><a @click.prevent="$router.push({ name: 'artistUpdate' })">{{ $t("browseArtist.tabs.updateArtist") }}</a></li>
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
                                <input class="input" :disabled="loadingTracks" v-if="liveSearch" v-model.trim="nameFilter" type="text" placeholder="search by text..." @keyup.esc="abortInstantSearch();" @keyup="instantSearch();">
                                <input class="input" :disabled="loadingTracks" v-else v-model.trim="nameFilter" type="text" placeholder="search by text..." @keyup.enter="searchTracks();">
                                <span class="icon is-small is-left">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <p class="control" v-if="! liveSearch">
                                <a class="button is-info" @click.prevent="searchTracks();">
                                    <span class="icon">
                                        <i class="fas fa-search" aria-hidden="true"></i>
                                    </span>
                                    <span>{{ $t("browseArtist.buttons.search") }}</span>
                                </a>
                            </p>
                        </div>
                        <spieldose-pagination v-bind:loading="loadingTracks" v-bind:data="pager" @pagination-changed="onPaginationChanged"></spieldose-pagination>
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
                                        <i class="cursor-pointer fa fa-play" v-bind:title="$t('commonLabels.playThisTrack')" @click.prevent="player.currentPlaylist.replace([track]);"></i>
                                        <i class="cursor-pointer fa fa-plus-square"  v-bind:title="$t('commonLabels.enqueueThisTrack')" @click.prevent="player.currentPlaylist.enqueue([track]);"></i>
                                        <i class="cursor-pointer fa fa-save"  v-bind:title="$t('commonLabels.downloadThisTrack')" @click.prevent="player.download(track.id);"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="panel" v-if="activeTab == 'albums'">
                        <div class="browse-album-item" v-show="! loading" v-for="album, i in artist.albums" v-bind:key="i">
                        <a class="play-album" v-bind:title="$t('commonLabels.playThisAlbum')" @click.prevent="playAlbumTracks(album.name, album.artist, album.year);">
                                <spieldose-image-album :src="album.image"></spieldose-image-album>
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
                                                <a class="button is-info" @click.prevent="searchMusicBrainz();">{{ $t("browseArtist.buttons.searchOnMusicBrainz") }}</a>
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
                                                <a class="button is-info" :disabled="! (artist.name && artist.mbid)" @click.prevent="overwriteMusicBrainzArtist(artist.name, artist.mbid);">{{ $t("browseArtist.buttons.save") }}</a>
                                            </div>
                                            <div class="control">
                                                <a class="button is-danger" @click.prevent="clearMusicBrainzArtist(artist.name, artist.mbid);">{{ $t("browseArtist.buttons.clear") }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            -->
            <spieldose-api-error-component v-if="hasAPIErrors" v-bind:apiError="apiError"></spieldose-api-error-component>
        </div>
    `;
};


export default {
    name: 'spieldose-browse-artist',
    template: template(),
    mixins: [
        mixinAPIError, mixinPlayer, mixinPagination, mixinLiveSearches
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
            updateArtistMBId: null,
            chart: null
        });
    },
    computed: {
        latestAlbum: function () {
            if (this.artist && this.artist.albums) {
                return (this.artist.albums[this.artist.albums.length - 1]);
            } else {
                return (null);
            }
        },
        popularAlbum: function () {
            if (this.artist && this.artist.albums) {
                return (this.artist.albums[0]);
            } else {
                return (null);
            }
        }
    },
    components: {
        'spieldose-dashboard-toplist': dashboardTopList,
        'spieldose-pagination': pagination,
        'spieldose-image-artist': imageArtist,
        'spieldose-image-album': imageAlbum,
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
    mounted: function () {
        if (this.chart) {
            this.chart.destroy();
        }
        const element = document.getElementById('play-stats');
        if (element) {
            this.chart = new Chart(element, {
                type: 'line',
                data: {
                    labels: ['January', 'February', 'March', 'April', 'May', 'June'],
                    datasets: [{
                        label: 'Total plays',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: '#666666',
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }

                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    },
    methods: {
        onPaginationChanged: function (currentPage) {
            this.$router.push({ name: 'artistTracksPaged', params: { page: currentPage } });
        },
        getArtist: function (artist) {
            this.loading = true;
            this.errors = false;
            spieldoseAPI.artist.get(artist, (response) => {
                if (response.status == 200) {
                    this.artist = response.data.artist;
                    if (this.artist.bio) {
                        this.artist.bio = this.artist.bio.replace(/(?:\r\n|\r|\n)/g, '<br />');
                        this.truncatedBio = this.truncate(this.artist.bio);
                        //this.activeTab = "overview";
                    }
                    this.loading = false;
                } else {
                    this.errors = true;
                    this.apiError = response.getApiErrorData();
                    this.loading = false;
                }
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
            spieldoseAPI.track.searchTracks(text, artist, '', false, this.pager.actualPage, this.pager.resultsPage, '', (response) => {
                if (response.status == 200) {
                    this.pager.actualPage = response.data.actualPage;
                    this.pager.totalPages = response.data.totalPages;
                    this.pager.totalResults = response.data.totalResults;
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.tracks = response.data.tracks;
                    } else {
                        this.tracks = [];
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
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
            spieldoseAPI.track.getAlbumTracks(album || null, artist || null, year || null, (response) => {
                this.player.currentPlaylist.empty();
                if (response.status == 200) {
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.player.tracks = response.data.tracks;
                        this.player.playback.play();
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
                this.loading = false;
            });
        },
        searchMusicBrainz(artistName) {
            window.open('https://musicbrainz.org/search?query=' + encodeURI(this.artist.name) + '&type=artist&limit=16&method=indexed');
        },
        overwriteMusicBrainzArtist(name, mbid) {
            this.loading = true;
            this.errors = false;
            spieldoseAPI.artist.overwriteMusicBrainz(name, mbid, (response) => {
                if (response.status == 200) {
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
                this.loading = false;
            });
        },
        clearMusicBrainzArtist: function (name, mbid) {
            this.loading = true;
            this.errors = false;
            spieldoseAPI.artist.clearMusicBrainz(name, mbid, (response) => {
                if (response.status == 200) {
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
                this.loading = false;
            });
        }
    }
}