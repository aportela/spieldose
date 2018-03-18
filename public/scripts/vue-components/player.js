var player = (function () {
    "use strict";

    var template = function () {
        return `
            <div class="columns">
                <div class="column is-5">
                    <div id="player" class="box is-paddingless is-radiusless">
                        <img id="album-cover" v-bind:src="coverSrc">
                        <!--
                        <canvas id="canvas"></canvas>
                        -->
                        <audio id="audio" ref="player" class="" controls autoplay v-bind:src="streamUrl" />
                        <!--
                        <progress id="song-played-progress" v-bind:value="progressv" max="1"></progress>
                        -->
                        <div id="player-time-container" class="is-clearfix">
                            <span id="song-current-time" class="is-pulled-left has-text-grey">{{ currentPlayedSeconds | formatSeconds }}</span>
                            <span id="song-duration" class="is-pulled-right has-text-grey">{{ nowPlayingLength }}</span>
                        </div>
                        <div id="player-metadata-container" class="has-text-centered">
                            <p class="title is-4 has-text-light">{{ nowPlayingTitle }}</p>
                            <p class="subtitle is-5 has-text-grey-light">{{ nowPlayingArtist }}</p>
                        </div>
                        <div id="player-controls" class="is-unselectable">
                            <div class="has-text-centered" id="player-buttons">
                                <span v-bind:class="{ 'btn-active': playerData.shuffleTracks }" v-on:click.prevent="playerData.toggleShuffleMode();" class="icon"><i class="fa fa-2x fa-random"></i></span>
                                <span v-bind:class="{ 'btn-active': playerData.repeatTracksMode != 'none' }" v-on:click.prevent="playerData.toggleRepeatMode();" class="icon"><i class="fa fa-2x fa-repeat"></i></span>
                                <span id="btn-previous" v-on:click.prevent="playerData.playPreviousTrack();" class="icon"><i class="fa fa-2x fa-step-backward"></i></span>
                                <span id="btn-play-pause" v-on:click.prevent="playerData.pause();" v-if="playerData.isPlaying" class="icon has-text-white-bis"><i class="fa fa-2x fa-pause"></i></span>
                                <span id="btn-play-play" v-on:click.prevent="playerData.play();" v-else class="icon has-text-white-bis"><i class="fa fa-2x fa-play"></i></span>
                                <span id="btn-next" v-on:click.prevent="playerData.playNextTrack();" class="icon"><i class="fa fa-2x fa-step-forward"></i></span>
                                <span v-if="nowPlayingLoved" v-on:click.prevent="playerData.unLoveActualTrack();" class="icon btn-active"><i class="fa fa-2x fa-heart"></i></span>
                                <span v-else v-on:click.prevent="playerData.loveActualTrack();" class="icon"><i class="fa fa-2x fa-heart"></i></span>
                                <span id="btn-download" class="icon" v-on:click.prevent="playerData.downloadActualTrack();"title="Download current track"><i class="fa fa-2x fa-save"></i></span>
                            </div>
                            <div id="player-volume-control">
                                <div class="columns">
                                    <div class="column is-narrow">
                                        <span class="icon has-text-white-bis" v-on:click.prevent="toggleMute">
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
                    <div id="playlist" class="box is-paddingless is-radiusless	">
                        <div v-bind:id="'playlist-item-' + index" v-for="(track, index) in playerData.tracks" class="playlist-element is-clearfix has-text-light is-size-7" v-bind:class="{ 'current': playerData.actualTrack && playerData.actualTrack.id == track.id}">
                            <span v-if="playerData.actualTrack && playerData.actualTrack.id == track.id" class="is-pulled-left has-text-light"><span class="icon"><i class="fa fa-2x fa-volume-up"></i></span></span>
                            <span v-else v-on:click="playerData.playAtIdx(index);" class="is-pulled-left has-text-grey-light"><span class="icon"><i class="fa fa-2x fa-play"></i></span></span>
                            <span class="is-pulled-left has-text-grey-light is-size-7">{{ index + 1 }}</span>
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
            isMuted: function() {
                return(this.$refs.player && this.$refs.player.muted);
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
            toggleMute: function() {
                this.$refs.player.muted = ! this.$refs.player.muted;
            }
        },
        watch: {
            /*
            isMuted: function(value) {
                if (value) {
                    this.volume = 0;
                }
            },
            */
            volume: function(value) {
                this.$refs.player.volume = value;
            },
            streamUrl: function (value) {
                if (value) {
                    if (this.isPlaying || this.isPaused) {
                        this.$refs.player.pause();
                        this.$refs.player.currentTime = 0;
                    }
                    this.$refs.player.load();
                    var playPromise = this.$refs.player.play();
                    if (playPromise !== undefined) {
                        playPromise.then(function () {
                            //initializeVisualizer(document.getElementById("canvas"), document.getElementById("player-audio"));
                        }).catch(function (error) {
                        });
                    } else {
                        //initializeVisualizer(document.getElementById("canvas"), document.getElementById("player-audio"));
                    }
                    if (this.playerData.actualTrackIdx >= 0) {
                        var element = document.getElementById("playlist-item-" + this.playerData.actualTrackIdx);
                        if (element) {
                            element.scrollIntoView();
                        }
                    }
                } else {
                    this.$refs.player.pause();
                }
            },
            isPlaying: function (newValue) {
                if (newValue) {
                    this.$refs.player.play();
                }
            },
            isPaused: function (newValue) {
                if (newValue == false) {
                    this.$refs.player.play();
                } else {
                    this.$refs.player.pause();
                }
            },
            isStopped: function (newValue) {
                if (newValue) {
                    this.$refs.player.pause();
                    this.$refs.player.currentTime = 0;
                }
            }
        },
        mounted: function () {
            var self = this;
            self.playerData.loadRandomTracks(initialState.defaultResultsPage, function () {
                //initializeVisualizer(document.getElementById("canvas"), document.getElementById("audio"));
                self.playerData.play();
                /*
                function formatTime(seconds) {
                    var minutes = Math.floor(seconds / 60);
                    minutes = (minutes >= 10) ? minutes : "0" + minutes;
                    var seconds = Math.floor(seconds % 60);
                    seconds = (seconds >= 10) ? seconds : "0" + seconds;
                    return minutes + ":" + seconds;
                  }
                  */
                let aa = document.getElementsByTagName("audio")[0];
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



            });
            /*
            document.getElementById('song-played-progress').addEventListener('click', function (e) {
                var offset = this.getBoundingClientRect();
                var x = e.pageX - offset.left;
                self.$refs.player.currentTime = ((parseFloat(x) / parseFloat(this.offsetWidth)) * 100) * self.$refs.player.duration / 100;
            });
            */
        }, created: function () {
        }
    });

    return (module);
})();

window.onkeydown = function (e) {
    return !(e.keyCode == 32);
};