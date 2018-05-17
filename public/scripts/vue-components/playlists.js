let nowPlaying = (function () {
    "use strict";

    const template = function () {
        return `
            <div class="container is-fluid box is-marginless">
                <p class="title is-1 has-text-centered">Now playing</p>
                <div v-if="! hasAPIErrors">
                    <div class="field has-addons">
                        <div class="control has-icons-left is-expanded">
                            <input class="input" type="text" placeholder="type playlist name" required v-bind:disabled="savingPlaylist" v-model.trim="currentPlaylistName">
                            <span class="icon is-small is-left">
                                <i class="fas fa-list-alt"></i>
                            </span>
                        </div>
                        <div class="control">
                            <a class="button is-success" v-bind:class="{ 'is-loading': savingPlaylist }" v-bind:disabled="isSavePlaylistDisabled" v-on:click.prevent="savePlayList();">
                                <span class="icon is-small">
                                <i class="fas fa-check"></i>
                                </span>
                                <span>Save playlist</span>
                            </a>
                        </div>
                        <div class="control" v-if="isPlaylisted">
                            <a class="button is-info" v-bind:class="{ 'is-loading': savingPlaylist }" v-bind:disabled="isSavePlaylistDisabled" v-on:click.prevent="unsetPlaylist();">
                                <span class="icon is-small">
                                <i class="fas fa-check-square"></i>
                                </span>
                                <span>Unset playlist</span>
                            </a>
                        </div>
                    </div>

                    <div class="buttons">
                        <a class="button is-light" v-bind:disabled="playerData.loading" v-on:click.prevent="loadRandom();">
                            <span class="icon is-small">
                                <i v-if="playerData.loading" class="fas fa-cog fa-spin fa-fw"></i>
                                <i v-else class="fas fa-clone"></i>
                            </span>
                            <span class="is-hidden-touch">load random playlist</span>
                        </a>
                        <a class="button is-light" v-on:click.prevent="playerData.currentPlaylist.empty();">
                            <span class="icon is-small">
                                <i class="fas fa-eraser"></i>
                            </span>
                            <span class="is-hidden-touch">clear playlist</span>
                        </a>
                        <a class="button is-light" v-bind:class="{ 'is-primary': isRepeatActive }" v-on:click.prevent="playerData.playback.toggleRepeatMode();">
                            <span class="icon is-small">
                                <i class="fas fa-redo"></i>
                            </span>
                            <span class="is-hidden-touch">repeat: {{ playerData.repeatTracksMode }}</span>
                        </a>
                        <a class="button is-light" v-on:click.prevent="playerData.currentPlaylist.shuffle();">
                            <span class="icon is-small">
                                <i class="fas fa-random"></i>
                            </span>
                            <span class="is-hidden-touch">shuffle playlist</span>
                        </a>
                        <a class="button is-light" v-on:click.prevent="playerData.currentPlaylist.playPrevious();">
                            <span class="icon is-small">
                                <i class="fas fa-backward"></i>
                            </span>
                            <span class="is-hidden-touch">previous</span>
                        </a>
                        <a class="button is-light" v-on:click.prevent="playerData.currentPlaylist.playNext();">
                            <span class="icon is-small">
                                <i class="fas fa-forward"></i>
                            </span>
                            <span class="is-hidden-touch">next</span>
                        </a>
                        <a class="button is-light" v-if="playerData.isStopped" v-on:click.prevent="playerData.playback.play();">
                            <span class="icon is-small">
                                <i class="fas fa-play"></i>
                            </span>
                            <span class="is-hidden-touch">play</span>
                        </a>
                        <a class="button is-light is-primary" v-else-if="playerData.isPaused" v-on:click.prevent="playerData.playback.resume();">
                            <span class="icon is-small">
                                <i class="fas fa-play"></i>
                            </span>
                            <span class="is-hidden-touch">resume</span>
                        </a>
                        <a class="button is-light is-primary" v-else-if="playerData.isPlaying" v-on:click.prevent="playerData.playback.pause();">
                            <span class="icon is-small">
                                <i class="fas fa-pause"></i>
                            </span>
                            <span class="is-hidden-touch">pause</span>
                        </a>
                        <a class="button is-light" v-bind:class="{ 'is-primary': playerData.isStopped }" v-on:click.prevent="playerData.playback.stop();">
                            <span class="icon is-small">
                                <i class="fas fa-stop"></i>
                            </span>
                            <span class="is-hidden-touch">stop</span>
                        </a>
                        <a class="button is-light is-primary" v-if="isLovedActive" v-bind:disabled="playerData.loading" v-on:click.prevent="playerData.playerData.currentTrack.unSetLoved();">
                            <span class="icon is-small">
                                <i class="fas fa-heart"></i>
                            </span>
                            <span class="is-hidden-touch">love</span>
                        </a>
                        <a class="button is-light" v-else v-bind:disabled="playerData.loading" v-on:click.prevent="playerData.playerData.currentTrack.setLoved();">
                            <span class="icon is-small">
                                <i class="fas fa-heart"></i>
                            </span>
                            <span class="is-hidden-touch">love</span>
                        </a>
                        <a class="button is-light" v-on:click.prevent="playerData.currentTrack.download();">
                            <span class="icon is-small">
                                <i class="fas fa-save"></i>
                            </span>
                            <span class="is-hidden-touch">download</span>
                        </a>
                    </div>

                    <table id="playlist-now-playing" class="table is-bordered is-striped is-narrow is-fullwidth is-unselectable">
                        <thead>
                            <tr>
                                <th>Track</th>
                                <th>Artist</th>
                                <th>Album</th>
                                <th>Genre</th>
                                <th>Year</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-bind:class="{ 'is-selected': playerData.currentTrack.index == i }" v-for="track, i in playerData.tracks" v-bind:key="track.id">
                                <td>
                                    <i class="fas fa-play cursor-pointer" title="play this track" aria-hidden="true" v-if="iconAction(i) == 'play'" v-on:click="playerData.currentPlaylist.playAtIdx(i);"></i>
                                    <i class="fas fa-headphones cursor-pointer" title="now playing, click to pause" aria-hidden="true" v-else-if="iconAction(i) == 'none'" v-on:click="playerData.playback.pause();"></i>
                                    <i class="fas fa-pause cursor-pointer" title="paused, click to resume" aria-hidden="true" v-else-if="iconAction(i) == 'unPause'" v-on:click="playerData.playback.resume();"></i>
                                    <span>{{ track.title}}</span>
                                </td>
                                <td><a title="click to open artist section" v-if="track.artist" v-on:click.prevent="navigateToArtistPage(track.artist);">{{ track.artist }}</a></td>
                                <td><span>{{ track.album }}</span></td>
                                <td><span>{{ track.genre }}</span></td>
                                <td><span>{{ track.year }}</span></td>
                                <td>
                                    <i class="fas fa-caret-up cursor-pointer" title="move up this track on playlist"  aria-hidden="true" v-on:click="playerData.currentPlaylist.moveItemUp(i);"></i>
                                    <i class="fas fa-caret-down cursor-pointer" title="move down this track playlist" aria-hidden="true" v-on:click="playerData.moveItemDown(i);"></i>
                                    <i class="fas fa-times cursor-pointer" title="remove this track from playlist"  aria-hidden="true" v-on:click="playerData.removeItem(i); $forceUpdate();"></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
            </div>
        `;
    };

    let module = Vue.component('spieldose-nowplaying', {
        template: template(),
        mixins: [
            mixinAPIError, mixinPlayer, mixinNavigation, mixinPlayer
        ],
        data: function () {
            return ({
                loading: false,
                savingPlaylist: false,
                currentPlaylistName: null
            });
        },
        created: function () {
            this.currentPlaylistName = this.playerData.currentPlaylist.name;
        },
        computed: {
            isPlaylisted: function() {
                if (this.playerData.currentPlaylist.name) {
                    return(true);
                } else {
                    return(false);
                }
            },
            isLovedActive: function () {
                return (this.playerData.currentTrack.track && this.playerData.currentTrack.track == '1');
            },
            isRepeatActive: function () {
                return (this.playerData.repeatTracksMode != 'none');
            },
            isSavePlaylistDisabled: function () {
                return (!this.currentPlaylistName || this.savingPlaylist);
            }
        },
        methods: {
            iconAction: function (index) {
                if (this.playerData.isPaused && this.playerData.currentTrack.index == index) {
                    return ('unPause');
                } else {
                    if (this.playerData.isPlaying && this.playerData.currentTrack.index == index) {
                        return ('none');
                    } else {
                        return ('play');
                    }
                }
            },
            unsetPlaylist: function() {
                this.playerData.currentPlaylist.unset();
                this.currentPlaylistName = null;
            },
            loadRandom: function () {
                this.playerData.currentPlaylist.loadRandomTracks(initialState.defaultResultsPage);
            },
            savePlayList: function () {
                let self = this;
                self.loading = true;
                self.clearAPIErrors();
                let trackIds = [];
                self.savingPlaylist = true;
                for (let i = 0; i < this.playerData.tracks.length; i++) {
                    trackIds.push(this.playerData.tracks[i].id);
                }
                if (this.playerData.currentPlaylist.isSet()) {
                    spieldoseAPI.playlist.update(this.playerData.currentPlaylist.id, this.currentPlaylistName, trackIds, function (response) {
                        self.savingPlaylist = false;
                        if (response.ok) {
                            self.playerData.currentPlaylist.set(response.body.playlist.id, response.body.playlist.name);
                        } else {
                            self.setAPIError(response.getApiErrorData());
                        }
                        self.loading = false;
                    });
                } else {
                    spieldoseAPI.playlist.add(this.currentPlaylistName, trackIds, function (response) {
                        self.savingPlaylist = false;
                        if (response.ok) {
                            self.playerData.currentPlaylist.set(response.body.playlist.id, response.body.playlist.name);
                        } else {
                            self.setAPIError(response.getApiErrorData());
                        }
                        self.loading = false;
                    });
                }
            }
        }
    });

    return (module);
})();
