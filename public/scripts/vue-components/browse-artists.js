"use strict";

var vTemplateBrowseArtists = function () {
    return `
    <section v-show="section == '#/artists'" class="section" id="section-artists">
        <spieldose-pagination v-bind:searchEvent="'browseArtists'"></spieldose-pagination>
        <div class="artist_item" v-for="artist in artists">
            <a class="view_artist" v-bind:href="'#/artist/' + artist.name">
                <img class="album_cover" v-if="artist.image" v-bind:src="artist.image" />
                <img class="album_cover" v-else src="https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png" />
                <i class="fa fa-search fa-4x"></i>
            </a>
            <div class="artist_info">
                <p class="artist_name">{{ artist.name }}</p>
            </div>
        </div>
    </section>
    `;
}

var browseArtists = Vue.component('spieldose-browse-artists', {
    template: vTemplateBrowseArtists(),
    data: function () {
        return ({
            artists: [],
            pager: {
                actualPage: 1,
                previousPage: 1,
                nextPage: 1,
                totalPages: 0,
                resultsPage: DEFAULT_SECTION_RESULTS_PAGE
            }
        });
    }, props: ['section'
    ], computed: {
    }, created: function () {
        var self = this;
        bus.$on("browseArtists", function (text, page, resultsPage) {
            self.search(text, page, resultsPage);
        });
    }, methods: {
        search: function (text, page, resultsPage) {
            var self = this;
            self.pager.actualPage = page;
            if (page > 1) {
                self.pager.previousPage = page - 1;
            } else {
                self.pager.previousPage = 1;
            }
            var d = {
                actualPage: self.pager.actualPage,
                resultsPage: self.pager.resultsPage
            };
            if (text) {
                d.text = text;
            }
            jsonHttpRequest("POST", "/api/artist/search", d, function (httpStatusCode, response) {
                if (response.artists.length > 0) {
                    self.pager.totalPages = response.totalPages;
                } else {
                    self.pager.totalPages = 0;
                }
                if (page < self.pager.totalPages) {
                    self.pager.nextPage = page + 1;
                } else {
                    self.pager.nextPage = self.pager.totalPages;
                }
                self.artists = response.artists;
                bus.$emit("updatePager", self.pager.actualPage, self.pager.totalPages, self.pager.totalResults);
            });
        }
    }
});
