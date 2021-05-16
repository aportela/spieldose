import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPlayer, mixinPagination, mixinAlbums, mixinLiveSearches } from '../mixins.js';
import { default as imageArtist } from './image-artist.js';
import { default as dashboardTopList } from './dashboard-toplist.js';
import { default as pagination } from './pagination';
import Chart from 'chart.js/auto';
import browseArtistHeader from './browse-artist-header.js';
import { default as album } from './album.js';

const template = function () {
    return `
        <div class="columns">
            <div class="column is-8">
                <div class="content" id="bio" v-if="artist.bio">
                <div v-html="artist.bio"></div>
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
                <table class="table is-unselectable is-clear-fix">
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
                <div id="similar">
                    <div class="is-clearfix">
                        <span class="title is-5 is-pulled-left">Similar to</span>
                        <span class="is-pulled-right">Show more <i class="fas fa-angle-right"></i></span>
                    </div>
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
    `;
};


export default {
    name: 'spieldose-browse-artist-overview',
    template: template(),
    props: [
        'artist'
    ],
    components: {
        'spieldose-album': album
    },
    methods: {
        onPlayTrack: function(track) {
            this.$player.playTracks([ track ]);
        },
        onToggleLoveTrack: function(track) {
            // TODO
            if (track.loved) {
            } else {
            }
        }
    }
}