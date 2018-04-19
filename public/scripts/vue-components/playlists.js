var nowPlaying = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Now playing</p>
        <div v-if="! errors">
            <div v-if="! currentPlaylistName" class="field has-addons">
                <div class="control has-icons-left is-expanded">
                    <input v-model.trim="currentPlaylistName" class="input" type="text" placeholder="Playlist name" required :disabled="savingPlaylist">
                    <span class="icon is-small is-left">
                        <i class="fas fa-list-alt"></i>
                    </span>
                </div>
                <div class="control">
                    <a class="button is-success" v-bind:class="savingPlaylist ? 'is-loading': ''"  v-on:click.prevent="savePlayList();" :disabled="! currentPlaylistName || savingPlaylist">
                        <span class="icon is-small">
                        <i class="fas fa-check"></i>
                        </span>
                        <span>Save as new playlist</span>
                    </a>
                </div>
            </div>

            <div v-else class="field has-addons">
                <div class="control has-icons-left is-expanded">
                    <input v-model.trim="currentPlaylistName" class="input" type="text" placeholder="Playlist name" required :disabled="savingPlaylist">
                    <span class="icon is-small is-left">
                        <i class="fas fa-list-alt"></i>
                    </span>
                </div>
                <div class="control">
                    <a class="button is-success" v-bind:class="savingPlaylist ? 'is-loading': ''" v-on:click.prevent="savePlayList();" :disabled="! currentPlaylistName || savingPlaylist">
                        <span class="icon is-small">
                        <i class="fas fa-check"></i>
                        </span>
                        <span>Save playlist</span>
                    </a>
                </div>
            </div>

            <div class="field is-grouped">
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="loadRandom();" :disabled="playerData.loading">
                        <span class="icon is-small">
                            <i v-if="playerData.loading" class="fas fa-cog fa-spin fa-fw"></i>
                            <i v-else class="fas fa-clone"></i>
                        </span>
                        <span>load random playlist</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.emptyPlayList();">
                        <span class="icon is-small">
                            <i class="fas fa-eraser"></i>
                        </span>
                        <span>clear playlist</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.toggleRepeatMode();" v-bind:class="playerData.repeatTracksMode != 'none' ? 'is-primary': ''">
                        <span class="icon is-small">
                            <i class="fas fa-redo"></i>
                        </span>
                        <span>repeat: {{ playerData.repeatTracksMode }}</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.toggleShuffleMode();" v-bind:class="playerData.shuffleTracks ? 'is-primary': ''">
                        <span class="icon is-small">
                            <i class="fas fa-random"></i>
                        </span>
                        <span>shuffle: {{ playerData.shuffleTracks ? "true": "false" }}</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.playPreviousTrack();">
                        <span class="icon is-small">
                            <i class="fas fa-backward"></i>
                        </span>
                        <span>previous</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.playNextTrack();">
                        <span class="icon is-small">
                            <i class="fas fa-forward"></i>
                        </span>
                        <span>next</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-if="playerData.isStopped" v-on:click.prevent="playerData.play();">
                        <span class="icon is-small">
                            <i class="fas fa-play"></i>
                        </span>
                        <span>play</span>
                    </a>
                    <a class="button is-light is-primary" v-else-if="playerData.isPaused" v-on:click.prevent="playerData.resume();">
                        <span class="icon is-small">
                            <i class="fas fa-play"></i>
                        </span>
                        <span>resume</span>
                    </a>
                    <a class="button is-light is-primary" v-else-if="playerData.isPlaying" v-on:click.prevent="playerData.pause();">
                        <span class="icon is-small">
                            <i class="fas fa-pause"></i>
                        </span>
                        <span>pause</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-bind:class="playerData.isStopped ? 'is-primary': ''" v-on:click.prevent="playerData.stop();">
                        <span class="icon is-small">
                            <i class="fas fa-stop"></i>
                        </span>
                        <span>stop</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light is-primary" v-if="playerData.hasTracks() && playerData.tracks[playerData.actualTrackIdx].loved == '1'" v-on:click.prevent="playerData.unLoveActualTrack();" :disabled="playerData.loading">
                        <span class="icon is-small">
                            <i class="fas fa-heart"></i>
                        </span>
                        <span>love</span>
                    </a>
                    <a class="button is-light" v-else v-on:click.prevent="playerData.loveActualTrack();" :disabled="playerData.loading">
                        <span class="icon is-small">
                            <i class="fas fa-heart"></i>
                        </span>
                        <span>love</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="playerData.downloadActualTrack();">
                        <span class="icon is-small">
                            <i class="fas fa-save"></i>
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
                            <i v-if="iconAction(i) == 'play'" title="play this track" class="fas fa-play cursor-pointer" aria-hidden="true" v-on:click="playerData.playAtIdx(i);"></i>
                            <i v-else-if="iconAction(i) == 'none'" title="now playing, click to pause" class="fas fa-headphones cursor-pointer" aria-hidden="true" v-on:click="playerData.pause();"></i>
                            <i v-else-if="iconAction(i) == 'unPause'" title="paused, click to resume" class="fas fa-pause cursor-pointer" aria-hidden="true" v-on:click="playerData.resume();"></i>
                            <span> {{ track.title}}</span>
                        </td>
                        <td><a v-if="track.artist" v-on:click.prevent="$router.push({ name: 'artist', params: { artist: track.artist } })" v-bind:title="'click to open artist section'">{{ track.artist }}</a></td>
                        <td><span>{{ track.album }}</span></td>
                        <td><span>{{ track.genre }}</span></td>
                        <td><span>{{ track.year }}</span></td>
                        <td class="is-unselectable">
                            <i title="move up this track on playlist" class="fas fa-caret-up cursor-pointer" aria-hidden="true" v-on:click="playerData.moveUpIdx(i);"></i>
                            <i title="move down this track playlist" class="fas fa-caret-down cursor-pointer" aria-hidden="true" v-on:click="playerData.moveDownIdx(i);"></i>
                            <i title="remove this track from playlist" class="fas fa-times cursor-pointer" aria-hidden="true" v-on:click="playerData.removeAtIdx(i); $forceUpdate();"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <spieldose-api-error-component v-if="errors" v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-nowplaying', {
        template: template(),
        data: function () {
            return ({
                tab: 0,
                loading: false,
                errors: false,
                apiError: null,
                nameFilter: null,
                playlists: [],
                savingPlaylist: false,
                pager: getPager(),
                currentPlaylistName: null,
                playerData: sharedPlayerData,
            });
        }, directives: {
            focus: {
                inserted: function(el) {
                    el.focus();
                },
                update: function (el) {
                    el.focus();
                }
            }
        },
        watch: {
            playerCurrentPlayListName: function(newValue) {
                if (newValue) {
                    this.currentPlaylistName = newValue;
                } else {
                    this.currentPlaylistName = null;
                }
            }
        },
        computed: {
            playerCurrentPlayListName: function() {
                return(this.playerData.currentPlaylistName);
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
            loadRandom: function() {
                this.playerData.unsetCurrentPlayList();
                this.playerData.loadRandomTracks(initialState.defaultResultsPage);
            },
            savePlayList: function () {
                var self = this;
                var trackIds = [];
                self.savingPlaylist = true;
                for (let i = 0; i < this.playerData.tracks.length; i++) {
                    trackIds.push(this.playerData.tracks[i].id);
                }
                if (this.playerData.hasCurrentPlayList()) {
                    spieldoseAPI.updatePlaylist(this.playerData.currentPlaylistId, this.currentPlaylistName, trackIds, function (response) {
                        self.savingPlaylist = false;
                        if (response.ok) {
                            self.playerData.setCurrentPlayList(response.body.playlist.id, response.body.playlist.name);
                        } else {
                            self.errors = true;
                            self.apiError = response.getApiErrorData();
                            self.loading = false;
                        }
                    });
                } else {
                    spieldoseAPI.addPlaylist(this.currentPlaylistName, trackIds, function (response) {
                        self.savingPlaylist = false;
                        if (response.ok) {
                            self.playerData.setCurrentPlayList(response.body.playlist.id, response.body.playlist.name);
                        } else {
                            self.errors = true;
                            self.apiError = response.getApiErrorData();
                            self.loading = false;
                        }
                    });
                }
            }
        }
    });

    return (module);
})();
