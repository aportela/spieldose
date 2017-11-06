"use strict";

var vTemplatePlayer = function () {
    return `
    <div id="player" class="container is-fluid box">
        <p id="player-cover-header" v-if="true" class="title is-6 is-marginless has-text-centered has-text-light"><span class="icon is-small"><i class="fa fa-music" aria-hidden="true"></i></span> NOW PLAYING <span class="icon is-small"><i class="fa fa-music" aria-hidden="true"></i></span></p>
        <p id="player-cover-header" v-else class="title is-6 is-marginless has-text-centered has-text-light"><span class="icon is-small"><i class="fa fa-pause" aria-hidden="true"></i></span> NOT PLAYING <span class="icon is-small"><i class="fa fa-pause" aria-hidden="true"></i></span></p>
        <img id="player-cover" v-if="nowPlayingImage" v-bind:src="nowPlayingImage" />
        <img id="player-cover" v-else src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAAGQCAIAAAAP3aGbAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAhESURBVHhe7dCBAAAAAICg/akXKYQKAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBQA1T5AAHVwlcsAAAAAElFTkSuQmCC" />
        <div id="player-cover-footer">
            <p>{{ nowPlayingTitle }} ({{ nowPlayingLength }})</p>
            <p>
                <a class="title is-6 is-marginless has-text-centered has-text-light" v-if="nowPlayingArtist || nowPlayingArtistAlbum || nowPlayingYear" v-bind:src="'#/artist/' + nowPlayingArtist ">{{ nowPlayingArtist + (nowPlayingArtistAlbum ? " / " + nowPlayingArtistAlbum: "") + " (" + nowPlayingYear + ")" }}</a>
                <a class="title is-6 is-marginless has-text-centered has-text-light" v-else>no artist / album (year)</a>
            </p>
        </div>
        <!--
            audio visualizer https://github.com/anonymousthing/audio-visualizer
        -->
        <canvas id="canvas" class="is-marginless is-paddingless"></canvas>
        <audio id="player-audio" ref="player" controls preload="none" class="is-marginless">
            <source v-bind:src="url" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>
        <div id="player-controls" class="is-unselectable">
            <div class="columns">
                <div class="column is-2"><i v-on:click.prevent="loadRandomPlayList" title="refresh random playlist" class="fa fa-refresh fa-lg"></i></div>
                <div class="column is-2"><i title="shuffle" class="fa fa-random fa-lg" v-on:click.prevent="toggleShuffle()" v-bind:class="{ 'player-active-control': shuffle }"></i></div>
                <div class="column is-2"><i title="previous track" v-on:click.prevent="playPrevious" class="fa fa-backward fa-lg"></i></div>
                <div class="column is-2"><i title="next track" v-on:click.prevent="playNext" class="fa fa-forward fa-lg"></i></div>
                <div class="column is-2"><i title="mark as loved song" class="fa fa-heart fa-lg"></i></div>
                <div class="column is-2"><i title="download song" v-on:click.prevent="download()" class="fa fa-save fa-lg"></i></div>
            </div>
        </div>
        <spieldose-menu-component></spieldose-menu-component>
        <!--
        <ul id="player-playlist">
            <li v-for="track, i in playList"><a v-bind:class="{ 'selected' : isPlayingTrack(track) }" v-on:click="play(track)">{{ track.title + (track.artist ? ' / ' + track.artist: '') }}</span></a></li>
        </ul>
        -->
    </div>
    `;
}

var player = Vue.component('spieldose-player-component', {
    template: vTemplatePlayer(),
    data: function () {
        return ({
            playing: false,
            url: "",
            nowPlayingTrack: null,
            playList: [],
            repeat: false,
            shuffle: false,
            autoPlay: true,
        });
    },
    created: function () {
        var self = this;
        bus.$on("playTrack", function (track) {
            self.playList = [ track ];
            if (self.playing) {
                self.pause();
            }
            if (self.autoPlay) {
                if (self.playList && self.playList.length > 0) {
                    self.play(self.playList[0]);
                }
            }
        });
        bus.$on("enqueueTrack", function (track) {
            self.playList.push(track);
        });
        bus.$on("playAlbum", function (album) {
        });
        bus.$on("enqueueAlbum", function (album) {
        });
        bus.$on("replacePlayList", function (tracks) {
            self.playList = tracks;
            if (self.playing) {
                self.pause();
            }
            if (self.autoPlay) {
                if (self.playList && self.playList.length > 0) {
                    self.play(self.playList[0]);
                }
            }
        });
        bus.$on("appendToPlayList", function (track) {
            var notFound = true;
            for (var i = 0; i < self.playList.length && notFound; i++) {
                if (self.playList[i].id == track.id) {
                    notFound = false;
                }
            }
            if (notFound) {
                self.playList.push(track);
            }
        });
        bus.$on("searchIntoPlayList", function (page, resultsPage, text, artist, album) {
            self.fillFromSearch(page, resultsPage, text, artist, album, null);
        });
        this.fillFromSearch(1, DEFAULT_SECTION_RESULTS_PAGE, null, null, null, "random");
    },
    methods: {
        play: function (track) {
            bus.$emit("nowPlayingTrack", track);
            this.nowPlayingTrack = track;
            this.url = "/api/track/get/" + track.id;
            this.playing = true;
            initializeVisualizer($("canvas#canvas")[0], $("audio#player-audio")[0]);
        },
        pause: function () {
            if (this.playing) {
                this.$refs.player.pause();
                this.playing = false;
            }
        },
        playPrevious: function () {
            var actualPlayingIdx = -1;
            for (var i = 0; i < this.playList.length && actualPlayingIdx == - 1; i++) {
                if (this.nowPlayingTrack.id == this.playList[i].id) {
                    actualPlayingIdx = i - 1;
                }
            }
            if (actualPlayingIdx > -1) {
                this.play(this.playList[actualPlayingIdx]);
            }
        },
        playNext: function () {
            var actualPlayingIdx = -1;
            for (var i = 0; i < this.playList.length && actualPlayingIdx == - 1; i++) {
                if (this.nowPlayingTrack.id == this.playList[i].id) {
                    actualPlayingIdx = i + 1;
                }
            }
            if (actualPlayingIdx > 0 && actualPlayingIdx < this.playList.length) {
                this.play(this.playList[actualPlayingIdx]);
            }
        },
        isPlayingTrack(track) {
            return (this.nowPlayingTrack != null && this.nowPlayingTrack.id == track.id);
        },
        fillFromSearch: function (page, resultsPage, text, artist, album, order) {
            var self = this;
            var d = {
                actualPage: page,
                resultsPage: resultsPage
            };
            if (text) {
                d.text = text;
            }
            if (artist) {
                d.artist = artist;
            }
            if (album) {
                d.album = album;
            }
            if (order) {
                d.orderBy = order;
            }
            jsonHttpRequest("POST", "/api/track/search", d, function (httpStatusCode, response) {
                bus.$emit("replacePlayList", response && response.tracks ? response.tracks : []);
            });
        },
        toggleShuffle: function () {
            this.shuffle = !this.shuffle;
        },
        download: function () {
            window.location = "/api/track/get/" + this.nowPlayingTrack.id;
        }
    }, computed: {
        nowPlayingTitle: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.title);
            } else {
                return ("");
            }
        },
        nowPlayingLength: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.playtimeString);
            } else {
                return ("");
            }
        },
        nowPlayingArtist: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.artist);
            } else {
                return ("");
            }
        },
        nowPlayingArtistAlbum: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.album);
            } else {
                return ("");
            }
        },
        nowPlayingYear: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.year);
            } else {
                return ("");
            }
        },
        nowPlayingImage: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.image);
            } else {
                return ("");
            }
        }
    }, mounted: function () {
        this.$watch('url', function () {
            if (this.url) {
                this.$refs.player.pause();
                this.$refs.player.load();
                this.$refs.player.play();
            } else {
                this.$refs.player.pause();
            }
        });
    }
});
