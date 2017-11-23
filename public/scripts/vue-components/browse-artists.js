var browseArtists = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Browse artists</i></p>
        <div v-if="! errors">
            <div class="field">
                <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-focus v-model="nameFilter" type="text" placeholder="search artist name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <span class="icon is-small is-left">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
            </div>
            <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-show="artists.length > 0"></spieldose-pagination>
            <!--
                Music band icon credits: adiante apps (http://www.adianteapps.com/)
                https://www.iconfinder.com/icons/339940/band_festival_music_rock_stage_icon
            -->
            <div class="browse-artist-item is-pulled-left" v-for="artist in artists" v-show="! loading">
                <a v-on:click.prevent="$router.push({ name: 'artist', params: { artist: artist.name } })" v-bind:title="'click to open artist section'">
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
        <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-browse-artists', {
        template: template(),
        data: function () {
            return ({
                loading: false,
                errors: false,
                apiError: null,
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
        }, directives: {
            focus: {
                update: function (el) {
                    el.focus();
                }
            }
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
                spieldoseAPI.searchArtists(self.nameFilter, self.pager.actualPage, self.pager.resultsPage, function (response) {
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