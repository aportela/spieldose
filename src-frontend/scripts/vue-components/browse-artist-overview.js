import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPlayer, mixinPagination, mixinAlbums, mixinLiveSearches } from '../mixins.js';
import { default as imageArtist } from './image-artist.js';
import { default as dashboardTopList } from './dashboard-toplist.js';
import { default as pagination } from './pagination';
import { Chart, registerables } from 'chartjs';
import browseArtistHeader from './browse-artist-header.js';
import { default as album } from './album.js';

const template = function () {
    return `
        <div class="columns">
            <div class="column is-8">
                <div class="content" id="bio" v-if="biography">
                    <div v-html="biography"></div>
                    <p class="read-more">
                        <router-link :class="'has-text-dark'" :to="{ name: 'artistBiography', params: $route.params }">Read more <i class="fas fa-angle-right"></i></router-link>
                    </p>
                </div>
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
                <table class="table is-unselectable is-hoverable is-clear-fix">
                    <tbody>
                        <tr v-for="track, idx in artist.topTracks">
                            <td class="is-vcentered">{{ idx + 1 }}</td>
                            <td class="is-vcentered"><span class="icon cursor-pointer" @click.prevent="onPlayTrack(track)"><i class="fas fa-play"></i></span></td>
                            <td class="is-vcentered"><span class="icon cursor-pointer" :class="{ 'btn-active': track.loved }" @click.prevent="onToggleLoveTrack(track)"><i class="fas fa-heart"></i></span></td>
                            <td class="is-vcentered" style="max-width: 24em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">{{ track.title }}</td>
                            <td class="is-vcentered">
                                <figure class="image is-32x32" v-if="track.image">
                                    <spieldose-image-album :src="track.image"></spieldose-image-album>
                                </figure>
                            </td>
                            <td class="is-vcentered" style="max-width: 24em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">{{ track.album }} <span v-if="track.year">({{ track.year }})</span></td>
                            <td class="is-vcentered">
                                <div style="min-width: 192px; max-width: 256px;">
                                    <span class="has-text-dark has-background-light pl-2 pr-2 pt-1 pb-1" style="display: block: width: 90%;">{{ track.total }} plays </span>
                                </div>
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
                    <spieldose-album v-for="album, i in artist.albums" :key="album.name+album.artist+album.year" v-show="! loading && i < 4" :album="album"></spieldose-album>
                </div>
                <div class="is-clearfix">
                    <router-link :class="'is-pulled-right has-text-dark'" :to="{ name: 'artistAlbums', params: $route.params }">View all albums <i class="fas fa-angle-right"></i></router-link>
                </div>
            </div>
            <div class="column is-4">
                <div id="similar" v-if="hasSimilar">
                    <div class="is-clearfix">
                        <span class="title is-5 is-pulled-left">Similar to</span>
                        <span class="is-pulled-right">Show more <i class="fas fa-angle-right"></i></span>
                    </div>
                    <div class="columns is-size-6">
                        <div class="column is-4 has-text-grey is-centered" v-for="similar, idx in artist.similarArtists" :key="similar.name" v-show="idx < 3">
                            <router-link :title="$t('commonLabels.navigateToArtistPage')" :to="{ name: 'artist', params: { artist: similar.name }}" :class="'has-text-grey'">
                                <figure class="image is-96x96" style="margin: 0px auto;">
                                    <spieldose-image-artist :src="similar.image" :extraClass="'is-rounded'"></spieldose-image-artist>
                                </figure>
                                <p class="has-text-centered">{{ similar.name }}</p>
                            </router-link>
                        </div>
                    </div>
                </div>
                <hr v-if="hasSimilar">
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
                <canvas width="100%" style="max-height: 200px;" id="play-stats"></canvas>
            </div>
        </div>
    `;
};


export default {
    name: 'spieldose-browse-artist-overview',
    template: template(),
    data: function () {
        return ({
            chart: null
        });
    },
    props: [
        'artist'
    ],
    computed: {
        biography: function () {
            return ((this.artist && this.artist.lastFM && this.artist.lastFM.artist && this.artist.lastFM.artist.bio && this.artist.lastFM.artist.bio.content) ? this.artist.lastFM.artist.bio.content.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2') : null);
        },
        hasSimilar: function () {
            return (this.artist.similarArtists && this.artist.similarArtists.length > 0);
        }
    },
    components: {
        'spieldose-album': album,
        'spieldose-image-artist': imageArtist
    },
    created: function () {
        Chart.register(...registerables);
    },
    mounted: function () {
        if (this.chart) {
            this.chart.destroy();
        }
        const commonChartOptions = {
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            scales: {
                // https://stackoverflow.com/a/59353503
                x: {
                    offset: true
                },
                // https://stackoverflow.com/a/71239566
                y: {
                    beginAtZero: true,
                    grace: '1%',
                    ticks: {
                        precision: 0
                    }
                }
            }
        };
        const monthNames = [
            this.$t('dashboard.labels.january'),
            this.$t('dashboard.labels.february'),
            this.$t('dashboard.labels.march'),
            this.$t('dashboard.labels.april'),
            this.$t('dashboard.labels.may'),
            this.$t('dashboard.labels.june'),
            this.$t('dashboard.labels.july'),
            this.$t('dashboard.labels.august'),
            this.$t('dashboard.labels.september'),
            this.$t('dashboard.labels.october'),
            this.$t('dashboard.labels.november'),
            this.$t('dashboard.labels.december')
        ];
        let data = [65, 59, 80, 81, 56, 55, 40, 65, 59, 80, 81, 56, 55];
        this.chart = new Chart(document.getElementById('play-stats'), {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: [
                    {
                        'label': this.$t('dashboard.labels.playStatsByMonth'),
                        'data': data,
                        'fill': true,
                        'borderColor': 'rgb(211, 3, 32)',
                        'backgroundColor': 'rgba(211, 3, 32, 0.2)',
                        'lineTension': 0.3
                    }
                ]
            }, options: commonChartOptions
        });
    },
    methods: {
        onPlayTrack: function (track) {
            this.$player.playTracks([track]);
        },
        onToggleLoveTrack: function (track) {
            // TODO
            if (track.loved) {
            } else {
            }
        }
    }
}