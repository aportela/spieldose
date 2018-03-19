var player = (function () {
    "use strict";

    var template = function () {
        return `
            <div class="columns">
                <div class="column is-5">
                    <div id="player" class="box is-paddingless is-radiusless is-unselectable app-content">
                        <img id="album-cover" v-bind:class="{ 'rotate-album': vinylRotationEffect && coverSrc == 'images/vinyl.png'}" v-bind:src="coverSrc">
                        <canvas id="canvas"></canvas>
                        <nav class="level is-marginless">
                            <div class="level-left">
                                <span id="song-current-time" class="level-item has-text-grey">{{ currentPlayedSeconds | formatSeconds }}</span>
                            </div>
                            <div class="level-item">
                                <input id="song-played-progress" class="is-pulled-left" type="range" v-model="progressv" min="0" max="1" step="0.01" />
                            </div>
                            <div class="level-right">
                                <span id="song-duration" class="level-item has-text-grey">{{ nowPlayingLength }}</span>
                            </div>
                        </nav>
                        <div id="player-metadata-container" class="has-text-centered">
                            <h1 class="title is-4 has-text-light cut-text" v-bind:title="nowPlayingTitle">{{ nowPlayingTitle }}</h1>
                            <h2 class="subtitle is-5 has-text-grey-light cut-text" v-bind:title="nowPlayingArtist">{{ nowPlayingArtist }}</h2>
                        </div>
                        <div id="player-controls" class="is-unselectable">
                            <div class="has-text-centered" id="player-buttons">
                                <span title="toggle shuffle mode" v-bind:class="{ 'btn-active': playerData.shuffleTracks }" v-on:click.prevent="playerData.toggleShuffleMode();" class="icon"><i class="fa fa-2x fa-random"></i></span>
                                <span title="toggle repeat mode" v-bind:class="{ 'btn-active': playerData.repeatTracksMode != 'none' }" v-on:click.prevent="playerData.toggleRepeatMode();" class="icon"><i class="fa fa-2x fa-repeat"></i></span>
                                <span title="go to previous track" id="btn-previous" v-on:click.prevent="playerData.playPreviousTrack();" class="icon"><i class="fa fa-2x fa-step-backward"></i></span>
                                <span title="pause track" id="btn-pause" v-on:click.prevent="playerData.pause();" v-if="playerData.isPlaying" class="icon has-text-white-bis"><i class="fa fa-2x fa-pause"></i></span>
                                <span title="play track" id="btn-play" v-on:click.prevent="playerData.play();" v-else class="icon has-text-white-bis"><i class="fa fa-2x fa-play"></i></span>
                                <span title="go to next track" id="btn-next" v-on:click.prevent="playerData.playNextTrack();" class="icon"><i class="fa fa-2x fa-step-forward"></i></span>
                                <span title="unlove this track" v-if="nowPlayingLoved" v-on:click.prevent="playerData.unLoveActualTrack();" class="icon btn-active"><i class="fa fa-2x fa-heart"></i></span>
                                <span title="love this track" v-else v-on:click.prevent="playerData.loveActualTrack();" class="icon"><i class="fa fa-2x fa-heart"></i></span>
                                <span title="download this track" id="btn-download" class="icon" v-on:click.prevent="playerData.downloadActualTrack();><i class="fa fa-2x fa-save"></i></span>
                            </div>
                            <div id="player-volume-control">
                                <div class="columns">
                                    <div class="column is-narrow">
                                        <span title="mute/unmute volume" class="icon has-text-white-bis" v-on:click.prevent="toggleMute">
                                            <i v-if="volume > 0" class="fa fa-2x fa-volume-up"></i>
                                            <i v-else class="fa fa-2x fa-volume-off"></i>
                                        </span>
                                    </div>
                                    <div class="column">
                                        <input id="volume-range" type="range" v-model="volume" min="0" max="1" step="0.05" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-7">
                    <div id="playlist" class="box is-paddingless is-radiusless app-content">
                        <div v-bind:id="'playlist-item-' + index" v-for="(track, index) in playerData.tracks" class="playlist-element is-clearfix has-text-light is-size-7" v-bind:class="{ 'current': playerData.actualTrack && playerData.actualTrack.id == track.id}">
                            <span v-if="playerData.actualTrack && playerData.actualTrack.id == track.id" class="is-pulled-left has-text-light">
                                <span class="icon">
                                    <i class="fa fa-2x fa-volume-up"></i>
                                </span>
                            </span>
                            <span v-else v-on:click="playerData.playAtIdx(index);" class="is-pulled-left has-text-grey-light">
                                <span class="icon">
                                    {{ index + 1 }}
                                </span>
                            </span>
                            <p class="is-pulled-left is-size-6">
                                <span class="song-name">{{ track.title }}</span><br><span class="artist-name">{{ track.artist }}</span> - <span class="album-name">{{ track.album }}</span> <span class="album-year">({{ track.year }})</span>
                            </p>
                            <span class="is-pulled-right is-size-6">{{ track.playtimeString }}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    var module = Vue.component('spieldose-player-component', {
        template: template(),
        data: function () {
            return ({
                playing: false,
                url: "",
                nowPlayingTrack: null,
                playList: [],
                repeat: false,
                shuffle: false,
                autoPlay: true,
                playerData: sharedPlayerData,
                playlist: [],
                currentTrack: null,
                currentPlayedSeconds: null,
                progressv: 0,
                volume: 1,
                preMuteVolume: 1,
                audio: null,
                vinylRotationEffect: false
            });
        },
        filters: {
            formatSeconds: function (seconds) {
                // https://stackoverflow.com/a/11234208
                function formatSecondsAsTime(secs, format) {
                    var hr = Math.floor(secs / 3600);
                    var min = Math.floor((secs - (hr * 3600)) / 60);
                    var sec = Math.floor(secs - (hr * 3600) - (min * 60));

                    if (min < 10) {
                        min = "0" + min;
                    }
                    if (sec < 10) {
                        sec = "0" + sec;
                    }

                    return min + ':' + sec;
                }
                return (formatSecondsAsTime(seconds));
            }
        },
        computed: {
            isPlaying: function () {
                return (this.playerData.isPlaying);
            },
            isPaused: function () {
                return (this.playerData.isPaused);
            },
            isMuted: function () {
                return(false);
            },
            isStopped: function () {
                return (this.playerData.isStopped);
            },
            coverSrc: function () {
                if (this.playerData.actualTrack && this.playerData.actualTrack.image) {
                    return ("/api/thumbnail?url=" + this.playerData.actualTrack.image);
                } else {
                    return ('images/vinyl.png');
                }
            },
            streamUrl: function () {
                if (this.playerData.actualTrack && this.playerData.actualTrack.id) {
                    return ("api/track/get/" + this.playerData.actualTrack.id);
                } else {
                    return ("");
                }
            },
            nowPlayingTitle: function () {
                if (this.isPlaying || this.isPaused) {
                    if (this.playerData.actualTrack.title) {
                        return (this.playerData.actualTrack.title);
                    } else {
                        return ("track title unknown");
                    }
                } else {
                    return ("track title");
                }
            },
            nowPlayingLength: function () {
                if (this.isPlaying || this.isPaused) {
                    if (this.playerData.actualTrack.playtimeString) {
                        return (this.playerData.actualTrack.playtimeString);
                    } else {
                        return ("00:00");
                    }
                } else {
                    return ("00:00");
                }
            },
            nowPlayingArtist: function () {
                if (this.isPlaying || this.isPaused) {
                    if (this.playerData.actualTrack.artist) {
                        return (this.playerData.actualTrack.artist);
                    } else {
                        return ("artist unknown");
                    }
                } else {
                    return ("artist");
                }
            },
            nowPlayingArtistAlbum: function () {
                if (this.isPlaying || this.isPaused) {
                    if (this.playerData.actualTrack.album) {
                        return (" / " + this.playerData.actualTrack.album);
                    } else {
                        return ("album unknown");
                    }
                } else {
                    return ("album");
                }
            },
            nowPlayingYear: function () {
                if (this.isPlaying || this.isPaused) {
                    if (this.playerData.actualTrack.year) {
                        return (" (" + this.playerData.actualTrack.year + ")");
                    } else {
                        return (" (year unknown)");
                    }
                } else {
                    return (" (year)");
                }
            },
            nowPlayingLoved: function () {
                return (this.playerData.hasTracks() && this.playerData.tracks[this.playerData.actualTrackIdx].loved == '1');
            }
        },

        methods: {
            toggleMute: function () {
                if (! this.audio.muted) {
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
                    var playPromise = this.audio.play();
                    if (playPromise !== undefined) {
                        playPromise.then(function () {
                        }).catch(function (error) {
                        });
                    }
                    if (this.playerData.actualTrackIdx >= 0) {
                        var element = document.getElementById("playlist-item-" + this.playerData.actualTrackIdx);
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
            var self = this;
            this.audio = document.createElement('audio');

            let aa = self.audio;
            aa.addEventListener("timeupdate", function (track) {
                var currentProgress = aa.currentTime / aa.duration;
                if (!isNaN(currentProgress)) {
                    self.progressv = currentProgress.toFixed(2);
                } else {
                    self.progressv = 0;
                }
                self.currentPlayedSeconds = Math.floor(aa.currentTime).toString();
            });
            aa.addEventListener("volumechange", function (v) {
                self.volume = aa.volume;
            });
            aa.addEventListener("ended", function() {
                self.playerData.playNextTrack();
            });
            aa.addEventListener("error", function(e) {
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
                var offset = this.getBoundingClientRect();
                var x = e.pageX - offset.left;
                self.audio.currentTime = ((parseFloat(x) / parseFloat(this.offsetWidth)) * 100) * self.audio.duration / 100;
            });
        }, created: function () {
            var self = this;
            self.playerData.loadRandomTracks(initialState.defaultResultsPage, function () {
                self.playerData.play();
            });

        }
    });

    return (module);
})();
