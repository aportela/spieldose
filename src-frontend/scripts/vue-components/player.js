import { bus } from '../bus.js';
import { mixinPlayer, mixinNavigation } from '../mixins.js';
import { default as spieldoseSettings } from '../settings.js';
//import { default as playerClass } from '../playerData.js';

const template = function () {
    return `
        <div id="player" class="box is-paddingless is-radiusless is-unselectable" v-if="this.$player">
            <img id="album-cover" v-bind:class="{ 'rotate-album': hasRotateVinylClass }" v-bind:src="coverSrc" @error="replaceAlbumThumbnailWithLoadError();">
            <!--
            <canvas id="canvas"></canvas>
            -->
            <nav class="level is-marginless">
                <div class="level-left">
                    <span id="song-current-time" class="level-item has-text-grey">{{ playerCurrentTime }}</span>
                </div>
                <div class="level-item">
                    <input id="song-played-progress" class="is-pulled-left" type="range" v-model="currentTrackProgressControl" min="0" max="1" step="0.01" :disabled="nowPlayingLength == '00:00'" />
                </div>
                <div class="level-right">
                    <span id="song-duration" class="level-item has-text-grey">{{ nowPlayingLength }}</span>
                </div>
            </nav>
            <div id="player-metadata-container" class="has-text-centered">
                <h1 class="title is-4 cut-text" v-bind:title="nowPlayingTitle">{{ nowPlayingTitle }}</h1>
                <h2 class="subtitle is-5 cut-text" v-bind:title="nowPlayingArtist">
                    <router-link :to="{ name: 'artist', params: { artist: nowPlayingArtist }}">{{ nowPlayingArtist }}</router-link>
                </h2>
            </div>
            <div id="player-controls" class="is-unselectable">
                <div class="has-text-centered player-buttons">
                    <span v-bind:title="$t('player.buttons.shufflePlaylistHint')" @click.prevent="this.$player.shuffleCurrentPlayList()" class="icon"><i class="fas fa-2x fa-random"></i></span>
                    <span v-bind:title="$t('player.buttons.toggleRepeatHint')" v-bind:class="{ 'btn-active': this.$player.currentPlayList.repeatMode != 'none' }" @click.prevent="this.$player.currentPlayList.toggleRepeatMode()" class="icon"><i class="fas fa-2x fa-redo"></i></span>
                    <span v-bind:title="$t('player.buttons.previousTrackHint')" id="btn-previous" @click.prevent="this.$player.playPreviousTrack()" class="icon"><i class="fas fa-2x fa-step-backward"></i></span>
                    <span v-bind:title="$t('player.buttons.pauseTrackHint')" id="btn-pause" @click.prevent="this.$player.pause()" v-if="this.$player.isPlaying" class="icon"><i class="fas fa-2x fa-pause"></i></span>
                    <span v-bind:title="$t('player.buttons.playTrackHint')" id="btn-play" @click.prevent="this.$player.play()" v-else class="icon"><i class="fas fa-2x fa-play"></i></span>
                    <span v-bind:title="$t('player.buttons.nextTrackHint')" id="btn-next" @click.prevent="this.$player.playNextTrack()" class="icon"><i class="fas fa-2x fa-step-forward"></i></span>
                    <span v-bind:title="$t('player.buttons.unloveTrackHint')" v-if="nowPlayingLoved" @click.prevent="this.$player.unSetLovedCurrentTrack()" class="icon btn-active"><i class="fas fa-2x fa-heart"></i></span>
                    <span v-bind:title="$t('player.buttons.loveTrackHint')" v-else @click.prevent="this.$player.setLovedCurrentTrack()" class="icon"><i class="fas fa-2x fa-heart"></i></span>
                    <span v-bind:title="$t('player.buttons.downloadTrackHint')" id="btn-download" class="icon" @click.prevent="player.currentTrack.download();"><i class="fas fa-2x fa-save"></i></span>
                </div>
                <div id="player-volume-control">
                    <div class="columns">
                        <div class="column is-narrow">
                            <span v-bind:title="$t('player.buttons.toggleMuteHint')" class="icon" @click.prevent="this.$player.mute()">
                                <i v-if="volume > 0" class="fas fa-2x fa-volume-up"></i>
                                <i v-else class="fas fa-2x fa-volume-off"></i>
                            </span>
                        </div>
                        <div class="column">
                            <input id="volume-range" type="range" v-model="currentAudioVolumeControl" min="0" max="1" step="0.05" />
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
    mixins: [mixinPlayer, mixinNavigation],
    data: function () {
        return ({
            vinylRotationEffect: false,
            currentPlayedSeconds: "00:00",
            currentTrackProgressControl: 0,
            currentAudioVolumeControl: 1,
            audioVolume: 100
        });
    },
    computed: {
        hasRotateVinylClass: function () {
            return (this.vinylRotationEffect && this.coverSrc == 'images/vinyl.png');
        },
        coverSrc: function () {
            if (this.$player.currentTrack) {
                if (this.$player.currentTrack.radioStation && this.$player.currentTrack.radioStation.image) {
                        return ('api/thumbnail?url=' + encodeURIComponent(this.$player.currentTrack.radioStation.image));
                } else if (this.$player.currentTrack.image) {
                    if (this.$player.currentTrack.image.indexOf("http") == 0) {
                        return ('api/thumbnail?url=' + encodeURIComponent(this.$player.currentTrack.image));
                    } else {
                        return ('api/thumbnail?hash=' + this.$player.currentTrack.image);
                    }
                } else {
                    return ('images/vinyl.png');
                }
            } else {
                return ('images/vinyl.png');
            }
        },
        streamUrl: function () {
            if (this.$player.currentTrack && this.$player.status != "stopped") {
                if (this.$player.currentTrack.radioStation) {
                    return (this.$player.currentTrack.radioStation.streamUrls[0]);
                } else if (this.$player.currentTrack.id) {
                    return ('api/track/get/' + this.$player.currentTrack.id);
                } else {
                    return ('');
                }
            } else {
                return ('');
            }
        },
        playerCurrentVolume: function() {
            return(this.$player.audioSettings.currentVolume);
        },
        playerCurrentTime: function() {
            function formatSecondsAsTime(secs, format) {
                const hr = Math.floor(secs / 3600);
                let min = Math.floor((secs - (hr * 3600)) / 60);
                let sec = Math.floor(secs - (hr * 3600) - (min * 60));
                if (min < 10) {
                    min = "0" + min;
                }
                if (sec < 10) {
                    sec = "0" + sec;
                }
                return (min + ':' + sec);
            }
            return(formatSecondsAsTime(this.$player.nowPlayingCurrentTime.toString()));
        },
        playerCurrentProgress: function() {
            return(this.$player.nowPlayingCurrentProgress);
        },
        nowPlayingLength: function() {
            if (this.$player.currentTrack && this.$player.currentTrack.playtimeString) {
                return (this.$player.currentTrack.playtimeString);
            } else {
                return ("00:00");
            }
        },
        nowPlayingLoved: function() {
            return (this.$player.currentTrack && this.$player.currentTrack.loved == '1');
        },
        nowPlayingTitle: function () {
            if (this.$player.currentTrack && this.$player.currentTrack.title) {
                return (this.$player.currentTrack.title);
            } else {
                return ("track title unknown");
            }
        },
        nowPlayingArtist: function() {
            if (this.$player.currentTrack && this.$player.currentTrack.artist) {
                return (this.$player.currentTrack.artist);
            } else {
                return ("artist unknown");
            }
        },
        nowPlayingAlbum: function() {
            if (this.$player.currentTrack && this.$player.currentTrack.album) {
                return (" / " + this.$player.currentTrack.album);
            } else {
                return ("album unknown");
            }
        },
        nowPlayingYear: function() {
            if (this.$player.currentTrack && this.$player.currentTrack.year) {
                return (" (" + this.$player.currentTrack.year + ")");
            } else {
                return (" (year unknown)");
            }
        }
    },
    watch: {
        streamUrl: function (newValue) {
            if (newValue) {
                this.$player.audio.src = newValue;
                if (this.$player.isPlaying || this.$player.isPaused) {
                    this.$player.audio.pause();
                    this.$player.audio.currentTime = 0;
                }
                this.$player.audio.load();
                let playPromise = this.$player.audio.play();
                if (playPromise !== undefined) {
                    playPromise.then(function () {
                    }).catch(function (error) {
                    });
                }
                if (this.$player.currentPlayList.currentTrackIndex >= 0) {
                    const element = document.getElementById('playlist-item-' + this.$player.currentTrackIndex);
                    if (element) {
                        element.scrollIntoView();
                    }
                }
            } else {
                this.$player.audio.currentTime = 0;
                this.$player.audio.pause();
            }
        },
        playerCurrentProgress: function(newValue) {
            this.currentTrackProgressControl = newValue;
        }
    },
    created: function () {
        this.$player.init();
        this.currentAudioVolumeControl = this.$player.audioSettings.currentVolume;
        this.$player.loadRandomTracksIntoCurrentPlayList(32);
    },
    mounted: function ()
    {
        document.getElementById('song-played-progress').addEventListener('click', (e) => {
            const offset = e.target.getBoundingClientRect();
            const x = e.pageX - offset.left;
            const seconds = ((parseFloat(x) / parseFloat(e.target.offsetWidth)) * 100) * this.$player.audio.duration / 100;
            this.$player.changeCurrentTime(seconds);
        });

        document.getElementById('volume-range').addEventListener('click', (e) => {
            this.$player.changeVolume(e.target.value);
        });

        /*
        aa
        // visualizer launch error with remote streams because CORS and Access-Control-Allow-Origin headers
        //initializeVisualizer(document.getElementById("canvas"), this.$player.audio);

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
            playerVisibilityObserver.observe(document.getElementById('player-controls'));
        }
        */
    },
    methods: {
        replaceAlbumThumbnailWithLoadError: function () {
            if (this.$player.currentTrack && this.$player.currentTrack.radioStation) {
                if (this.$player.currentTrack.radioStation.image) {
                    this.$player.currentTrack.radioStation.image = null;
                }
            } else {
                if (this.$player.currentTrack && this.$player.currentTrack.image) {
                    this.$player.currentTrack.image = null;
                }
            }
        },
        toggleMute: function () {
            if (!this.$player.audio.muted) {
                this.$player.audioSettings.preMuteVolume = this.$player.audio.volume;
                //this.audioVolume = 0;
            } else {
                //this.audioVolume = this.$player.audioSettings.preMuteVolume;
            }
            this.$player.audio.muted = !this.$player.audio.muted;
        }
    }
}