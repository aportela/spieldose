import { bus } from '../bus.js';
import { mixinNavigation, mixinPlayer } from '../mixins.js';
import { default as spieldoseSettings } from '../settings.js';

const template = function () {
    return `
        <div id="player" class="box is-paddingless is-radiusless is-unselectable">
            <div v-if="vinylRotationEffect" id="album-cover-container" :class="{'album-cover-container-rotate': $audioplayer.isPlaying }" @click.prevent="vinylRotationEffect=!vinylRotationEffect">
                <img id="album-cover-animated" v-if="coverSrc != 'images/vinyl.png'" v-bind:src="coverSrc" @error="replaceAlbumThumbnailWithLoadError();">
            </div>
            <img id="album-cover" v-else v-bind:src="coverSrc" v-on:error="replaceAlbumThumbnailWithLoadError();" @click.prevent="vinylRotationEffect=!vinylRotationEffect">
            <!--
            <canvas id="canvas"></canvas>
            -->
            <nav class="level is-marginless">
                <div class="level-left">
                    <span id="song-current-time" class="level-item has-text-grey">{{ formatSeconds(currentPlayedSeconds) }}</span>
                </div>
                <div class="level-item">
                    <input id="song-played-progress" class="is-pulled-left" type="range" v-model="songProgress" min="0" max="1" step="0.01" />
                </div>
                <div class="level-right">
                    <span id="song-duration" class="level-item has-text-grey">{{ nowPlayingLength }}</span>
                </div>
            </nav>
            <div id="player-metadata-container" class="has-text-centered">
                <h1 class="title is-4 cut-text" v-bind:title="nowPlayingTitle">{{ nowPlayingTitle }}</h1>
                <h2 class="subtitle is-5 cut-text" v-bind:title="nowPlayingArtist"><a href="#" v-on:click.prevent="navigateToArtistPage(nowPlayingArtist);">{{ nowPlayingArtist }}</a></h2>
            </div>
            <div id="player-controls" class="is-unselectable">
                <div class="has-text-centered player-buttons">
                    <span v-bind:title="$t('player.buttons.shufflePlaylistHint')" v-on:click.prevent="$audioplayer.currentPlaylist.shuffle();" class="icon"><i class="fas fa-2x fa-random"></i></span>
                    <span v-bind:title="$t('player.buttons.toggleRepeatHint')" v-bind:class="{ 'btn-active': $audioplayer.repeatTracksMode != 'none' }" v-on:click.prevent="$audioplayer.playback.toggleRepeatMode();" class="icon"><i class="fas fa-2x fa-redo"></i></span>
                    <span v-bind:title="$t('player.buttons.previousTrackHint')" id="btn-previous" v-on:click.prevent="$audioplayer.currentPlaylist.playPrevious();" class="icon"><i class="fas fa-2x fa-step-backward"></i></span>
                    <span v-bind:title="$t('player.buttons.pauseTrackHint')" id="btn-pause" v-on:click.prevent="$audioplayer.playback.pause();" v-if="$audioplayer.isPlaying" class="icon"><i class="fas fa-2x fa-pause"></i></span>
                    <span v-bind:title="$t('player.buttons.playTrackHint')" id="btn-play" v-on:click.prevent="$audioplayer.playback.play();" v-else class="icon"><i class="fas fa-2x fa-play"></i></span>
                    <span v-bind:title="$t('player.buttons.nextTrackHint')" id="btn-next" v-on:click.prevent="$audioplayer.currentPlaylist.playNext();" class="icon"><i class="fas fa-2x fa-step-forward"></i></span>
                    <span v-bind:title="$t('player.buttons.unloveTrackHint')" v-if="nowPlayingLoved" v-on:click.prevent="$audioplayer.currentTrack.unSetLoved();" class="icon btn-active"><i class="fas fa-2x fa-heart"></i></span>
                    <span v-bind:title="$t('player.buttons.loveTrackHint')" v-else v-on:click.prevent="$audioplayer.currentTrack.setLoved();" class="icon"><i class="fas fa-2x fa-heart"></i></span>
                    <span v-bind:title="$t('player.buttons.downloadTrackHint')" id="btn-download" class="icon" v-on:click.prevent="$audioplayer.currentTrack.download();"><i class="fas fa-2x fa-save"></i></span>
                </div>
                <div id="player-volume-control">
                    <div class="columns">
                        <div class="column is-narrow">
                            <span v-bind:title="$t('player.buttons.toggleMuteHint')" class="icon" v-on:click.prevent="toggleMute">
                                <i v-if="volume > 0" class="fas fa-2x fa-volume-up"></i>
                                <i v-else class="fas fa-2x fa-volume-off"></i>
                            </span>
                        </div>
                        <div class="column">
                            <input id="volume-range" type="range" v-model="volume" min="0" max="1" step="0.05" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-player-component',
    template: template(),
    mixins: [ mixinNavigation, mixinPlayer],
    data: function () {
        return ({
            preMuteVolume: 1,
            audio: null,
            vinylRotationEffect: true
        });
    },
    computed: {
        hasRotateVinylClass: function () {
            return (this.vinylRotationEffect && this.coverSrc == 'images/vinyl.png');
        },
        coverSrc: function () {
            if (this.$audioplayer.currentTrack.track && this.$audioplayer.currentTrack.track.radioStation) {
                if (this.$audioplayer.currentTrack.track.radioStation.image) {
                    return ('api/thumbnail?url=' + encodeURIComponent(this.$audioplayer.currentTrack.track.radioStation.image));
                } else {
                    return ('images/vinyl.png');
                }
            } else {
                if (this.$audioplayer.currentTrack.track && this.$audioplayer.currentTrack.track.image) {
                    if (this.$audioplayer.currentTrack.track.image.indexOf("http") == 0) {
                        return ('api/thumbnail?url=' + encodeURIComponent(this.$audioplayer.currentTrack.track.image));
                    } else {
                        return ('api/thumbnail?hash=' + this.$audioplayer.currentTrack.track.image);
                    }
                } else {
                    if (this.$audioplayer.currentTrack.track && this.$audioplayer.currentTrack.track.albumMBId) {
                        return('https://coverartarchive.org/release/' + this.$audioplayer.currentTrack.track.albumMBId + '/front');
                    } else {
                        return ('images/vinyl.png');
                    }
                }
            }
        },
        streamUrl: function () {
            if (this.$audioplayer.currentTrack.track && this.$audioplayer.currentTrack.track.radioStation) {
                return (this.$audioplayer.currentTrack.track.radioStation.streamUrls[0]);
            } else {
                if (this.$audioplayer.currentTrack.track && this.$audioplayer.currentTrack.track.id) {
                    return ('api/track/get/' + this.$audioplayer.currentTrack.track.id);
                } else {
                    return ('');
                }
            }
        }
    },
    methods: {
        replaceAlbumThumbnailWithLoadError: function () {
            if (this.$audioplayer.currentTrack.track && this.$audioplayer.currentTrack.track.radioStation) {
                if (this.$audioplayer.currentTrack.track.radioStation.image) {
                    this.$audioplayer.currentTrack.track.radioStation.image = null;
                }
            } else {
                if (this.$audioplayer.currentTrack.track && this.$audioplayer.currentTrack.track.image) {
                    this.$audioplayer.currentTrack.track.image = null;
                } else {
                    if (this.$audioplayer.currentTrack.track && this.$audioplayer.currentTrack.track.albumMBId) {
                        this.$audioplayer.currentTrack.track.albumMBId = null;
                    }
                }
            }
        },
        toggleMute: function () {
            if (!this.audio.muted) {
                this.preMuteVolume = this.audio.volume;
                this.audio.volume = 0;
            } else {
                this.audio.volume = this.preMuteVolume
            }
            this.audio.muted = !this.audio.muted;
        }
    },
    watch: {
        volume: function (value) {
            this.audio.volume = value;
            spieldoseSettings.setCurrentSessionVolume(value);
        },
        streamUrl: function (value) {
            if (value) {
                this.audio.src = this.streamUrl;
                if (this.isPlaying || this.isPaused) {
                    this.audio.pause();
                    this.audio.currentTime = 0;
                }
                this.audio.load();
                let playPromise = this.audio.play();
                if (playPromise !== undefined) {
                    console.log(playPromise);
                    playPromise.then(() => {
                    }).catch((error) => {
                        this.$audioplayer.playback.pause();
                        this.audio.currentTime = 0;
                    });
                }
                if (this.$audioplayer.currentTrack.index >= 0) {
                    /*
                    const element = document.getElementById('playlist-item-' + this.$audioplayer.currentTrack.index);
                    if (element) {
                        element.scrollIntoView();
                    }
                    */
                }
            } else {
                this.audio.currentTime = 0;
                this.audio.pause();
            }
        },
        isPlaying: function (newValue) {
            if (newValue) {
                this.audio.play();
            }
        },
        isPaused: function (newValue) {
            if (newValue == false) {
                this.audio.play();
            } else {
                this.audio.pause();
            }
        },
        isStopped: function (newValue) {
            if (newValue) {
                this.audio.pause();
                this.audio.currentTime = 0;
            }
        }
    },
    mounted: function () {
        this.audio = document.createElement('audio');
        this.audio.volume = spieldoseSettings.getCurrentSessionVolume();
        let aa = this.audio;
        aa.addEventListener('timeupdate', (track) => {
            const currentProgress = aa.currentTime / aa.duration;
            if (!isNaN(currentProgress)) {
                this.songProgress = currentProgress.toFixed(2);
            } else {
                this.songProgress = 0;
            }
            this.currentPlayedSeconds = Math.floor(aa.currentTime).toString();
        });
        aa.addEventListener('volumechange', (v) => {
            this.volume = aa.volume;
        });
        aa.addEventListener('ended', () => {
            if (this.$audioplayer.repeatTracksMode == 'track') {
                this.audio.pause();
                this.audio.currentTime = 0;
                this.audio.play();
            } else {
                this.$audioplayer.currentPlaylist.playNext();
            }
        });
        aa.addEventListener('error', (e) => {
            // try to load next song on playlist if errors found
            this.$audioplayer.currentPlaylist.playNext();
            // TODO
            /*
            switch (e.target.error.code) {
            }
            */
        });
        // visualizer launch error with remote streams because CORS and Access-Control-Allow-Origin headers
        //initializeVisualizer(document.getElementById("canvas"), this.audio);
        let el1 = document.getElementById('song-played-progress');
        if (el1) {
            el1.addEventListener('click', (e) => {
            const offset = e.target.getBoundingClientRect();
            const x = e.pageX - offset.left;
            this.audio.currentTime = ((parseFloat(x) / parseFloat(e.target.offsetWidth)) * 100) * this.audio.duration / 100;
            });
        }

        if (typeof window.IntersectionObserver !== 'undefined') {
            const playerVisibilityObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.intersectionRatio > 0) {
                        bus.emit('hidePlayerNavbar');
                    } else {
                        bus.emit('showPlayerNavBar');
                    }
                });
            });

            // observe player controls div (show fixed player controls bottom bar when sidebar player controls are hidden because scrolled area)
            var el2 = document.getElementById('player-controls');
            if (el2) {
                playerVisibilityObserver.observe(el2);
            }
        }
    },
    created: function () {
        this.$audioplayer.currentPlaylist.loadRandomTracks(initialState.defaultResultsPage, function () { });
    }
}