var playLists = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Playlists</p>
        <div class="tabs is-centered">
            <ul>
                <li v-bind:class="{ 'is-active' : tab == 0 }"><a v-on:click.prevent="changeTab(0)" href="#">Now playing</a></li>
                <li v-bind:class="{ 'is-active' : tab == 1 }"><a v-on:click.prevent="changeTab(1)" href="#">Playlists</a></li>
                <li v-bind:class="{ 'is-active' : tab == 2 }"><a v-on:click.prevent="changeTab(2)" href="#">Add new playlist</a></li>
            </ul>
        </div>
        <div v-show="tab == 0">
            <p class="title is-1 has-text-centered">Current playlist</p>

            <div v-if="! currentPlaylistId" class="field has-addons">
                <div class="control has-icons-left is-expanded">
                    <input v-model="currentPlaylistName" class="input" type="text" placeholder="Playlist name" required :disabled="savingPlaylist">
                    <span class="icon is-small is-left">
                        <i class="fa fa-list-alt"></i>
                    </span>
                </div>
                <div class="control">
                    <a class="button is-success" v-bind:class="savingPlaylist ? 'is-loading': ''"  v-on:click.prevent="savePlayList();" :disabled="! currentPlaylistName || savingPlaylist">
                        <span class="icon is-small">
                        <i class="fa fa-check"></i>
                        </span>
                        <span>Save as new playlist</span>
                    </a>
                </div>
            </div>

            <div v-else="! currentPlaylistId" class="field has-addons">
                <div class="control has-icons-left is-expanded">
                    <input v-model="currentPlaylistName" class="input" type="text" placeholder="Playlist name" required :disabled="savingPlaylist">
                    <span class="icon is-small is-left">
                        <i class="fa fa-list-alt"></i>
                    </span>
                </div>
                <div class="control">
                    <a class="button is-success" v-bind:class="savingPlaylist ? 'is-loading': ''" v-on:click.prevent="savePlayList();" :disabled="! currentPlaylistName || savingPlaylist">
                        <span class="icon is-small">
                        <i class="fa fa-check"></i>
                        </span>
                        <span>Save playlist</span>
                    </a>
                </div>
            </div>

            <div class="field is-grouped">
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.loadRandomTracks(32);" :disabled="playerData.loading">
                        <span class="icon is-small">
                            <i v-if="playerData.loading" class="fa fa-cog fa-spin fa-fw"></i>
                            <i v-else class="fa fa-clone"></i>
                        </span>
                        <span>load random playlist</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.emptyPlayList();">
                        <span class="icon is-small">
                            <i class="fa fa-eraser"></i>
                        </span>
                        <span>clear playlist</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.toggleRepeatMode();" v-bind:class="playerData.repeatTracksMode != 'none' ? 'is-primary': ''">
                        <span class="icon is-small">
                            <i class="fa fa-refresh"></i>
                        </span>
                        <span>repeat: {{ playerData.repeatTracksMode }}</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.toggleShuffleMode();" v-bind:class="playerData.shuffleTracks ? 'is-primary': ''">
                        <span class="icon is-small">
                            <i class="fa fa-random"></i>
                        </span>
                        <span>shuffle: {{ playerData.shuffleTracks ? "true": "false" }}</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.playPreviousTrack();">
                        <span class="icon is-small">
                            <i class="fa fa-backward"></i>
                        </span>
                        <span>previous</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.playNextTrack();">
                        <span class="icon is-small">
                            <i class="fa fa-forward"></i>
                        </span>
                        <span>next</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-if="playerData.isStopped" v-on:click.prevent="playerData.play();">
                        <span class="icon is-small">
                            <i class="fa fa-play"></i>
                        </span>
                        <span>play</span>
                    </a>
                    <a class="button is-light is-primary" v-else-if="playerData.isPlaying" v-on:click.prevent="playerData.pause();">
                        <span class="icon is-small">
                            <i class="fa fa-play"></i>
                        </span>
                        <span>play</span>
                    </a>
                    <a class="button is-light is-primary" v-else-if="playerData.isPaused" v-on:click.prevent="playerData.resume();">
                        <span class="icon is-small">
                            <i class="fa fa-pause"></i>
                        </span>
                        <span>resume</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-bind:class="playerData.isStopped ? 'is-primary': ''" v-on:click.prevent="playerData.stop();">
                        <span class="icon is-small">
                            <i class="fa fa-stop"></i>
                        </span>
                        <span>stop</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light is-primary" v-if="playerData.hasTracks() && playerData.tracks[playerData.actualTrackIdx].loved == '1'" v-on:click.prevent="playerData.unLoveActualTrack();" :disabled="playerData.loading">
                        <span class="icon is-small">
                            <i class="fa fa-heart"></i>
                        </span>
                        <span>love</span>
                    </a>
                    <a class="button is-light" v-else v-on:click.prevent="playerData.loveActualTrack();" :disabled="playerData.loading">
                        <span class="icon is-small">
                            <i class="fa fa-heart"></i>
                        </span>
                        <span>love</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.downloadActualTrack();">
                        <span class="icon is-small">
                            <i class="fa fa-save"></i>
                        </span>
                        <span>download</span>
                    </a>
                </p>
            </div>
            <table id="playlist-now-playing" class="table is-bordered is-striped is-narrow is-fullwidth" v-show="tab == 0">
                <thead>
                        <tr class="is-unselectable">
                            <th>Track</th>
                            <th>Artist</th>
                            <th>Album</th>
                            <th>Genre</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                </thead>
                <tbody>
                    <tr v-for="track, i in playerData.tracks" v-bind:class="playerData.actualTrack && playerData.actualTrackIdx == i ? 'is-selected': ''">
                        <td>
                            <i v-if="iconAction(i) == 'play'" title="play this track" class="fa fa-play cursor-pointer" aria-hidden="true" v-on:click="playerData.playAtIdx(i);"></i>
                            <i v-else-if="iconAction(i) == 'none'" title="now playing, click to pause" class="fa fa-headphones cursor-pointer" aria-hidden="true" v-on:click="playerData.pause();"></i>
                            <i v-else-if="iconAction(i) == 'unPause'" title="paused, click to resume" class="fa fa-pause cursor-pointer" aria-hidden="true" v-on:click="playerData.resume();"></i>
                            <span> {{ track.title}}</span>
                        </td>
                        <td><a v-if="track.artist" v-on:click.prevent="$router.push({ name: 'artist', params: { artist: track.artist } })" v-bind:title="'click to open artist section'">{{ track.artist }}</a></td>
                        <td><span>{{ track.album }}</span></td>
                        <td><span>{{ track.genre }}</span></td>
                        <td><span>{{ track.year }}</span></td>
                        <td class="is-unselectable">
                            <i title="move up this track on playlist" class="fa fa-caret-up cursor-pointer" aria-hidden="true" v-on:click="playerData.moveUpIdx(i);"></i>
                            <i title="move down this track playlist" class="fa fa-caret-down cursor-pointer" aria-hidden="true" v-on:click="playerData.moveDownIdx(i);"></i>
                            <i title="remove this track from playlist" class="fa fa-remove cursor-pointer" aria-hidden="true" v-on:click="playerData.removeAtIdx(i); $forceUpdate();"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-show="tab == 1">
            <p class="title is-1 has-text-centered">My playlists</i></p>
            <div class="field">
                <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-focus v-model="nameFilter" type="text" placeholder="search playlist name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <span class="icon is-small is-left">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
            </div>
            <spieldose-pagination v-bind:data="pager" v-show="playlists.length > 0"></spieldose-pagination>
            <div class="playlist-item box" v-for="playlist in playlists" v-show="! loading">
                <p class="playlist-info has-text-centered">
                    <strong>“{{ playlist.name }}”</strong>
                    <br>13 tracks
                </p>
            </div>
            <div class="is-clearfix"></div>
        </div>
        <div v-show="tab == 2">
            <h2>TODO</h2>
        </div>
        <spieldose-api-error-component v-if="errors" v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-playlists', {
        template: template(),
        data: function () {
            return ({
                tab: 0,
                loading: false,
                errors: false,
                apiError: null,
                nameFilter: null,
                playlists: [],
                currentPlaylistId: null,
                currentPlaylistName: null,
                savingPlaylist: false,
                pager: getPager(),
                playerData: sharedPlayerData,
            });
        }, directives: {
            focus: {
                update: function (el) {
                    el.focus();
                }
            }
        },
        created: function () {
            var self = this;
            this.pager.refresh = function () {
                //self.$router.push({ name: 'playListsPaged', params: { page: self.pager.actualPage } });
            }
            if (this.$route.params.page) {
                //self.pager.actualPage = parseInt(this.$route.params.page);
            }
        },
        methods: {
            iconAction: function (index) {
                if (this.playerData.isPaused && this.playerData.actualTrackIdx == index) {
                    return ('unPause');
                } else {
                    if (this.playerData.isPlaying && this.playerData.actualTrackIdx == index) {
                        return ('none');
                    } else {
                        return ('play');
                    }
                }
            },
            changeTab: function (tab) {
                this.tab = tab;
                if (this.tab == 1) {
                    this.search();
                }
            },
            abortInstantSearch: function () {
                this.nameFilter = null;
            },
            instantSearch: function () {
                var self = this;
                if (self.timeout) {
                    clearTimeout(self.timeout);
                }
                self.timeout = setTimeout(function () {
                    self.pager.actualPage = 1;
                    self.search();
                }, 256);
            },
            search: function () {
                var self = this;
                this.loading = true;
                spieldoseAPI.searchPlaylists(self.nameFilter, self.pager.actualPage, self.pager.resultsPage, function (response) {
                    if (response.ok) {
                        self.pager.actualPage = response.body.pagination.actualPage;
                        self.pager.totalPages = response.body.pagination.totalPages;
                        self.pager.totalResults = response.body.pagination.totalResults;
                        if (response.body.playlists && response.body.playlists.length > 0) {
                            self.playlists = response.body.playlists;
                        } else {
                            self.playlists = [];
                        }
                        self.loading = false;
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                        self.loading = false;
                    }
                });

            },
            savePlayList: function() {
                var self = this;
                var trackIds = [];
                self.savingPlaylist = true;
                for (let i = 0; i < this.playerData.tracks.length; i++) {
                    trackIds.push(this.playerData.tracks[i].id);
                }
                if (this.currentPlaylistId) {
                    spieldoseAPI.updatePlaylist(this.currentPlaylistId, this.currentPlaylistName, trackIds, function(response) {
                        self.savingPlaylist = false;
                        if (response.ok) {
                        } else {
                        }
                    });
                } else {
                    spieldoseAPI.addPlaylist(this.currentPlaylistName, trackIds, function(response) {
                        self.savingPlaylist = false;
                        if (response.ok) {
                            self.currentPlaylistId = response.body.playlist.id;
                        } else {
                        }
                    });
                }
            }
        }
    });

    return (module);
})();
