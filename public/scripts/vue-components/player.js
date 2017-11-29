var player = (function () {
    "use strict";

    var template = function () {
        return `
    <div id="player" class="container is-fluid box">
        <p id="player-cover-header" v-if="playerData.isPlaying" class="title is-6 is-marginless has-text-centered has-text-light"><span class="icon is-small"><i class="fa fa-music" aria-hidden="true"></i></span> NOW PLAYING <span class="icon is-small"><i class="fa fa-music" aria-hidden="true"></i></span></p>
        <p id="player-cover-header" v-else-if="playerData.isPaused" class="title is-6 is-marginless has-text-centered has-text-light"><span class="icon is-small"><i class="fa fa-pause" aria-hidden="true"></i></span> PAUSED <span class="icon is-small"><i class="fa fa-pause" aria-hidden="true"></i></span></p>
        <p id="player-cover-header" v-else class="title is-6 is-marginless has-text-centered has-text-light"><span class="icon is-small"><i class="fa fa-stop" aria-hidden="true"></i></span> STOPPED <span class="icon is-small"><i class="fa fa-stop" aria-hidden="true"></i></span></p>
        <img id="player-cover" v-bind:src="coverSrc" />
        <div id="player-cover-footer">
            <p><span>{{ nowPlayingTitle }}</span> <span v-show="nowPlayingLength"> {{ nowPlayingLength }}</span></p>
            <p>
                <span v-if="nowPlayingArtist"><a class="title is-6 is-marginless has-text-centered has-text-light" v-if="nowPlayingArtist" v-on:click.prevent="$router.push({ name: 'artist', params: { artist: nowPlayingArtist } })">{{ nowPlayingArtist }}</a></span>
                <span v-else></span>
                <span v-if="nowPlayingArtistAlbum"><a class="title is-6 is-marginless has-text-centered has-text-light">{{ nowPlayingArtistAlbum + nowPlayingYear  }}</a></span>
                <span v-else></span>
            </p>
        </div>
        <!--
            audio visualizer https://github.com/anonymousthing/audio-visualizer
        -->
        <canvas id="canvas" class="is-marginless is-paddingless"></canvas>
        <audio id="player-audio" ref="player" controls preload="none" class="is-marginless">
            <source v-bind:src="streamUrl" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <div id="player-controls" class="is-unselectable">
            <div class="buttons has-addons is-centered">
                <span class="button" v-on:click.prevent="playerData.toggleRepeatMode();"><span class="icon"><i title="repeat" class="fa fa-refresh" v-bind:class="{ 'player-active-control': playerData.repeatTracksMode != 'none' }"></i></span></span>
                <span class="button" v-on:click.prevent="playerData.toggleShuffleMode();"><span class="icon"><i title="shuffle" class="fa fa-random" v-bind:class="{ 'player-active-control': playerData.shuffleTracks }"></i></span></span>
                <span class="button" v-on:click.prevent="playerData.playPreviousTrack();"><span class="icon"><i title="previous track" class="fa fa-backward"></i></span></span>
                <span class="button" v-if="playerData.isStopped" v-on:click.prevent="playerData.play();">
                    <span class="icon">
                        <i title="play" class="fa fa-play"></i>
                    </span>
                </span>
                <span class="button" v-else-if="playerData.isPlaying" v-on:click.prevent="playerData.pause();">
                    <span class="icon">
                        <i title="pause" class="fa fa-play player-active-control"></i>
                    </span>
                </span>
                <span class="button" v-else-if="playerData.isPaused" v-on:click.prevent="playerData.resume();">
                    <span class="icon">
                        <i title="resume" class="fa fa-pause player-active-control"></i>
                    </span>
                </span>
                <span class="button" v-on:click.prevent="playerData.playNextTrack();"><span class="icon"><i title="next track" class="fa fa-forward"></i></span></span>
                <span class="button" v-on:click.prevent="playerData.stop();"><span class="icon"><i title="stop" class="fa fa-stop" v-bind:class="{ 'player-active-control': ! playerData.isPlaying && ! playerData.isPaused }"></i></span></span>
                <span class="button" v-on:click.prevent="playerData.loadRandomTracks(32);"><span class="icon"><i title="load random playlist" class="fa fa-clone"></i></span></span>
                <span class="button" v-if="nowPlayingLoved" v-on:click.prevent="playerData.unLoveActualTrack();">
                    <span class="icon">
                        <i title="unmark as loved song" class="fa fa-heart has-text-danger"></i>
                    </span>
                </span>
                <span class="button" v-else v-on:click.prevent="playerData.loveActualTrack();">
                    <span class="icon">
                        <i  title="mark as loved song" class="fa fa-heart"></i>
                    </span>
                </span>
                <span class="button" v-on:click.prevent="playerData.downloadActualTrack();"><span class="icon"><i title="download song" class="fa fa-save"></i></span></span>
            </div>
        </div>
        <spieldose-menu-component class="is-hidden-mobile"></spieldose-menu-component>
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
                playerData: sharedPlayerData
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
                if ((this.playerData.isPlaying || this.playerData.isPaused) && this.playerData.actualTrack.image) {
                    return (this.playerData.actualTrack.image);
                } else {
                    return ('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAAGQCAIAAAAP3aGbAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAhESURBVHhe7dCBAAAAAICg/akXKYQKAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBQA1T5AAHVwlcsAAAAAElFTkSuQmCC');
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
            streamUrl: function (value) {
                if (value) {
                    if (this.isPlaying || this.isPaused) {
                        this.$refs.player.pause();
                        this.$refs.player.currentTime = 0;
                    }
                    this.$refs.player.load();
                    this.$refs.player.play();
                    initializeVisualizer(document.getElementById("canvas"), document.getElementById("player-audio"));
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
            this.$refs.player.addEventListener("ended", function () {
                switch (self.playerData.repeatTracksMode) {
                    case "track":
                        self.$refs.player.currentTime = 0;
                        self.$refs.player.play();
                        break;
                    case "all":
                        if (self.playerData.isLastTrack()) {
                            self.playerData.playAtIdx(0);
                        } else {
                            self.playerData.advancePlayList();
                        }
                        break;
                    default:
                        self.playerData.advancePlayList();
                        break;
                }
            });
        }
    });

    return (module);
})();
