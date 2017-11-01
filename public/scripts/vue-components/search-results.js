"use strict";

var vTemplateSearchResults = function () {
    return `
    <section v-show="section == '#/search-results'" class="section" id="search-results">
        <spieldose-pagination v-bind:searchEvent="'browseArtists'"></spieldose-pagination>
        <table id="t-search-results" class="table is-bordered is-striped is-narrow is-fullwidth">
            <thead>
                    <tr>
                        <th>Track</th>
                        <th>Artist</th>
                        <th>Album</th>
                    </tr>
            </thead>
            <tbody>
                    <tr v-for="track in results">
                        <td><i title="play this track" class="fa fa-play-circle-o" aria-hidden="true" v-on:click="play(track)"></i> <i title="enqueue this track" class="fa fa-plus-square" aria-hidden="true" v-on:click="enqueue(track)"></i> <span v-html="highlight(track.title)"></span></td>
                        <td><span v-html="highlight(track.artist)"></span></td>
                        <td><span v-html="highlight(track.album)"></span></td>
                    </tr>
            </tbody>
        </table>
    </section>
    `;
}

var search_results = Vue.component('spieldose-search-results', {
    template: vTemplateSearchResults(),
    data: function () {
        return ({
            text: null,
            results: [],
            pager: {
                actualPage: 1,
                previousPage: 1,
                nextPage: 1,
                totalPages: 0,
                resultsPage: DEFAULT_SECTION_RESULTS_PAGE
            }
        });
    }, props: ['section'
    ], created: function () {
        var self = this;
        bus.$on("globalSearch", function (text, page, resultsPage) {
            self.text = text;
            self.globalSearch(text, page, resultsPage);
        });
    }, filters: {
    }, methods: {
        play: function (track) {
            bus.$emit("replacePlayList", new Array(track));
        },
        enqueue: function (track) {
            bus.$emit("appendToPlayList", track);
        },
        highlight: function (words) {
            if (words) {
                return words.replace(new RegExp("(" + this.text + ")", 'gi'), '<span class="highlight">$1</span>');
            } else {
                return (null);
            }
        },
        globalSearch(text, page, resultsPage) {
            var self = this;
            self.pager.actualPage = page;
            if (page > 1) {
                self.pager.previousPage = page - 1;
            } else {
                self.pager.previousPage = 1;
            }
            var d = {
                actualPage: page,
                resultsPage: resultsPage
            };
            if (text) {
                d.text = text;
            }
            jsonHttpRequest("POST", "/api/track/search", d, function (httpStatusCode, response) {
                if (response.tracks.length > 0) {
                    self.pager.totalPages = response.totalPages;
                } else {
                    self.pager.totalPages = 0;
                }
                if (page < self.pager.totalPages) {
                    self.pager.nextPage = page + 1;
                } else {
                    self.pager.nextPage = self.pager.totalPages;
                }
                self.results = response.tracks;
                bus.$emit("updatePager", self.pager.actualPage, self.pager.totalPages, self.pager.totalResults);
            });
        }
    }
});
