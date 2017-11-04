"use strict";

var vTemplateBrowseArtists = function () {
    return `
    <section class="section" id="section-artists">
        <div v-show="! loading">
            <spieldose-pagination v-bind:data="pager"></spieldose-pagination>
            <div class="artist_item" v-for="artist in artists">
                <a class="view_artist" v-bind:href="'/#/app/artist/' + artist.name">
                    <img class="album_cover" v-if="artist.image" v-bind:src="artist.image" />
                    <img class="album_cover" v-else src="https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png" />
                    <i class="fa fa-search fa-4x"></i>
                </a>
                <div class="artist_info">
                    <p class="artist_name">{{ artist.name }}</p>
                </div>
            </div>
        </div>
    </section>
    `;
}

var browseArtists = Vue.component('spieldose-browse-artists', {
    template: vTemplateBrowseArtists(),
    data: function () {
        return ({
            loading: false,
            artists: [],
            pager: getPager()
        });
    },
    watch: {
        '$route'(to, from) {
            this.pager.actualPage = parseInt(to.params.page);
            this.search("");
        }
    },
    created: function () {
        var self = this;
        this.pager.refresh = function () {
            self.$router.push({ name: 'artistsPaged', params: { page: self.pager.actualPage } });
        }
        if (this.$route.params.page) {
            self.pager.actualPage = parseInt(this.$route.params.page);
        }
        this.search("");
    }, methods: {
        search: function (text) {
            var self = this;
            self.loading = true;
            var d = {
                actualPage: parseInt(self.pager.actualPage),
                resultsPage: parseInt(self.pager.resultsPage)
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
                if (self.pager.actualPage < self.pager.totalPages) {
                    self.pager.nextPage = self.pager.actualPage + 1;
                } else {
                    self.pager.nextPage = self.pager.totalPages;
                }
                self.artists = response.artists;
                self.loading = false;
            });
        }
    }
});
