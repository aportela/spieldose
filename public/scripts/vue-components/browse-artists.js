var browseArtists = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box is-marginless">
        <p class="title is-1 has-text-centered">Browse artists</i></p>
        <div v-if="! errors">
            <div class="field is-expanded has-addons">
                <div class="control is-expanded has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-if="liveSearch" v-model.trim="nameFilter" type="text" placeholder="search artist name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <input class="input" :disabled="loading" v-else v-model.trim="nameFilter" type="text" placeholder="search artist name..." v-on:keyup.enter="search();">
                    <span class="icon is-small is-left">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                <div class="control">
                    <div class="select">
                        <select v-model="filterNotScraped" v-on:change="instantSearch();">
                            <option value="0">All artists</option>
                            <option value="1">Artists not scraped</option>
                        </select>
                    </div>
                </div>
                <p class="control" v-if="! liveSearch">
                    <a class="button is-info" v-on:click.prevent="search();">
                        <span class="icon">
                            <i class="fas fa-search" aria-hidden="true"></i>
                        </span>
                        <span>search</span>
                    </a>
                </p>
            </div>
            <spieldose-pagination v-bind:loading="loading" v-bind:data="pager"></spieldose-pagination>
            <!--
                Music band icon credits: adiante apps (http://www.adianteapps.com/)
                https://www.iconfinder.com/icons/339940/band_festival_music_rock_stage_icon
            -->
            <div class="browse-artist-item is-pulled-left" v-for="artist in artists" v-show="! loading">
                <a v-on:click.prevent="$router.push({ name: 'artist', params: { artist: artist.name } })" v-bind:title="'click to open artist section'">
                    <img v-if="artist.image" v-bind:src="'api/thumbnail?url=' + artist.image" v-on:error="artist.image=null;"/>
                    <img v-else src="https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png" />
                    <i class="fas fa-search fa-4x"></i>
                </a>
                <div class="artist-info is-clipped">
                    <p class="artist-name has-text-centered">{{ artist.name }}</p>
                </div>
            </div>
            <div class="is-clearfix"></div>
        </div>

        <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-browse-artists', {
        template: template(),
        mixins: [mixinLiveSearches],
        data: function () {
            return ({
                loading: false,
                errors: false,
                apiError: null,
                nameFilter: null,
                timeout: null,
                artists: [],
                filterNotScraped: 0,
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
                var d = {};
                if (self.nameFilter) {
                    d.text = self.nameFilter;
                }
                spieldoseAPI.searchArtists(self.nameFilter, self.filterNotScraped == 1, self.pager.actualPage, self.pager.resultsPage, function (response) {
                    if (response.ok) {
                        self.pager.actualPage = response.body.pagination.actualPage;
                        self.pager.totalPages = response.body.pagination.totalPages;
                        self.pager.totalResults = response.body.pagination.totalResults;
                        if (response.body.artists && response.body.artists.length > 0) {
                            self.artists = response.body.artists;
                        } else {
                            self.artists = [];
                        }
                        self.loading = false;
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                        self.loading = false;
                    }
                });
            }
        }
    });

    return (module);
})();