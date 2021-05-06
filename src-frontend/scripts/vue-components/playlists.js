import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPlayer, mixinNavigation } from '../mixins.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
            <p class="title is-1 has-text-centered">{{ $t('currentPlaylist.labels.sectionName') }}</p>
            <div v-if="! hasAPIErrors">
                <div class="field has-addons">
                    <div class="control has-icons-left is-expanded">
                        <input class="input" type="text" :placeholder="$t('currentPlaylist.inputs.playlistNamePlaceholder')" required :disabled="savingPlaylist" v-model.trim="currentPlaylistName">
                        <span class="icon is-small is-left">
                            <i class="fas fa-list-alt"></i>
                        </span>
                    </div>
                    <div class="control">
                        <a class="button is-dark" :class="{ 'is-loading': savingPlaylist }" @click.prevent="savePlayList();">
                            <span class="icon is-small">
                            <i class="fas fa-check"></i>
                            </span>
                            <span>{{ $t('currentPlaylist.buttons.savePlaylist') }}</span>
                        </a>
                    </div>
                    <div class="control" v-if="isPlaylisted">
                        <a class="button is-info" :class="{ 'is-loading': savingPlaylist }" @click.prevent="unsetPlaylist();">
                            <span class="icon is-small">
                            <i class="fas fa-check-square"></i>
                            </span>
                            <span>{{ $t('currentPlaylist.buttons.unsetPlaylist') }}</span>
                        </a>
                    </div>
                </div>

                <div class="buttons">
                    <a class="button is-light" @click.prevent="onLoadRandom">
                        <span class="icon is-small">
                            <i v-if="this.$player.loading" class="fas fa-cog fa-spin fa-fw"></i>
                            <i v-else class="fas fa-clone"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.loadRandom') }}</span>
                    </a>
                    <a class="button is-light" @click.prevent="this.$player.currentPlayList.clear()">
                        <span class="icon is-small">
                            <i class="fas fa-eraser"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.clearPlaylist') }}</span>
                    </a>
                    <a class="button is-light" :class="{ 'is-dark': isRepeatActive }" @click.prevent="this.$player.currentPlayList.toggleRepeatMode()">
                        <span class="icon is-small">
                            <i class="fas fa-redo"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.repeat') }}: {{ repeatMode }}</span>
                    </a>
                    <a class="button is-light" @click.prevent="this.$player.shuffleCurrentPlayList()">
                        <span class="icon is-small">
                            <i class="fas fa-random"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.shufflePlaylist') }}</span>
                    </a>
                    <a class="button is-light" @click.prevent="this.$player.playPreviousTrack()">
                        <span class="icon is-small">
                            <i class="fas fa-backward"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.previousTrack') }}</span>
                    </a>
                    <a class="button is-light" v-if="this.$player.isStopped" @click.prevent="this.$player.play();">
                        <span class="icon is-small">
                            <i class="fas fa-play"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.playTrack') }}</span>
                    </a>
                    <a class="button is-light" v-else-if="this.$player.isPaused" @click.prevent="this.$player.play()">
                        <span class="icon is-small">
                            <i class="fas fa-play"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.resumeTrack') }}</span>
                    </a>
                    <a class="button is-light" v-else-if="this.$player.isPlaying" @click.prevent="this.$player.pause()">
                        <span class="icon is-small">
                            <i class="fas fa-pause"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.pauseTrack') }}</span>
                    </a>
                    <a class="button is-light" @click.prevent="this.$player.stop()">
                        <span class="icon is-small">
                            <i class="fas fa-stop"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.stopTrack') }}</span>
                    </a>
                    <a class="button is-light" @click.prevent="this.$player.playNextTrack()">
                        <span class="icon is-small">
                            <i class="fas fa-forward"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.nextTrack') }}</span>
                    </a>
                    <a class="button is-dark" v-if="nowPlayingLoved" @click.prevent="this.$player.unSetLovedCurrentTrack();">
                        <span class="icon is-small">
                            <i class="fas fa-heart"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.unloveTrack') }}</span>
                    </a>
                    <a class="button is-light" v-else @click.prevent="this.$player.setLovedCurrentTrack();">
                        <span class="icon is-small">
                            <i class="fas fa-heart"></i>
                        </span>
                        <span class="is-hidden-touch">{{ $t('currentPlaylist.buttons.loveTrack') }}</span>
                    </a>
                    <a class="button is-light" @click.prevent="onDownloadCurrentTrack">
                        <span class="icon is-small">
                            <i class="fas fa-save"></i>
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
                        <tr :class="{ 'is-selected': this.$player.currentPlayList.currentTrackIndex == i }" v-for="track, i in this.$player.currentPlayList.tracks" :key="track.id">
                            <td>
                                <span class="icon">
                                    <i class="fas fa-play cursor-pointer" :title="$t('currentPlaylist.labels.playThisTrackHint')" aria-hidden="true" v-if="iconAction(i) == 'play'" @click="this.$player.currentPlayList.currentTrackIndex = i; this.$player.play()"></i>
                                    <i class="fas fa-headphones cursor-pointer" :title="$t('currentPlaylist.labels.nowPlayingClickToPauseHint')" aria-hidden="true" v-else-if="iconAction(i) == 'none'" @click="this.$player.pause();"></i>
                                    <i class="fas fa-pause cursor-pointer" :title="$t('currentPlaylist.labels.pausedClickToResumeHint')" aria-hidden="true" v-else-if="iconAction(i) == 'unPause'" @click="this.$player.resume();"></i>
                                </span>
                                <span>{{ track.title}}</span>
                            </td>
                            <td v-if="! track.radioStation">
                                <router-link :title="$t('commonLabels.navigateToArtistPage')" v-if="track.artist" :to="{ name: 'artist', params: { artist: track.artist }}">{{ track.artist }}</router-link>
                            </td>
                            <td v-else>
                                {{ track.artist }}
                            </td>
                            <td><span>{{ track.album }}</span></td>
                            <td><span>{{ track.genre }}</span></td>
                            <td><span>{{ track.year }}</span></td>
                            <td>
                                <span class="icon cursor-pointer" :title="$t('currentPlaylist.labels.moveElementUpHint')" aria-hidden="true" @click="this.$player.currentPlayList.moveItemUp(i)"><i class="fas fa-caret-up"></i></span>
                                <span class="icon cursor-pointer" :title="$t('currentPlaylist.labels.moveElementDownHint')" aria-hidden="true" @click="this.$player.currentPlayList.moveItemDown(i)"><i class="fas fa-caret-down"></i></span>
                                <span class="icon cursor-pointer" :title="$t('currentPlaylist.labels.removeElementHint')" aria-hidden="true" @click="this.$player.currentPlayList.removeItem(i)"><i class="fas fa-times cursor-pointer"></i></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <spieldose-api-error-component v-else :apiError="apiError"></spieldose-api-error-component>
        </div>
    `;
};

export default {
    name: 'spieldose-nowplaying',
    template: template(),
    mixins: [
        mixinAPIError, mixinPlayer, mixinNavigation
    ],
    data: function () {
        return ({
            loading: false,
            savingPlaylist: false,
            currentPlaylistName: null
        });
    },
    created: function () {
        this.currentPlaylistName = this.$player.currentPlayList.name;
    },
    computed: {
        nowPlayingLoved: function() {
            if (this.$player.currentPlayList.currentTrackIndex >= 0) {
                return(this.$player.currentPlayList.tracks[this.$player.currentPlayList.currentTrackIndex].loved == "1");
            } else {
                return(false);
            }
        },
        isPlaylisted: function () {
            if (this.$player.currentPlayList.name) {
                return (true);
            } else {
                return (false);
            }
        },
        isRepeatActive: function () {
            return (this.$player.currentPlayList.repeatMode != 'none');
        },
        isSavePlaylistDisabled: function () {
            return (!this.currentPlaylistName || this.savingPlaylist);
        },
        repeatMode: function () {
            let mode = this.$t('commonLabels.repeatModeNone');
            switch (this.$player.currentPlayList.repeatMode) {
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
        onLoadRandom: function() {
            this.$player.loadRandomTracksIntoCurrentPlayList(32);
        },
        onDownloadCurrentTrack: function() {
            if (this.$player.currentPlayList.currentTrackIndex >= 0) {
                this.$player.downloadTrack(this.$player.currentPlayList.tracks[this.$player.currentPlayList.currentTrackIndex].id);
            }
        },
        iconAction: function (index) {
            if (this.$player.isPaused && this.$player.currentPlayList.currentTrackIndex == index) {
                return ('unPause');
            } else {
                if (this.$player.isPlaying && this.$player.currentPlayList.currentTrackIndex == index) {
                    return ('none');
                } else {
                    return ('play');
                }
            }
        },
        unsetPlaylist: function () {
            this.$player.currentPlaylist.unset();
            this.currentPlaylistName = null;
        },
        savePlayList: function () {
            this.loading = true;
            this.clearAPIErrors();
            let trackIds = [];
            this.savingPlaylist = true;
            for (let i = 0; i < this.player.tracks.length; i++) {
                trackIds.push(this.player.tracks[i].id);
            }
            if (this.$player.currentPlayList.isSet()) {
                spieldoseAPI.playlist.update(this.$player.currentPlayList.id, this.currentPlaylistName, trackIds, (response) => {
                    this.savingPlaylist = false;
                    if (response.status == 200) {
                        this.player.currentPlaylist.set(response.data.playlist.id, response.data.playlist.name);
                    } else {
                        this.setAPIError(response.getApiErrorData());
                    }
                    this.loading = false;
                });
            } else {
                spieldoseAPI.playlist.add(this.currentPlaylistName, trackIds, (response) => {
                    this.savingPlaylist = false;
                    if (response.status == 200) {
                        this.player.currentPlaylist.set(response.data.playlist.id, response.data.playlist.name);
                    } else {
                        this.setAPIError(response.getApiErrorData());
                    }
                    this.loading = false;
                });
            }
        }
    }
}