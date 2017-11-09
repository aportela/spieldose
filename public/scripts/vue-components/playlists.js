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
                    <a class="button is-light" v-on:click.prevent="if (! playerData.loading) { playerData.loadRandomTracks(32); }" :disabled="playerData.loading">
                        <span class="icon is-small">
                            <i v-if="playerData.loading" class="fa fa-cog fa-spin fa-fw"></i>
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
                    <a class="button is-light" v-on:click.prevent="if (playerData.isPlaying) { playerData.playPreviousTrack(); }" :disabled="! playerData.isPlaying">
                        <span class="icon is-small">
                            <i class="fa fa-backward"></i>
                        </span>
                        <span>previous</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="if (playerData.isPlaying) { playerData.playNextTrack(); }" :disabled="! playerData.isPlaying">
                        <span class="icon is-small">
                            <i class="fa fa-forward"></i>
                        </span>
                        <span>next</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="if (! playerData.isPlaying) { playerData.play(); }" v-bind:class="playerData.isPlaying ? 'is-primary': ''">
                        <span class="icon is-small">
                            <i class="fa fa-play"></i>
                        </span>
                        <span>play</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="if (playerData.isPlaying) { playerData.pause(); } else if (playerData.isPaused) { playerData.resume(); }"  v-bind:class="playerData.isPaused ? 'is-primary': ''">
                        <span class="icon is-small">
                            <i class="fa fa-pause"></i>
                        </span>
                        <span>pause</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="if (playerData.isPlaying) { playerData.stop(); }">
                        <span class="icon is-small">
                            <i class="fa fa-stop"></i>
                        </span>
                        <span>stop</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light is-primary" v-if="playerData.hasTracks() && playerData.tracks[playerData.actualTrackIdx].loved == '1'" v-on:click.prevent="playerData.unlove(playerData.tracks[playerData.actualTrackIdx]);" :disabled="playerData.loading">
                        <span class="icon is-small">
                            <i class="fa fa-heart"></i>
                        </span>
                        <span>love</span>
                    </a>
                    <a class="button is-light" v-else v-on:click.prevent="playerData.love(playerData.tracks[playerData.actualTrackIdx]);" :disabled="playerData.loading">
                        <span class="icon is-small">
                            <i class="fa fa-heart"></i>
                        </span>
                        <span>love</span>
                    </a>
                </p>
                <p class="control">
                    <a class="button is-light" v-on:click.prevent="if (playerData.tracks.length > 0) { playerData.download(playerData.tracks[playerData.actualTrackIdx].id); }">
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
                    <tr v-for="track, i in playerData.tracks" v-bind:class="playerData.actualTrack && playerData.actualTrack.id == track.id ? 'is-selected': ''">
                        <td>
                        <i v-if="playerData.actualTrack && playerData.actualTrack.id != track.id" title="play this track" class="fa fa-play cursor-pointer" aria-hidden="true" v-on:click="playerData.playAtIdx(i);"></i>
                        <i v-else-if="! playerData.isPaused" title="now playing, click to pause" class="fa fa-headphones cursor-pointer" aria-hidden="true" v-on:click="playerData.pause();"></i>
                        <i v-else title="paused, click to resume" class="fa fa-pause cursor-pointer" aria-hidden="true" v-on:click="playerData.resume();"></i>
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

var playLists = Vue.component('spieldose-playlists', {
    template: vTemplatePlayLists(),
    data: function () {
        return ({
            tab: 0,
            loading: false,
            playlists: [],
            pager: getPager(),
            playerData: sharedPlayerData,
        });
    },
    methods: {
        changeTab: function (tab) {
            this.tab = tab;
        }
    }
});
