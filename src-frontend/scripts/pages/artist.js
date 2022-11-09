import { mixinAPIError, mixinPlayer, mixinPagination, mixinAlbums, mixinLiveSearches } from '../mixins.js';
/*
import { default as dashboardTopList } from './dashboard-toplist.js';
import Chart from 'chart.js/auto';
*/

import { default as imageArtist } from '../vue-components/image-artist.js';
import { default as imageAlbum } from '../vue-components/image-album.js';

import { default as pagination } from '../vue-components/pagination';

import browseArtistHeader from '../vue-components/browse-artist-header.js';
import browseArtistOverview from '../vue-components/browse-artist-overview.js';
import browseArtistBiography from '../vue-components/browse-artist-biography.js';
import browseArtistAlbums from '../vue-components/browse-artist-albums.js';
import browseArtistSimilar from '../vue-components/browse-artist-similar.js';

const template = function () {
    return `
        <div>
            <div>
                <spieldose-browse-artist-header :artist="artist" :loading="loading"></spieldose-browse-artist-header>
                <div class="container is-fluid box mt-3">
                    <spieldose-browse-artist-overview v-if="currentTab == 'overview'" :artist="artist"></spieldose-browse-artist-overview>
                    <spieldose-browse-artist-biography v-if="currentTab == 'biography'" :artist="artist"></spieldose-browse-artist-biography>
                    <spieldose-browse-artist-albums v-if="currentTab == 'albums'" :artist="artist"></spieldose-browse-artist-albums>
                    <spieldose-browse-artist-similar v-if="currentTab == 'similarArtists'" :artist="artist"></spieldose-browse-artist-similar>
                </div>
            </div>
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
        },
        // TODO: duplicated in header
        currentTab: function () {
            let tab = null;
            switch (this.$route.name) {
                case 'artistBiography':
                    tab = 'biography';
                    break;
                case 'artistSimilarArtists':
                    tab = 'similarArtists';
                    break;
                case 'artistAlbums':
                    tab = 'albums';
                    break;
                case 'artistTracks':
                    tab = 'tracks';
                    break;
                case 'artistStats':
                    tab = 'stats'
                    break;
                default:
                    tab = 'overview';
                    break;
            }
            return (tab);
        }
    },
    components: {
        'spieldose-browse-artist-header': browseArtistHeader,
        'spieldose-browse-artist-overview': browseArtistOverview,
        'spieldose-browse-artist-biography': browseArtistBiography,
        'spieldose-browse-artist-albums': browseArtistAlbums,
        'spieldose-browse-artist-similar': browseArtistSimilar,
        //'spieldose-dashboard-toplist': dashboardTopList,
        'spieldose-pagination': pagination,
        'spieldose-image-artist': imageArtist,
        'spieldose-image-album': imageAlbum,
    },
    watch: {
        /*
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
        */
    },
    beforeRouteUpdate(to, from) {
        if (from.params.artist != to.params.artist) {
            this.getArtist(to.params.artist);
        }
    },
    created: function () {
        this.artist = {
            name: this.$route.params.name,
            //image: 'https://milladoiro.gal/wp-content/uploads/2016/05/milladoiro-historia4.png',
            albums: [
                {
                    name: 'Aires da terra',
                    year: 1999,
                    image: 'http://localhost:8080/api2/track/thumbnail/small/23682ab25db6ce676a2727f2930bb80e3b135f64'
                }
            ],
            wikipedia: null
        };
        this.getArtist(this.artist.name);
        /*
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
        */
    },
    mounted: function () {
        if (this.chart) {
            this.chart.destroy();
        }
        /*
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
        */
    },
    methods: {
        onChangeTab: function (event) {
            this.activeTab = event.tab;
        },
        onPaginationChanged: function (currentPage) {
            this.$router.push({ name: 'artistTracksPaged', params: { page: currentPage } });
        },
        getArtist: function (artist) {
            this.loading = true;
            this.errors = false;
            this.$api.artist.get(artist).then(success => {
                this.loading = false;
                if (success.data.MusicBrainz.relations) {
                    success.data.MusicBrainz.relations.forEach((relation) => {
                        if (relation.type == 'image') {
                            if (relation.url.resource.startsWith('https://commons.wikimedia.org/wiki/File:')) {
                                this.artist.image = 'https://commons.wikimedia.org/w/thumb.php?f=' + relation.url.resource.replace(/https:\/\/commons.wikimedia.org\/wiki\/File:/, '') + '&w=400';
                            } else {
                                this.artist.image = relation.url.resource;
                            }
                        } else if (relation.type == 'wikidata') {
                            //console.log(relation.url.resource);
                        }
                    });
                }
                this.artist.wikipedia = Object.values(success.data.Wikipedia.query.pages)[0]
            }).catch(error => {
                this.loading = false;
                // TODO
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