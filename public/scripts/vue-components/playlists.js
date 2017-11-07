"use strict";

var vTemplatePlayLists = function () {
    return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Playlists</p>
            <div class="tabs is-centered">
                <ul>
                    <li v-bind:class="{ 'is-active' : tab == 0 }"><a v-on:click.prevent="changeTab(0)" href="#">Now playing</a></li>
                    <li v-bind:class="{ 'is-active' : tab == 1 }"><a v-on:click.prevent="changeTab(1)" href="#">Playlists</a></li>
                    <li v-bind:class="{ 'is-active' : tab == 2 }"><a v-on:click.prevent="changeTab(2)" href="#">Add new playlist</a></li>
                </ul>
            </div>
            <div class="field is-grouped" v-show="tab == 0">
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="playerData.loadRandomTracks(32);">
                            <span class="icon is-small">
                                <i v-if="loading" class="fa fa-cog fa-spin fa-fw"></i>
                                <i v-else class="fa fa-clone"></i>
                            </span>
                            <span>load random playlist</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="playerData.emptyPlayList();">
                            <span class="icon is-small">
                                <i class="fa fa-eraser"></i>
                            </span>
                            <span>clear playlist</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="playerData.toggleRepeatMode();" v-bind:class="playerData.repeatTracksMode != 'none' ? 'is-primary': ''">
                            <span class="icon is-small">
                                <i class="fa fa-refresh"></i>
                            </span>
                            <span>repeat: {{ playerData.repeatTracksMode }}</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="playerData.toggleShuffleMode();" v-bind:class="playerData.shuffleTracks ? 'is-primary': ''">
                            <span class="icon is-small">
                                <i class="fa fa-random"></i>
                            </span>
                            <span>shuffle: {{ playerData.shuffleTracks ? "true": "false" }}</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="playerData.playPreviousTrack();">
                            <span class="icon is-small">
                                <i class="fa fa-backward"></i>
                            </span>
                            <span>previous</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="playerData.playNextTrack();">
                            <span class="icon is-small">
                                <i class="fa fa-forward"></i>
                            </span>
                            <span>next</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="if (! playerData.isPlaying) { playTrack(); }" :disabled="playerData.isPlaying" v-bind:class="playerData.isPlaying ? 'is-primary': ''">
                            <span class="icon is-small">
                                <i class="fa fa-play"></i>
                            </span>
                            <span>play</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="if (playerData.isPlaying) { pauseTrack(); }" :disabled="! playerData.isPlaying">
                            <span class="icon is-small">
                                <i class="fa fa-pause"></i>
                            </span>
                            <span>pause</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="if (playerData.isPlaying) { stopTrack(); }" :disabled="! playerData.isPlaying">
                            <span class="icon is-small">
                                <i class="fa fa-stop"></i>
                            </span>
                            <span>stop</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="loveTrack();">
                            <span class="icon is-small">
                                <i class="fa fa-heart"></i>
                            </span>
                            <span>love</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-light" v-on:click.prevent="downloadTrack();">
                            <span class="icon is-small">
                                <i class="fa fa-save"></i>
                            </span>
                            <span>download</span>
                        </a>
                    </p>
            </div>
            <table id="playlist-now-playing" class="table is-bordered is-striped is-narrow is-fullwidth" v-show="tab == 0">
                <thead>
                        <tr>
                            <th>Track</th>
                            <th>Artist</th>
                            <th>Album</th>
                            <th>Genre</th>
                            <th>Year</th>
                        </tr>
                </thead>
                <tbody>
                    <tr v-for="track in playerData.tracks" v-bind:class="nowPlayingTrack && nowPlayingTrack.id == track.id ? 'is-selected': ''">
                        <td>
                        <i v-if="nowPlayingTrack.id != track.id" title="play this track" class="fa fa-play cursor-pointer" aria-hidden="true"></i>
                        <i v-else title="now playing" class="fa fa-headphones cursor-pointer" aria-hidden="true" v-on:click="play(track)"></i>
                        <span> {{ track.title}}</span>
                        </td>
                        <td><a v-if="track.artist" v-bind:href="'/#/app/artist/' + $router.encodeSafeName(track.artist)" v-bind:title="'click to open artist section'">{{ track.artist }}</a></td>
                        <td><span>{{ track.album }}</span></td>
                        <td><span>{{ track.genre }}</span></td>
                        <td><span>{{ track.year }}</span></td>
                    </tr>
                </tbody>
            </table>
            <div v-show="tab == 1">
                <spieldose-pagination v-bind:data="pager" v-show="playlists.length > 0"></spieldose-pagination>
                <div class="browse-playlist-item" v-for="playlist in playlists" v-show="! loading">
                </div>
                <h2>TODO</h2>
            </div>
            <div v-show="tab == 2">
                <h2>TODO</h2>
            </div>
    </div>
    `;
}

/*
    https://stackoverflow.com/a/6274398
*/
function shuffle(array) {
    let counter = array.length;

    // While there are elements in the array
    while (counter > 0) {
        // Pick a random index
        let index = Math.floor(Math.random() * counter);

        // Decrease counter by 1
        counter--;

        // And swap the last element with it
        let temp = array[counter];
        array[counter] = array[index];
        array[index] = temp;
    }

    return array;
}

var playLists = Vue.component('spieldose-playlists', {
    template: vTemplatePlayLists(),
    data: function () {
        return ({
            tab: 0,
            loading: false,
            tracks: [],
            playlists: [],
            pager: getPager(),
            nowPlayingTrack: {},
        });
    },
    props: [ 'playerData' ],
    mounted: function () {
    }, created: function () {
        var self = this;
        bus.$on("replacePlayList", function (tracks) {
            //self.tracks = tracks;
        });
        bus.$on("nowPlayingTrack", function (track) {
            //self.nowPlayingTrack = track;
        });
        bus.$on("playTrack", function (track) {
            console.log(track);
            console.log(self);
            console.log(tracks);
            console.log(pager);
            //self.tracks.push(tracks);
        });
        bus.$on("enqueueTrack", function (track) {
            console.log(track);
            //self.tracks.push(tracks);
        });
        bus.$on("playAlbum", function (album) {
        });
        bus.$on("enqueueAlbum", function (album) {
        });
    }, methods: {
        changeTab: function (tab) {
            this.tab = tab;
        },
        play: function (track) {
            console.log(track);
        },
        /*
        shufflePlayList: function () {
            this.tracks = shuffle(this.tracks);
            this.$forceUpdate();
        },
        */
        playPreviousTrack: function() {
        },
        pauseTrack: function() {
            bus.$emit("debug", "pauseTrack");
        },
        stopTrack: function() {
            bus.$emit("debug", "stopTrack");
        },
        loveTrack: function() {
            bus.$emit("debug", "loveTrack");
        },
        unLoveTrack: function() {
            bus.$emit("debug", "unLoveTrack");
        },
        downloadTrack: function() {
            bus.$emit("debug", "downloadTrack");
        }
    }
});
