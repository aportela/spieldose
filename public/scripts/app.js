"use strict";

const DEFAULT_SECTION_RESULTS_PAGE = 32;

/* global object for events between vuejs components */
const bus = new Vue();

/* change section event */
window.onhashchange = function (e) {
    bus.$emit("hashChanged", location.hash);
};


var search = Vue.component('spieldose-search', {
    template: '#search-template',
    data: function () {
        return ({
            filterByTextOn: "",
            filterByTextCondition: ""
        });
    }, props: ['section'
    ], methods: {
        search() {
            switch (this.filterByTextOn) {
                case "artists":
                    if (this.section != "#/artists") {
                        bus.$emit("activateSection", "#/artists");
                    }
                    bus.$emit("browseArtists", this.filterByTextCondition, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    break;
                case "albums":
                    bus.$emit("activateSection", "#/albums");
                    bus.$emit("browseAlbums", this.filterByTextCondition, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    break;
                default:
                    bus.$emit("activateSection", "#/search-results");
                    bus.$emit("globalSearch", this.filterByTextCondition, 1, DEFAULT_SECTION_RESULTS_PAGE);
                    break;
            }
        }
    }
});

var search_results = Vue.component('spieldose-search-results', {
    template: '#search-results-template',
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

/* app chart (test) component */
var chart = Vue.component('spieldose-chart', {
    template: '#chart-template',
    data: function () {
        return ({
            xhr: false,
            iconClass: 'fa-pie-chart',
            interval: 0,
            items: []
        });
    },
    created: function () {
        this.loadChartData();
    }, methods: {
        loadChartData: function () {
            var self = this;
            self.items = [];
            var url = null;
            var d = {};
            switch (this.interval) {
                case 0:
                    break;
                case 1:
                    d.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                    d.toDate = moment().format('YYYYMMDD');
                    break;
                case 2:
                    d.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                    d.toDate = moment().format('YYYYMMDD');
                    break;
                case 3:
                    d.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                    d.toDate = moment().format('YYYYMMDD');
                    break;
                case 4:
                    d.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                    d.toDate = moment().format('YYYYMMDD');
                    break;
            }
            self.xhr = true;
            switch (this.type) {
                case "topTracks":
                    jsonHttpRequest("POST", "/api/metrics/top_played_tracks", d, function (httpStatusCode, response) {
                        self.items = response.metrics;
                        self.xhr = false;
                    });
                    break;
                case "topArtists":
                    jsonHttpRequest("POST", "/api/metrics/top_artists", d, function (httpStatusCode, response) {
                        self.items = response.metrics;
                        self.xhr = false;
                    });
                    break;
                case "topGenres":
                    jsonHttpRequest("POST", "/api/metrics/top_genres", d, function (httpStatusCode, response) {
                        self.items = response.metrics;
                        self.xhr = false;
                    });
                    break;
            }
        }, changeInterval: function (i) {
            this.interval = i;
            this.loadChartData();
        }
    },
    props: ['type', 'title']
});

/* app chart (test) component */
var chart2 = Vue.component('spieldose-chart-recent', {
    template: '#chart-template-recent',
    data: function () {
        return ({
            xhr: false,
            iconClass: 'fa-pie-chart',
            items: [],
            entity: 0
        });
    },
    created: function () {
        this.loadChartData();
    }, methods: {
        loadChartData: function () {
            var self = this;
            self.items = [];
            var url = null;
            if (this.type == "recentlyAdded") {
                url = '/api/metrics/recently_added';
            } else if (this.type == "recentlyPlayed") {
                url = '/api/metrics/recently_played';
            }
            var d = {};
            switch (this.entity) {
                case 0: // tracks
                    d.entity = "tracks";
                    break;
                case 1: // artists
                    d.entity = "artists";
                    break;
                case 2: // albums
                    d.entity = "albums";
                    break;
            }
            self.xhr = true;
            jsonHttpRequest("POST", url, d, function (httpStatusCode, response) {
                self.xhr = false;
                self.items = response.metrics;
            });
        }, changeEntity: function (e) {
            this.entity = e;
            this.loadChartData();
        }
    },
    props: ['type', 'title']
});

var dashboard = Vue.component('spieldose-dashboard', {
    template: '#dashboard-template',
    data: function () {
        return ({
            xhr: false
        });
    },
    props: [
        'section',
    ], mounted: function () {
        /*
        var self = this;
        var d = {};
        self.xhr = true;
        jsonHttpRequest("POST", "/api/metrics/play_stats", d, function (httpStatusCode, response) {
            self.xhr = false;
            var d = [ 0, 0, 0, 0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
            for (var i = 0; i < response.metrics.length; i++) {
                d[response.metrics[i].hour] = response.metrics[i].total;
            }
            var ctx = document.getElementById("play_metrics_chart");
            var myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'],
                    datasets: [
                        {
                            "label": "by hour",
                            "data": d,
                            "fill": true,
                            "borderColor": "rgb(75, 192, 192)",
                            "lineTension": 0.1
                        }
                    ]
                }, options: {}
            });
        });
        */
    }, created: function () {
    }, methods: {
    }
});

var pagination = Vue.component('spieldose-pagination', {
    template: '#pagination-template',
    data: function () {
        return ({
            actualPage: 1,
            totalResults: 0,
            resultsPage: DEFAULT_SECTION_RESULTS_PAGE,
            totalPages: 0
        });
    }, props: ['searchEvent'
    ], computed: {
        visible: function () {
            return (this.totalResults > 0 && this.totalPages > 0);
        }
    }, created: function () {
        var self = this;
        bus.$on("updatePager", function (actualPage, totalPages, totalResults) {
            self.actualPage = actualPage;
            self.totalPages = totalPages;
            self.totalResults = totalResults;
        });
    }, methods: {
        previous: function () {
            if (this.actualPage > 1) {
                bus.$emit(this.searchEvent, null, this.actualPage - 1, this.resultsPage);
            }
        },
        next: function () {
            if (this.actualPage < this.totalPages) {
                bus.$emit(this.searchEvent, null, this.actualPage + 1, this.resultsPage);
            }
        },
        navigateTo: function (pageIdx) {
            if (pageIdx > 0 && pageIdx <= this.totalPages) {
                bus.$emit(this.searchEvent, null, pageIdx, this.resultsPage);
            }
        }
    }
});

var browseArtists = Vue.component('spieldose-browse-artists', {
    template: '#browse-artists-template',
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

var browseArtist = Vue.component('spieldose-browse-artist', {
    template: '#browse-artist-template',
    data: function () {
        return ({
            artist: {},
            detailedView: false,
        });
    }, props: ['section'
    ], created: function () {
        var self = this;
        bus.$on("loadArtist", function (artist) {
            self.getArtist(artist);
        });
    }, methods: {
        getArtist: function (artist) {
            var self = this;
            var d = {};
            jsonHttpRequest("GET", "/api/artist/" + encodeURIComponent(artist), d, function (httpStatusCode, response) {
                self.artist = response.artist;
                if (self.artist.bio) {
                    self.artist.bio = self.artist.bio.replace(/(?:\r\n|\r|\n)/g, '<br />')
                }
            });
        },
        playAlbum: function (album, artist) {
            bus.$emit("searchIntoPlayList", 1, DEFAULT_SECTION_RESULTS_PAGE, null, artist, album, null);
        },
        hideAlbumDetails: function () {
            this.detailedView = false;
        }, showAlbumDetails: function () {
            this.detailedView = true;
        }
    }
});

var browseAlbums = Vue.component('spieldose-browse-albums', {
    template: '#browse-albums-template',
    data: function () {
        return ({
            albums: [],
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
        bus.$on("browseAlbums", function (text, page, resultsPage) {
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
            jsonHttpRequest("POST", "/api/album/search", d, function (httpStatusCode, response) {
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
                self.albums = response.albums;
                bus.$emit("updatePager", self.pager.actualPage, self.pager.totalPages, self.pager.totalResults);
            });
        },
        enqueueAlbumTracks: function (album, artist) {
            bus.$emit("searchIntoPlayList", 1, DEFAULT_SECTION_RESULTS_PAGE, null, artist, album, null);
        }
    }
});

var browseGenres = Vue.component('spieldose-browse-genres', {
    template: '#browse-genres-template'
    , props: ['section'
    ]
});

var preferences = Vue.component('spieldose-preferences', {
    template: '#preferences-template'
    , props: ['section'
    ]
});

var container = Vue.component('spieldose-app-component', {
    template: '#spieldose-template',
    data: function () {
        return ({
            xhr: false,
            section: window.location.hash,
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
        var self = this;
        bus.$on("loadSection", function (s) {
            self.changeSection(s);
        });
        bus.$on("activateSection", function (s) {
            self.section = s;
        });
    },
    methods: {
        changeSection: function (s) {
            var self = this;
            self.section = s;
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
                            self.section = "#/artist";
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

var app = new Vue({
    el: "#app",
    data: function () {
        return ({
            logged: true,
            xhr: false
        });
    },
    created: function () {
        var self = this;
        bus.$on("signOut", function (hash) {
            self.signout();
        });
    },
    methods: {
        signout: function () {
            var self = this;
            self.xhr = true;
            jsonHttpRequest("GET", "/api/user/signout", {}, function (httpStatusCode, response, originalResponse) {
                self.xhr = false;
                self.logged = false;
                switch (httpStatusCode) {
                    case 200:
                        window.location.href = "/login";
                        break;
                    default:
                        bus.$emit("showModal", "Error", "Invalid server response: " + httpStatusCode + "\n" + originalResponse);
                        break;
                }
            });
        }
    }
});
