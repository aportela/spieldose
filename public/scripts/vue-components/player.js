var player = (function () {
    "use strict";

    var template = function () {
        return `
            <div class="columns">
                <div class="column is-5">
                    <div id="player" class="box is-paddingless is-radiusless	">
                        <img id="album-cover" amplitude-song-info2="cover_art_url" amplitude-main-song-info2="true" v-bind:src="coverSrc">
                        <canvas id="canvas"></canvas>
                        <div id="player-progress-bar-container">
                            <progress id="song-played-progress" amplitude-main-song-played-progress="true" class="amplitude-song-played-progress"></progress>
                        </div>
                        <div id="player-time-container" class="is-clearfix">
                            <span id="song-current-time" class="is-pulled-left has-text-grey"><span amplitude-main-current-minutes="true" class="amplitude-current-minutes">00</span>:<span amplitude-main-current-seconds="true" class="amplitude-current-seconds">00</span></span>
                            <span id="song-duration" class="is-pulled-right has-text-grey"><span amplitude-main-duration-minutes="true" class="amplitude-duration-minutes">00</span>:<span amplitude-main-duration-seconds="true" class="amplitude-duration-seconds">00</span></span>
                        </div>
                        <div id="player-metadata-container" class="has-text-centered">
                            <p v-if="currentTrack && currentTrack.name" class="title is-4 has-text-light">{{ currentTrack.name }}</p>
                            <p v-else class="title is-4 has-text-light">&nbsp;</p>
                            <p v-if="currentTrack && currentTrack.artist" class="subtitle is-5 has-text-grey-light">{{ currentTrack.artist }}</p>
                            <p v-else class="subtitle is-5 has-text-grey-light">&nbsp;</p>
                        </div>
                        <div id="player-controls">
                            <div class="has-text-centered" id="player-buttons">
                                <span id="btn-shuffle" class="icon amplitude-shuffle amplitude-shuffle-off"><i class="fa fa-2x fa-random"></i></span>
                                <span id="btn-repeat" class="icon amplitude-repeat amplitude-repeat-of"><i class="fa fa-2x fa-repeat"></i></span>
                                <span id="btn-previous" class="icon amplitude-prev "><i class="fa fa-2x fa-step-backward"></i></span>
                                <span id="btn-play-pause" class="icon amplitude-play-pause amplitude-playing has-text-white-bis" amplitude-main-play-pause="true"><i class="fa fa-2x fa-play"></i><i class="fa fa-2x fa-pause"></i></span>
                                <span id="btn-next" class="icon amplitude-next"><i class="fa fa-2x fa-step-forward"></i></span>
                                <span id="btn-love" class="icon"><i class="fa fa-2x fa-heart"></i></span>
                                <span id="btn-download"class="icon" title="Download current track"><i class="fa fa-2x fa-save"></i></span>
                            </div>
                            <div id="player-volume-control">
                                <span class="icon amplitude-mute amplitude-not-muted">
                                    <i class="fa fa-2x fa-volume-up"></i>
                                    <i class="fa fa-2x fa-volume-off"></i>
                                </span>
                                <input type="range" class="amplitude-volume-slider" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-7">
                    <div id="playlist" class="box is-paddingless is-radiusless	">
                        <div v-for="(track, index) in playlist" class="song amplitude-play-pause playlist-element is-clearfix has-text-light is-size-7" amplitude-song-index="2" v-bind:class="{ 'current': currentTrack && currentTrack.id == track.id}">
                            <span v-if="currentTrack && currentTrack.id == track.id" class="is-pulled-left has-text-light"><span class="icon"><i class="fa fa-2x fa-volume-up"></i></span></span>
                            <span v-else class="is-pulled-left has-text-grey-light"><span class="icon"><i class="fa fa-2x fa-play"></i></span></span>
                            <span class="is-pulled-left has-text-grey-light is-size-7">{{ index + 1 }}</span>
                            <p class="is-pulled-left is-size-6">
                                <span class="song-name">{{ track.name }}</span><br><span class="artist-name">{{ track.artist }}</span> - <span class="album-name">{{ track.album }}</span> <span class="album-year">({{ track.year }})</span>
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
                currentTrack: null

            });
        },
        computed: {
            isPlaying: function () {
                return (this.playerData.isPlaying);
            },
            isPaused: function () {
                return (this.playerData.isPaused);
            },
            isStopped: function () {
                return (this.playerData.isStopped);
            },
            coverSrc: function () {
                if (this.currentTrack && this.currentTrack.image) {
                    return ("/api/thumbnail?url=" + this.currentTrack.image);
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
                        return ("(" + this.playerData.actualTrack.playtimeString + ")");
                    } else {
                        return ("(00:00)");
                    }
                } else {
                    return ("(track length)");
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
        watch: {
            /*
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
                            initializeVisualizer(document.getElementById("canvas"), document.getElementById("player-audio"));
                        }).catch(function(error) {
                        });
                    } else {
                        initializeVisualizer(document.getElementById("canvas"), document.getElementById("player-audio"));
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
            */
        },
        mounted: function () {
            var self = this;
            self.playerData.loadRandomTracks(initialState.defaultResultsPage, function () {
                //self.playerData.play();
            });
            bus.$on("setPlayList", function (songs) {
                self.playlist = songs;
                Amplitude.init({
                    "debug": true,
                    "songs": songs,
                    "volume": 100,
                    "autoplay": true,
                    "bindings": {
                        37: 'prev',
                        39: 'next',
                        32: 'play_pause'
                      },
                    "callbacks": {
                        "before_play": function () {
                            initializeVisualizer(document.getElementById("canvas"), Amplitude.audio());
                            self.currentTrack = self.playlist[Amplitude.getActiveIndex()];
                        }
                    }
                });
                document.getElementById('song-played-progress').addEventListener('click', function (e) {
                    var offset = this.getBoundingClientRect();
                    var x = e.pageX - offset.left;
                    Amplitude.setSongPlayedPercentage((parseFloat(x) / parseFloat(this.offsetWidth)) * 100);
                });


            });

        }, created: function () {
        }
    });

    return (module);
})();

window.onkeydown = function(e) {
    return !(e.keyCode == 32);
};