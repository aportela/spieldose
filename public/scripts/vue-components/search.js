"use strict";

var vTemplateSearch = function () {
    return `
    <div class="navbar-item has-dropdown is-mega" v-bind:class="hasResults ? 'is-active': ''">
        <div class="field has-addons">
            <p class="control">
                <span class="select">
                    <select v-model="searchType">
                        <option value="0">Global search</option>
                        <option value="1">Track search</option>
                        <option value="2" selected>Artist search</option>
                        <option value="3">Album search</option>
                    </select>
                </span>
            </p>
            <p class="control">
                <input class="input" type="text" v-model="searchText" placeholder="search conditions..." v-on:keyup.esc="abort()" v-on:keyup="search()">
            </p>
            <p class="control">
                <a class="button is-link" v-bind:disabled="! isEnabled" v-on:click.prevent="search()">
                    <span class="icon">
                        <i v-if="xhr" class="fa fa-cog fa-spin fa-fw"></i>
                        <i v-else class="fa fa-search"></i>
                    </span>
                    <span>Search</span>
                </a>
            </p>
        </div>
        <!--
        <div id="dropdown_search_results" v-show="results.length > 0">
            <article class="media" v-for="result in results" v-if="searchType == 2">
                <div class="media-left">
                    <figure class="image is-48x48">
                        <img v-bind:src="result.image || 'https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png'" alt="Image">
                    </figure>
                </div>
                <div class="media-content">
                    <div class="content">
                        <p><a v-bind:href="'/#/app/artist/' + result.name" v-on:click="hideResults()" v-html="highlight(result.name)"></a></p>
                    </div>
                </div>
            </article>
            <article class="media" v-for="result in results" v-if="searchType == 3">
                <div class="media-left">
                    <figure class="image is-48x48">
                        <img v-bind:src="result.image || 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='" alt="Image">
                    </figure>
                </div>
                <div class="media-content">
                    <div class="content">
                        <p><a v-bind:href="'/#/app/album/' + result.name" v-on:click="hideResults()" v-html="highlight(result.name + (result.artist ? ' / ' + result.artist: ''))"></a></p>
                    </div>
                </div>
            </article>
        </div>
        -->

        <div id="blogDropdown" class="navbar-dropdown">
            <div class="container is-fluid">
                <div class="columns">
                    <div class="column is-one-quarter" style="overflow: hidden;">
                        <h1 class="title is-6 is-mega-menu-title">Artists</h1>
                        <hr class="dropdown-divider">
                        <a class="dropdown-item" v-for="result in results.artists">
                            <article class="media">
                                <div class="media-left">
                                <figure class="image is-48x48">
                                    <img v-bind:src="result.image || 'https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png'" alt="Image">
                                </figure>
                                </div>
                                <div class="media-content">
                                    <div class="content">
                                        <p><a v-bind:href="'/#/app/artist/' + result.name" v-on:click="hideResults()" v-html="highlight(result.name)"></a></p>
                                    </div>
                                </div>
                            </article>
                        </a>
                    </div>
                    <div class="column is-one-quarter" style="overflow: hidden;">
                        <h1 class="title is-6 is-mega-menu-title">Albums</h1>
                        <hr class="dropdown-divider">
                        <a class="dropdown-item" v-for="result in results.albums">
                        <article class="media">
                            <div class="media-left">
                                <figure class="image is-48x48">
                                    <img v-bind:src="result.image || 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='" alt="Image">
                                </figure>
                            </div>
                            <div class="media-content">
                                <div class="content">
                                    <p><a v-bind:href="'/#/app/album/' + result.name" v-on:click="hideResults()" v-html="highlight(result.name + (result.artist ? ' / ' + result.artist: ''))"></a></p>
                                </div>
                            </div>
                        </article>

                        </a>
                    </div>
                    <div class="column is-one-quarter" style="overflow: hidden;">
                        <h1 class="title is-6 is-mega-menu-title">Tracks</h1>
                        <hr class="dropdown-divider">
                        <a class="dropdown-item" v-for="result in results.tracks">
                            {{ result.title }}
                            <span v-if="result.artist"> / <a v-bind:href="'/#/app/artist/' + result.artist">{{ result.artist }}</a></span>
                        </a>
                    </div>
                    <div class="column is-one-quarter">
                        <h1 class="title is-6 is-mega-menu-title">Playlists</h1>
                        <hr class="dropdown-divider">
                    </div>
                </div>
            </div>
            </div>
        </div>
      </div>

    </div>
    `;
}

var search = Vue.component('spieldose-search', {
    template: vTemplateSearch(),
    data: function () {
        return ({
            xhr: false,
            searchType: "0",
            searchText: null,
            results: {},
            timeout: null
        });
    },
    computed: {
        isEnabled: function () {
            return (this.searchText && this.searchText.length > 0);
        },
        hasResults: function () {
            return (this.results && (
                (this.results.artists && this.results.artists.length > 0) ||
                (this.results.albums && this.results.albums.length > 0) ||
                (this.results.tracks && this.results.tracks.length > 0)
            ));
        }
    },
    methods: {
        hideResults() {
            this.results = {};
        },
        abort() {
            this.searchText = "";
            this.hideResults();
        },
        search() {
            var self = this;
            if (self.searchText && self.searchText.length > 0) {

                if (self.timeout) {
                    clearTimeout(self.timeout);
                }
                self.timeout = setTimeout(function () {
                    if (!self.xhr) {
                        self.xhr = true;
                        var url = null;
                        switch (self.searchType) {
                            case "0":
                                url = "/api/search/global";
                                break;
                            case "1":
                                url = "/api/track/search";
                                break;
                            case "2":
                                url = "/api/artist/search";
                                break;
                            case "3":
                                url = "/api/album/search";
                                break;
                        }
                        if (url) {
                            var d = {
                                actualPage: 1,
                                resultsPage: 5,
                                text: self.searchText
                            };
                            jsonHttpRequest("POST", url, d, function (httpStatusCode, response) {
                                self.xhr = false;
                                switch (self.searchType) {
                                    case "0":
                                        self.results = {
                                            artists: response.artists,
                                            albums: response.albums,
                                            tracks: response.tracks
                                        };
                                        break;
                                    case "1":
                                        self.results = {
                                            artists: [],
                                            albums: [],
                                            tracks: response.tracks
                                        };
                                        break;
                                    case "2":
                                        self.results = {
                                            artists: response.artists,
                                            albums: [],
                                            tracks: []
                                        };
                                        break;
                                    case "3":
                                        self.results = {
                                            artists: [],
                                            albums: response.albums,
                                            tracks: []
                                        };
                                        break;
                                }
                            });
                        } else {
                            this.hideResults();
                        }
                    }
                }, 256);
            } else {
                this.hideResults();
            }
        },
        highlight: function (words) {
            if (words) {
                return words.replace(new RegExp("(" + this.searchText + ")", 'gi'), '<span class="highlight">$1</span>');
            } else {
                return (null);
            }
        },
    }
});