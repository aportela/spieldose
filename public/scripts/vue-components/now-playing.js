"use strict";

var vTemplateNowPlaying = function () {
    return `
    <section class="section">
        <div v-show="! loading">
            <table id="t-search-results" class="table is-bordered is-striped is-narrow is-fullwidth">
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
                    <tr v-for="track in tracks">
                        <td><i title="play this track" class="fa fa-play-circle-o" aria-hidden="true" v-on:click="play(track)"></i> <i title="enqueue this track" class="fa fa-plus-square" aria-hidden="true" v-on:click="enqueue(track)"></i> <span>{{ track.title}}</span></td>
                        <td><span>{{ track.artist }}</span></td>
                        <td><span>{{ track.album }}</span></td>
                        <td><span>{{ track.genre }}</span></td>
                        <td><span>{{ track.year }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
    `;
}

var nowPlaying = Vue.component('spieldose-now-playing', {
    template: vTemplateNowPlaying(),
    data: function () {
        return ({
            loading: false,
            tracks: []
        });
    },
    created: function() {
        var self = this;
        bus.$on("replacePlayList", function (tracks) {
            self.tracks = tracks;
        });
    }, methods: {
        search: function (text) {
            var self = this;
            var d = {
                actualPage: 1,
                resultsPage: 15,
                orderBy: "random"
            };
            jsonHttpRequest("POST", "/api/track/search", d, function (httpStatusCode, response) {
                self.tracks = response.tracks;
            });
        }
    }
});
