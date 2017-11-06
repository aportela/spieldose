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
                    <tr v-for="track in tracks" v-bind:class="nowPlayingTrack.id == track.id ? 'is-selected': ''">
                        <td><i title="play this track" class="fa fa-play-circle-o" aria-hidden="true" v-on:click="play(track)"></i> <i title="enqueue this track" class="fa fa-plus-square" aria-hidden="true" v-on:click="enqueue(track)"></i> <span>{{ track.title}}</span></td>
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
            tracks: [],
            playlists: [],
            pager: getPager(),
            nowPlayingTrack: null
        });
    },
    mounted: function () {
    }, created: function () {
        var self = this;
        bus.$on("replacePlayList", function (tracks) {
            self.tracks = tracks;
        });
        bus.$on("nowPlayingTrack", function (track) {
            self.nowPlayingTrack = track;
        });
        /*
        bus.$on("enqueueNowPlayingPlayList", function (tracks) {
            self.tracks.push(tracks);
        });
        */
    }, methods: {
        changeTab: function (tab) {
            this.tab = tab;
        }
    }
});
