"use strict";

var vTemplateSearch = function () {
    return `
    <div>
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
                <input class="input" type="text" v-model="searchText" placeholder="search conditions..." v-on:keyup="search()">
            </p>
            <p class="control">
                <a class="button is-link" v-bind:disabled="! isEnabled" v-on:click.prevent="search()">
                    <span class="icon">
                        <i class="fa fa-search"></i>
                    </span>
                    <span>Search</span>
                </a>
            </p>
        </div>
        <div id="dropdown_search_results" v-show="results.length > 0">
            <div class="box" v-for="result in results" v-if="searchType == 2">
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
            </div>
            <div class="box" v-for="result in results" v-if="searchType == 3">
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
            searchType: "2",
            searchText: null,
            results: []
        });
    },
    computed: {
        isEnabled: function () {
            return (this.searchText && this.searchText.length > 0);
        }
    },
    methods: {
        hideResults() {
            this.results = [];
        },
        search() {
            var self = this;
            if (self.searchText && self.searchText.length > 2) {
                if (!self.xhr) {
                    var url = null;
                    switch (self.searchType) {
                        case "0":
                            break;
                        case "1":
                            //url = "/api/track/search";
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
                                    break;
                                case "1":
                                    self.results = response.tracks;
                                    break;
                                case "2":
                                    self.results = response.artists;
                                    break;
                                case "3":
                                    self.results = response.albums;
                                    break;
                            }
                        });
                    }
                }
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