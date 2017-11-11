"use strict";

var vTemplateBrowseArtists = function () {
    return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Browse artists</i></p>
        <div class="field">
            <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                <input class="input " v-model="nameFilter" type="text" placeholder="search artist name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                <span class="icon is-small is-left">
                    <i class="fa fa-search"></i>
                </span>
            </div>
        </div>
        <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-show="artists.length > 0"></spieldose-pagination>
        <div class="browse-artist-item is-pulled-left" v-for="artist in artists" v-show="! loading">
            <a v-bind:href="'/#/app/artist/' + $router.encodeSafeName(artist.name)" v-bind:title="'click to open artist section'">
                <img v-if="artist.image" v-bind:src="artist.image" />
                <img v-else src="https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png" />
                <i class="fa fa-search fa-4x"></i>
            </a>
            <div class="artist-info is-clipped">
                <p class="artist-name has-text-centered">{{ artist.name }}</p>
            </div>
        </div>
        <div class="is-clearfix"></div>
    </div>
    `;
}

var browseArtists = Vue.component('spieldose-browse-artists', {
    template: vTemplateBrowseArtists(),
    data: function () {
        return ({
            loading: false,
            errors: false,
            nameFilter: null,
            timeout: null,
            artists: [],
            pager: getPager()
        });
    },
    watch: {
        '$route'(to, from) {
            if (to.name == "artists" || to.name == "artistsPaged") {
                this.pager.actualPage = parseInt(to.params.page);
                this.search();
            }
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
        this.search();
    }, methods: {
        abortInstantSearch: function () {
            this.nameFilter = null;
        },
        instantSearch: function () {
            var self = this;
            if (self.timeout) {
                clearTimeout(self.timeout);
            }
            self.timeout = setTimeout(function () {
                self.pager.actualPage = 1;
                self.search();
            }, 256);
        },
        search: function () {
            var self = this;
            self.loading = true;
            self.errors = false;
            var d = {
                actualPage: parseInt(self.pager.actualPage),
                resultsPage: parseInt(self.pager.resultsPage)
            };
            if (self.nameFilter) {
                d.text = self.nameFilter;
            }
            this.$http.post("/api/artist/search", d).then(
                response => {
                    self.pager.actualPage = response.body.pagination.actualPage;
                    self.pager.totalPages = response.body.pagination.totalPages;
                    self.pager.totalResults = response.body.pagination.totalResults;
                    if (response.body.artists && response.body.artists.length > 0) {
                        self.artists = response.body.artists;
                    } else {
                        self.artists = [];
                    }
                    self.loading = false;
                },
                response => {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                    self.loading = false;
                }
            );
        }
    }
});
