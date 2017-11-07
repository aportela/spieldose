"use strict";

var vTemplateContainer = function () {
    return `
        <div class="hero is-fullheight is-light is-bold">
            <spieldose-debug></spieldose-debug>
            <div class="columns is-gapless" id="main_container">
                <aside id="aside-player" class="column is-3">
                    <spieldose-player-component></spieldose-player-component>
                </aside>
                <section class="column is-9">
                    <keep-alive>
                        <router-view></router-view>
                    </keep-alive>
                </section>
            </div>
        </div>
    `;
}

var container = Vue.component('spieldose-app-component', {
    template: vTemplateContainer(),
    data: function () {
        return ({
            xhr: false,
            section: null,
            urlHash: window.location.hash,
            artistList: [],
            albumList: [],
            trackList: [],
            pager: {
                actualPage: 1,
                previousPage: 1,
                nextPage: 1,
                totalPages: 0,
                resultsPage: DEFAULT_SECTION_RESULTS_PAGE
            }
        });
    },
    computed: {
    }, created: function () {
    },
    methods: {
        changeSection: function (s) {
            var self = this;
            self.filterByTextOn = "";
            switch (s) {
                case "#/artists":
                    bus.$emit("browseArtists", null, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    self.filterByTextOn = "artists";
                    break;
                case "#/albums":
                    bus.$emit("browseAlbums", null, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    self.filterByTextOn = "albums";
                    break;
                default:
                    if (s.indexOf("#/artist") >= 0) {
                        var m = s.match(/#\/artist\/(.+)/);
                        if (m && m.length == 2) {
                            bus.$emit("loadArtist", m[1]);
                        } else {
                            // TODO
                        }
                    }
                    break;
            }
        },
        globalSearch2: function () {
            switch (this.filterByTextOn) {
                case "artists":
                    if (this.section != "#/artists") {
                        this.section = "#/artists";
                    }
                    bus.$emit("browseArtists", this.filterByTextCondition, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    break;
                case "albums":
                    if (this.section != "#/albums") {
                        this.section = "#/albums";
                    }
                    bus.$emit("browseAlbums", this.filterByTextCondition, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    break;
            }
        },
        searchArtists2: function (page) {
            var self = this;
            self.pager.actualPage = page;
            if (page > 1) {
                self.pager.previousPage = page - 1;
            } else {
                self.pager.previousPage = 1;
            }
            var fData = new FormData();
            fData.append("actualPage", self.pager.actualPage);
            fData.append("resultsPage", self.pager.resultsPage);
            fData.append("text", self.filterByTextCondition);
            jsonHttpRequest("POST", "/api/artist/search.php", fData, function (httpStatusCode, response) {
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
                self.artistList = response.artists;
                bus.$emit("updatePager", self.pager.actualPage, self.pager.totalPages, self.pager.totalResults);
            });
        },
        searchAlbums: function (page) {
            var self = this;
            self.pager.actualPage = page;
            if (page > 1) {
                self.pager.previousPage = page - 1;
            } else {
                self.pager.previousPage = 1;
            }
            var fData = new FormData();
            fData.append("actualPage", self.pager.actualPage);
            fData.append("resultsPage", self.pager.resultsPage);
            fData.append("text", self.filterByTextCondition);
            jsonHttpRequest("POST", "/api/album/search.php", fData, function (httpStatusCode, response) {
                for (var i = 0; i < response.albums.length; i++) {
                    if (response.albums[i].image) {
                        response.albums[i].albumCoverUrl = response.albums[i].image;
                    } else {
                        response.albums[i].albumCoverUrl = "#";
                    }
                }
                if (response.albums.length > 0) {
                    self.pager.totalPages = response.totalPages;
                } else {
                    self.pager.totalPages = 0;
                }
                if (page < self.pager.totalPages) {
                    self.pager.nextPage = page + 1;
                } else {
                    self.pager.nextPage = self.pager.totalPages;
                }
                self.albumList = response.albums;
                bus.$emit("updatePager", self.pager.actualPage, self.pager.totalPages, self.pager.totalResults);
            });
        },
        searchTracks: function (page, order, text, artist, album) {
            var self = this;
            self.pager.actualPage = page;
            var d = {
                actualPage: page,
                resultsPage: resultsPage
            };
            if (text) {
                d.text = text;
            }
            if (artist) {
                d.artist = artist;
            }
            if (album) {
                d.album = album;
            }
            if (order) {
                d.orderBy = order;
            }
            jsonHttpRequest("POST", "/api/track/search", d, function (httpStatusCode, response) {
                for (var i = 0; i < response.tracks.length; i++) {
                    if (response.tracks[i].image) {
                        response.tracks[i].albumCoverUrl = response.tracks[i].image;
                    } else {
                        response.tracks[i].albumCoverUrl = "";
                    }
                }
                if (response.tracks.length > 0) {
                    self.pager.totalPages = response.totalPages;
                } else {
                    self.pager.totalPages = 0;
                }
                console.log(response.tracks);
                bus.$emit("replacePlayList", response.tracks);
            });
        }
    }, filters: {
        encodeURI: function (str) {
            return (encodeURI(str));
        }
    }, mounted: function () { }
    , components: {}
});
