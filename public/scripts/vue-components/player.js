let player = (function () {
    "use strict";

    const template = function () {
        return `
            <div id="player" class="box is-paddingless is-radiusless is-unselectable">
                <img id="album-cover" v-bind:class="{ 'rotate-album': hasRotateVinylClass }" v-bind:src="coverSrc" v-on:error="replaceAlbumThumbnailWithLoadError();">
                <canvas id="canvas"></canvas>
                <nav class="level is-marginless">
                    <div class="level-left">
                        <span id="song-current-time" class="level-item has-text-grey">{{ currentPlayedSeconds | formatSeconds }}</span>
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
                    <h2 class="subtitle is-5 cut-text" v-bind:title="nowPlayingArtist">{{ nowPlayingArtist }}</h2>
                </div>
                <div id="player-controls" class="is-unselectable">
                    <div class="has-text-centered player-buttons">
                        <span title="shuffle playlist" v-on:click.prevent="playerData.shufflePlayList();" class="icon"><i class="fas fa-2x fa-random"></i></span>
                        <span title="toggle repeat mode" v-bind:class="{ 'btn-active': playerData.repeatTracksMode != 'none' }" v-on:click.prevent="playerData.toggleRepeatMode();" class="icon"><i class="fas fa-2x fa-redo"></i></span>
                        <span title="go to previous track" id="btn-previous" v-on:click.prevent="playerData.playPreviousTrack();" class="icon"><i class="fas fa-2x fa-step-backward"></i></span>
                        <span title="pause track" id="btn-pause" v-on:click.prevent="playerData.pause();" v-if="playerData.isPlaying" class="icon"><i class="fas fa-2x fa-pause"></i></span>
                        <span title="play track" id="btn-play" v-on:click.prevent="playerData.play();" v-else class="icon"><i class="fas fa-2x fa-play"></i></span>
                        <span title="go to next track" id="btn-next" v-on:click.prevent="playerData.playNextTrack();" class="icon"><i class="fas fa-2x fa-step-forward"></i></span>
                        <span title="unlove this track" v-if="nowPlayingLoved" v-on:click.prevent="playerData.playerData.currentTrack.unSetLoved();" class="icon btn-active"><i class="fas fa-2x fa-heart"></i></span>
                        <span title="love this track" v-else v-on:click.prevent="playerData.playerData.currentTrack.setLoved();" class="icon"><i class="fas fa-2x fa-heart"></i></span>
                        <span title="download this track" id="btn-download" class="icon" v-on:click.prevent="playerData.currentTrack.download();"><i class="fas fa-2x fa-save"></i></span>
                    </div>
                    <div id="player-volume-control">
                        <div class="columns">
                            <div class="column is-narrow">
                                <span title="mute/unmute volume" class="icon" v-on:click.prevent="toggleMute">
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

    let module = Vue.component('spieldose-player-component', {
        template: template(),
        mixins: [mixinPlayer],
        data: function () {
            return ({
                preMuteVolume: 1,
                audio: null,
                vinylRotationEffect: false
            });
        },
        computed: {
            hasRotateVinylClass: function () {
                return (this.vinylRotationEffect && this.coverSrc == 'images/vinyl.png');
            },
            coverSrc: function () {
                if (this.playerData.currentTrack.track && this.playerData.currentTrack.track.image) {
                    if (this.playerData.currentTrack.track.image.indexOf("http") == 0) {
                        return ("api/thumbnail?url=" + this.playerData.currentTrack.track.image);
                    } else {
                        return ("api/thumbnail?hash=" + this.playerData.currentTrack.track.image);
                    }
                } else {
                    return ('images/vinyl.png');
                }
            },
            streamUrl: function () {
                if (this.playerData.currentTrack.track && this.playerData.currentTrack.track.id) {
                    return ("api/track/get/" + this.playerData.currentTrack.track.id);
                } else {
                    return ("");
                }
            }
        },
        methods: {
            replaceAlbumThumbnailWithLoadError: function () {
                if (this.playerData.currentTrack.track && this.playerData.currentTrack.track.image) {
                    this.playerData.currentTrack.track.image = null;
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
                        playPromise.then(function () {
                        }).catch(function (error) {
                        });
                    }
                    if (this.playerData.currentTrack.index >= 0) {
                        const element = document.getElementById("playlist-item-" + this.playerData.currentTrack.index);
                        if (element) {
                            element.scrollIntoView();
                        }
                    }
                } else {
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
            let self = this;
            this.audio = document.createElement('audio');

            let aa = this.audio;
            aa.addEventListener("timeupdate", function (track) {
                const currentProgress = aa.currentTime / aa.duration;
                if (!isNaN(currentProgress)) {
                    self.songProgress = currentProgress.toFixed(2);
                } else {
                    self.songProgress = 0;
                }
                self.currentPlayedSeconds = Math.floor(aa.currentTime).toString();
            });
            aa.addEventListener("volumechange", function (v) {
                self.volume = aa.volume;
            });
            aa.addEventListener("ended", function () {
                self.playerData.playNextTrack();
            });
            aa.addEventListener("error", function (e) {
                // try to load next song on playlist if errors found
                self.playerData.playNextTrack();
                // TODO
                /*
                switch (e.target.error.code) {
                }
                */
            });
            initializeVisualizer(document.getElementById("canvas"), self.audio);
            document.getElementById('song-played-progress').addEventListener('click', function (e) {
                const offset = this.getBoundingClientRect();
                const x = e.pageX - offset.left;
                self.audio.currentTime = ((parseFloat(x) / parseFloat(this.offsetWidth)) * 100) * self.audio.duration / 100;
            });

            if (typeof window.IntersectionObserver !== "undefined") {
                const playerVisibilityObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.intersectionRatio > 0) {
                            bus.$emit("hidePlayerNavbar");
                        } else {
                            bus.$emit("showPlayerNavBar");
                        }
                    });
                });

                // observe player controls div (show fixed player controls bottom bar when sidebar player controls are hidden because scrolled area)
                playerVisibilityObserver.observe(document.getElementById("player-controls"));
            }
        }, created: function () {
            let self = this;
            self.playerData.loadRandomTracks(initialState.defaultResultsPage, function () {
                self.playerData.play();
            });
        }
    });

    return (module);
})();
