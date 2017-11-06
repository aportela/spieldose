"use strict";

var vTemplateSearch = function () {
    return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Search artists, albums, tracks, playlists</p>
        <div class="field">
            <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                <input class="input" v-model="textFilter" type="text" placeholder="search..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                <span class="icon is-small is-left">
                    <i class="fa fa-search"></i>
                </span>
            </div>
        </div>

        <div class="columns">
            <div class="column is-one-quarter is-clipped">
                <h1 class="title is-6 has-text-centered">Artists</h1>
                <hr class="dropdown-divider">
                <article class="media" v-for="item, i in artists">
                    <div class="media-left">
                        <figure class="image is-48x48">
                            <img class="border-radius-50" v-if="item.image" v-bind:src="item.image" />
                            <img class="border-radius-50" v-else src="https://cdn2.iconfinder.com/data/icons/app-types-in-grey/128/app_type_festival_512px_GREY.png" />
                        </figure>
                    </div>
                    <div class="media-content">
                        <div class="content">
                            <p class="subtitle is-6"><a v-bind:title="'click to open artist section'" v-bind:href="'/#/app/artist/' + $router.encodeSafeName(item.name)">{{ item.name }}</a></p>
                        </div>
                    </div>
                </article>
            </div>
            <div class="column is-one-quarter is-clipped">
                <h1 class="title is-6 has-text-centered">Albums</h1>
                <hr class="dropdown-divider">
                <article class="media" v-for="item, i in albums">
                    <div class="media-left">
                        <figure class="image is-48x48">
                            <img class="border-radius-50" v-if="item.image" v-bind:src="item.image"/>
                            <img class="border-radius-50" v-else="" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="/>
                        </figure>
                    </div>
                    <div class="media-content">
                        <div class="content cut-text">
                            <p class="subtitle is-6"><a v-on:click="enqueueAlbumTracks(item.name, item.artist);" v-bind:title="'click to enqueue album'">{{ item.name }}</a></p>
                        </div>
                    </div>
                </article>
            </div>
            <div class="column is-one-quarter is-clipped">
                <h1 class="title is-6 has-text-centered">Tracks</h1>
                <hr class="dropdown-divider">
                <article class="media" v-for="item, i in tracks">
                    <div class="media-left">
                    </div>
                    <div class="media-content">
                        <div class="content cut-text">
                        <a v-on:click="enqueueTrack(item);" v-bind:title="'click to enqueue track'">{{ item.title }}</a>
                        <br >
                        <span v-if="item.artist">by <a v-bind:href="'/#/app/artist/' + $router.encodeSafeName(item.artist)">{{ item.artist }} <span v-if="item.album"> / {{ item.album }}</span></span>
                        </div>
                    </div>
                </article>
            </div>
            <div class="column is-one-quarter is-clipped">
                <h1 class="title is-6 has-text-centered">Playlists</h1>
                <hr class="dropdown-divider">
                <ol v-if="playLists.length > 0">
                </ol>
            </div>
        </div>

    </div>
    `;
}

var search = Vue.component('spieldose-search', {
    template: vTemplateSearch(),
    data: function () {
        return ({
            loading: false,
            textFilter: null,
            timeout: null,
            artists: [],
            albums: [],
            tracks: [],
            playLists: []
        });
    }, methods: {
        abortInstantSearch: function () {
            this.textFilter = null;
        },
        instantSearch: function () {
            var self = this;
            if (self.timeout) {
                clearTimeout(self.timeout);
            }
            self.timeout = setTimeout(function () {
                self.search();
            }, 256);
        },
        search: function () {
            var self = this;
            self.loading = true;
            var d = {
                actualPage: 1,
                resultsPage: 8
            };
            if (self.textFilter) {
                d.text = self.textFilter;
            }
            jsonHttpRequest("POST", "/api/search/global", d, function (httpStatusCode, response) {
                self.artists = response.artists;
                self.albums = response.albums;
                self.tracks = response.tracks;
                self.playLists = [];
                self.loading = false;
            });
        },
        highlight: function (text) {
            if (text && this.textFilter) {
                return text.replace(new RegExp("(" + this.textFilter + ")", 'gi'), '<span class="highlight">$1</span>');
            } else {
                return (null);
            }
        },
        enqueueAlbumTracks: function (album, artist) {
            bus.$emit("searchIntoPlayList", 1, DEFAULT_SECTION_RESULTS_PAGE, null, artist, album, null);
        },
        enqueueTrack: function (track) {
            bus.$emit("replacePlayList", [track]);
        }
    }
});

