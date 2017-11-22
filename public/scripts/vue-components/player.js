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
            <div class="columns">
                <div class="column is-1"><i v-on:click.prevent="playerData.loadRandomTracks(32);" title="load random playlist" class="fa fa-clone fa-lg"></i></div>
                <div class="column is-1"><i title="repeat" class="fa fa-refresh fa-lg" v-on:click.prevent="playerData.toggleRepeatMode();" v-bind:class="{ 'player-active-control': playerData.repeatTracksMode != 'none' }"></i></div>
                <div class="column is-1"><i title="shuffle" class="fa fa-random fa-lg" v-on:click.prevent="playerData.toggleShuffleMode();" v-bind:class="{ 'player-active-control': playerData.shuffleTracks }"></i></div>
                <div class="column is-1"><i title="previous track" v-on:click.prevent="playerData.playPreviousTrack();" class="fa fa-backward fa-lg"></i></div>
                <div class="column is-1">
                    <i title="play" v-if="playerData.isStopped" v-on:click.prevent="playerData.play();" class="fa fa-play fa-lg"></i>
                    <i title="pause" v-else-if="playerData.isPlaying" v-on:click.prevent="playerData.pause();" class="fa fa-play fa-lg player-active-control"></i>
                    <i title="resume" v-else-if="playerData.isPaused" v-on:click.prevent="playerData.resume();" class="fa fa-pause fa-lg player-active-control"></i>
                </div>
                <div class="column is-1"><i title="next track" v-on:click.prevent="playerData.playNextTrack();" class="fa fa-forward fa-lg"></i></div>
                <div class="column is-1"><i title="stop" v-on:click.prevent="playerData.stop();" class="fa fa-stop fa-lg" v-bind:class="{ 'player-active-control': ! playerData.isPlaying && ! playerData.isPaused }"></i></div>
                <div class="column is-1">
                    <i v-if="nowPlayingLoved" v-on:click.prevent="playerData.unLoveActualTrack();" title="unmark as loved song" class="fa fa-heart fa-lg has-text-danger"></i>
                    <i v-else title="mark as loved song" class="fa fa-heart fa-lg" v-on:click.prevent="playerData.loveActualTrack();"></i>
                </div>
                <div class="column is-1"><i title="download song" v-on:click.prevent="playerData.downloadActualTrack();" class="fa fa-save fa-lg"></i></div>
            </div>
        </div>
        <spieldose-menu-component></spieldose-menu-component>
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
            isPaused: function() {
                return (this.playerData.isPaused);
            },
            isStopped: function() {
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
                    return("");
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
                    this.$refs.player.pause();
                    this.$refs.player.load();
                    this.$refs.player.play();
                    initializeVisualizer(document.getElementById("canvas"), document.getElementById("player-audio"));
                } else {
                    this.$refs.player.pause();
                }
            },
            isPlaying: function(newValue) {
                if (newValue) {
                    this.$refs.player.play();
                }
            },
            isPaused: function(newValue) {
                if (newValue == false) {
                    this.$refs.player.play();
                } else {
                    this.$refs.player.pause();
                }
            },
            isStopped: function(newValue) {
                if (newValue) {
                    this.$refs.player.pause();
                    this.$refs.player.currentTime = 0;
                }
            }
        },
        mounted: function () {
            this.$refs.player.addEventListener("ended", function () {
                switch (self.playerData.repeatTracksMode) {
                    case "track":
                        self.$refs.player.play();
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
