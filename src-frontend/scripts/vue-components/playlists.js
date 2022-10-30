import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinNavigation } from '../mixins.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
            <p class="title is-1 has-text-centered">{{ $t('currentPlaylist.labels.sectionName') }}</p>
            <div v-if="! hasAPIErrors">
                <div class="field has-addons">
                    <div class="control has-icons-left is-expanded">
                        <input class="input" type="text" v-bind:placeholder="$t('currentPlaylist.inputs.playlistNamePlaceholder')" required v-bind:disabled="savingPlaylist" v-model.trim="currentPlaylistName">
                        <span class="icon is-small is-left">
                            <i class="fas fa-list-alt"></i>
                        </span>
                    </div>
                    <div class="control">
                        <a class="button is-success" v-bind:class="{ 'is-loading': savingPlaylist }" v-bind:disabled="isSavePlaylistDisabled" v-on:click.prevent="savePlayList();">
                            <span class="icon is-small">
                            <i class="fas fa-check"></i>
                            </span>
                            <span>{{ $t('currentPlaylist.buttons.savePlaylist') }}</span>
                        </a>
                    </div>
                    <div class="control" v-if="isPlaylisted">
                        <a class="button is-info" v-bind:class="{ 'is-loading': savingPlaylist }" v-bind:disabled="isSavePlaylistDisabled" v-on:click.prevent="unsetPlaylist();">
                            <span class="icon is-small">
                            <i class="fas fa-check-square"></i>
                            </span>
                            <span>{{ $t('currentPlaylist.buttons.unsetPlaylist') }}</span>
                        </a>
                    </div>
                </div>

                <div class="buttons">
                    <a class="button is-light" v-bind:disabled2="$audioplayer.loading" v-on:click.prevent="loadRandom();">
                        <span class="icon is-small">
                            <i v-if="$audioplayer.loading" class="fas fa-cog fa-spin fa-fw"></i>
                            <i v-else class="fas fa-clone fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.loadRandom') }}</span>
                    </a>
                    <a class="button is-light" v-on:click.prevent="$audioplayer.currentPlaylist.empty();">
                        <span class="icon is-small">
                            <i class="fas fa-eraser fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.clearPlaylist') }}</span>
                    </a>
                    <a class="button is-light" v-bind:class="{ 'is-primary': isRepeatActive }" v-on:click.prevent="$audioplayer.playback.toggleRepeatMode();">
                        <span class="icon is-small">
                            <i class="fas fa-redo fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.repeat') }}: {{ repeatMode }}</span>
                    </a>
                    <a class="button is-light" v-on:click.prevent="$audioplayer.currentPlaylist.shuffle();">
                        <span class="icon is-small">
                            <i class="fas fa-random fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.shufflePlaylist') }}</span>
                    </a>
                    <a class="button is-light" v-on:click.prevent="$audioplayer.currentPlaylist.playPrevious();">
                        <span class="icon is-small">
                            <i class="fas fa-backward fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.previousTrack') }}</span>
                    </a>
                    <a class="button is-light" v-on:click.prevent="$audioplayer.currentPlaylist.playNext();">
                        <span class="icon is-small">
                            <i class="fas fa-forward fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.nextTrack') }}</span>
                    </a>
                    <a class="button is-light" v-if="$audioplayer.isStopped" v-on:click.prevent="$audioplayer.playback.play();">
                        <span class="icon is-small">
                            <i class="fas fa-play fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.playTrack') }}</span>
                    </a>
                    <a class="button is-light is-primary" v-else-if="$audioplayer.isPaused" v-on:click.prevent="$audioplayer.playback.resume();">
                        <span class="icon is-small">
                            <i class="fas fa-play fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.resumeTrack') }}</span>
                    </a>
                    <a class="button is-light is-primary" v-else-if="$audioplayer.isPlaying" v-on:click.prevent="$audioplayer.playback.pause();">
                        <span class="icon is-small">
                            <i class="fas fa-pause fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.pauseTrack') }}</span>
                    </a>
                    <a class="button is-light" v-bind:class="{ 'is-primary': $audioplayer.isStopped }" v-on:click.prevent="$audioplayer.playback.stop();">
                        <span class="icon is-small">
                            <i class="fas fa-stop fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.stopTrack') }}</span>
                    </a>
                    <a class="button is-light is-primary" v-if="nowPlayingLoved" v-bind:disabled="$audioplayer.loading" v-on:click.prevent="$audioplayer.currentTrack.unSetLoved();">
                        <span class="icon is-small">
                            <i class="fas fa-heart fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.unloveTrack') }}</span>
                    </a>
                    <a class="button is-light" v-else v-bind:disabled="$audioplayer.loading" v-on:click.prevent="$audioplayer.currentTrack.setLoved();">
                        <span class="icon is-small">
                            <i class="fas fa-heart fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.loveTrack') }}</span>
                    </a>
                    <a class="button is-light" v-on:click.prevent="$audioplayer.currentTrack.download();">
                        <span class="icon is-small">
                            <i class="fas fa-save fa-fw"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.downloadTrack') }}</span>
                    </a>
                </div>

                <table id="playlist-now-playing" class="table is-bordered is-striped is-narrow is-fullwidth is-unselectable">
                    <thead>
                        <tr>
                            <th>{{ $t('currentPlaylist.labels.tableHeaderTrack') }}</th>
                            <th>{{ $t('currentPlaylist.labels.tableHeaderArtist') }}</th>
                            <th>{{ $t('currentPlaylist.labels.tableHeaderAlbum') }}</th>
                            <th>{{ $t('currentPlaylist.labels.tableHeaderGenre') }}</th>
                            <th>{{ $t('currentPlaylist.labels.tableHeaderYear') }}</th>
                            <th>{{ $t('currentPlaylist.labels.tableHeaderActions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-bind:class="{ 'is-selected': $audioplayer.currentTrack.index == i }" v-for="track, i in $audioplayer.tracks" v-bind:key="i">
                            <td>
                                <i class="fa-fw fas fa-play cursor-pointer mr-1" v-bind:title="$t('currentPlaylist.labels.playThisTrackHint')" aria-hidden="true" v-if="iconAction(i) == 'play'" v-on:click="$audioplayer.currentPlaylist.playAtIdx(i);"></i>
                                <i class="fa-fw fas fa-headphones cursor-pointer mr-1" v-bind:title="$t('currentPlaylist.labels.nowPlayingClickToPauseHint')" aria-hidden="true" v-else-if="iconAction(i) == 'none'" v-on:click="$audioplayer.playback.pause();"></i>
                                <i class="fa-fw fas fa-pause cursor-pointer mr-1" v-bind:title="$t('currentPlaylist.labels.pausedClickToResumeHint')" aria-hidden="true" v-else-if="iconAction(i) == 'unPause'" v-on:click="$audioplayer.playback.resume();"></i>
                                <span>{{ track.title}}</span>
                            </td>
                            <td v-if="! track.radioStation">
                                <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-if="track.artist" v-on:click.prevent="navigateToArtistPage(track.artist);">{{ track.artist }}</a>
                            </td>
                            <td v-else>
                                {{ track.artist }}
                            </td>
                            <td><span>{{ track.album }}</span></td>
                            <td><span>{{ track.genre }}</span></td>
                            <td><span>{{ track.year }}</span></td>
                            <td>
                                <i class="fa-fw fas fa-caret-up cursor-pointer" v-bind:title="$t('currentPlaylist.labels.moveElementUpHint')"  aria-hidden="true" v-on:click="$audioplayer.currentPlaylist.moveItemUp(i);"></i>
                                <i class="fa-fw fas fa-caret-down cursor-pointer" v-bind:title="$t('currentPlaylist.labels.moveElementDownHint')" aria-hidden="true" v-on:click="$audioplayer.currentPlaylist.moveItemDown(i);"></i>
                                <i class="fa-fw fas fa-times cursor-pointer" v-bind:title="$t('currentPlaylist.labels.removeElementHint')"  aria-hidden="true" v-on:click="$audioplayer.currentPlaylist.removeItem(i); $forceUpdate();"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-nowplaying',
    template: template(),
    mixins: [
        mixinAPIError, mixinNavigation
    ],
    data: function () {
        return ({
            loading: false,
            savingPlaylist: false,
            currentPlaylistName: null
        });
    },
    created: function () {
        this.currentPlaylistName = this.$audioplayer.currentPlaylist.name;
    },
    computed: {
        isPlaylisted: function () {
            if (this.$audioplayer.currentPlaylist.name) {
                return (true);
            } else {
                return (false);
            }
        },
        isRepeatActive: function () {
            return (this.$audioplayer.repeatTracksMode != 'none');
        },
        isSavePlaylistDisabled: function () {
            return (!this.currentPlaylistName || this.savingPlaylist);
        },
        repeatMode: function () {
            let mode = this.$t('commonLabels.repeatModeNone');
            switch (this.$audioplayer.repeatTracksMode) {
                case 'track':
                    mode = this.$t('commonLabels.repeatModeTrack');
                    break;
                case 'all':
                    mode = this.$t('commonLabels.repeatModeAll');
                    break;
            }
            return (mode);
        }

    },
    methods: {
        iconAction: function (index) {
            if (this.$audioplayer.isPaused && this.$audioplayer.currentTrack.index == index) {
                return ('unPause');
            } else {
                if (this.$audioplayer.isPlaying && this.$audioplayer.currentTrack.index == index) {
                    return ('none');
                } else {
                    return ('play');
                }
            }
        },
        unsetPlaylist: function () {
            this.$audioplayer.currentPlaylist.unset();
            this.currentPlaylistName = null;
        },
        loadRandom: function () {
            this.$audioplayer.currentPlaylist.loadRandomTracks(initialState.defaultResultsPage);
        },
        savePlayList: function () {
            this.loading = true;
            this.clearAPIErrors();
            let trackIds = [];
            this.savingPlaylist = true;
            for (let i = 0; i < this.$audioplayer.tracks.length; i++) {
                trackIds.push(this.$audioplayer.tracks[i].id);
            }
            if (this.$audioplayer.currentPlaylist.isSet()) {
                spieldoseAPI.playlist.update(this.$audioplayer.currentPlaylist.id, this.currentPlaylistName, trackIds).then(response => {
                    this.savingPlaylist = false;
                    this.$audioplayer.currentPlaylist.set(response.data.playlist.id, response.data.playlist.name);
                    this.loading = false;
                }).catch(error => {
                    this.setAPIError(error.getApiErrorData());
                    this.loading = false;
                });
            } else {
                spieldoseAPI.playlist.add(this.currentPlaylistName, trackIds).then(response => {
                    this.savingPlaylist = false;
                    this.$audioplayer.currentPlaylist.set(response.data.playlist.id, response.data.playlist.name);
                    this.loading = false;
                }).catch(error => {
                    this.setAPIError(error.getApiErrorData());
                    this.loading = false;
                });
            }
        }
    }
}