"use strict";

var httpRequest = function (method, url, data, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.onreadystatechange = function (e) {
        if (this.readyState == 4 && xhr.status !== 0) {
            var result = null;
            try {
                result = JSON.parse(xhr.responseText);
            } catch (e) {
                console.groupCollapsed("Error parsing JSON response");
                console.log(e);
                console.log(xhr.responseText);
                console.groupEnd();
            } finally {
                callback(xhr.status, result);
            }
        }
    }
    xhr.ontimeout = function (e) {
        callback(408, null);
    };
    xhr.send(data);
}

/* global object for events between vuejs components */
var bus = new Vue();

/* change section event */
window.onhashchange = function (e) {
    bus.$emit("hashChanged", location.hash);
};

/* modal component (warning & errors) */
var modal = Vue.component('modal-component', {
    template: '#modal-template',
    data: function () {
        return ({
            visible: false,
            title: "Default modal title",
            body: "Default modal body"
        });
    },
    created: function () {
        var self = this;
        bus.$on("showModal", function (title, body) {
            self.show(title, body);
        });
    },
    methods: {
        show: function (title, body) {
            this.title = title;
            this.body = body;
            this.visible = true;
        },
        hide: function () {
            this.visible = false;
            this.$emit("closeModal");
        }
    }
});

/* signIn component */
var signIn = Vue.component('spieldose-signin-component', {
    template: '#signin-template',
    created: function () { },
    data: function () {
        return ({
            xhr: false,
            apiURL: "",
            invalidUsername: false,
            invalidPassword: false
        });
    },
    methods: {
        submit: function (e) {
            var self = this;
            self.invalidUsername = false;
            self.invalidPassword = false;
            self.xhr = true;
            httpRequest($("form#f_signin").attr("method"), $("form#f_signin").attr("action"), new FormData($("form#f_signin")[0]), function (httpStatusCode, response) {
                self.xhr = false;
                switch (httpStatusCode) {
                    case 404:
                        self.invalidUsername = true;
                        break;
                    case 200:
                        if (!response) {
                            bus.$emit("showModal", "Error", "Server error (no response)");
                        } else {
                            if (!response.success) {
                                self.invalidPassword = true;
                            } else {
                                app.logged = true;
                            }
                        }
                        break;
                    default:
                        bus.$emit("showModal", "Error", "Invalid server response: " + httpStatusCode);
                        break;
                }
            });
        }
    }
});

/* app (logged) menu component */
var menu = Vue.component('spieldose-menu-component', {
    template: '#menu-template',
    data: function () {
        return ({
            xhr: false,
            section: "#/dashboard"
        });
    },
    created: function () {
        var self = this;
        bus.$on("hashChanged", function (hash) {
            self.section = hash;
            bus.$emit("loadSection", hash);
        });
    },
    methods: {
        signout: function (e) {
            var self = this;
            self.xhr = true;
            httpRequest("POST", "/api/user/signout.php", new FormData(), function (httpStatusCode, response) {
                self.xhr = false;
                app.logged = false;
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
            items: []
        });
    },
    created: function () {
        //this.loadChartData();
    }, methods: {
        loadChartData: function () {
            var self = this;
            var url = null;
            switch (this.type) {
                default:
                    url = "/api/track/search.php";
                    break;
            }
            var fData = new FormData();
            fData.append("actualPage", 1);
            fData.append("resultsPage", 8);
            fData.append("orderBy", "random");
            self.xhr = true;
            httpRequest("POST", url, fData, function (httpStatusCode, response) {
                self.xhr = false;
                switch (self.type) {
                    default:
                        self.items = response.tracks;
                        break;
                }
            });
        }
    },
    props: ['type', 'title']
});

/* app (logged) player component */
var player = Vue.component('spieldose-player-component', {
    template: '#player-template',
    data: function () {
        return ({
            playing: false,
            url: "",
            nowPlayingTrack: null,
            playList: [],
            repeat: false,
            shuffle: false,
            autoPlay: false,
        });
    },
    created: function () {
        var self = this;
        bus.$on("replacePlayList", function (tracks) {
            self.playList = tracks;
            if (self.playing) {
                self.pause();
            }
            if (self.autoPlay) {
                self.play(self.playList[0]);
            }
        });
        this.loadRandomPlayList();
    },
    methods: {
        play: function (track) {
            this.nowPlayingTrack = track;
            this.url = "/api/track/get.php?id=" + track.id;
            this.playing = true;
        },
        pause: function() {
            this.$refs.player.pause();
            this.playing = false;
        },
        playPrevious: function () {
            var actualPlayingIdx = -1;
            for (var i = 0; i < this.playList.length && actualPlayingIdx == - 1; i++) {
                if (this.nowPlayingTrack.id == this.playList[i].id) {
                    actualPlayingIdx = i - 1;
                }
            }
            if (actualPlayingIdx > -1) {
                this.play(this.playList[actualPlayingIdx]);
            }
        },
        playNext: function () {
            var actualPlayingIdx = -1;
            for (var i = 0; i < this.playList.length && actualPlayingIdx == - 1; i++) {
                if (this.nowPlayingTrack.id == this.playList[i].id) {
                    actualPlayingIdx = i + 1;
                }
            }
            if (actualPlayingIdx > 0 && actualPlayingIdx < this.playList.length) {
                this.play(this.playList[actualPlayingIdx]);
            }
        },
        isPlayingTrack(track) {
            return (this.nowPlayingTrack != null && this.nowPlayingTrack.id == track.id);
        },
        loadRandomPlayList: function () {
            var self = this;
            var fData = new FormData();
            fData.append("actualPage", 1);
            fData.append("resultsPage", 16);
            fData.append("orderBy", "random");
            httpRequest("POST", "/api/track/search.php", fData, function (httpStatusCode, response) {
                bus.$emit("replacePlayList", response.tracks);
            });
        },
        toggleShuffle: function () {
            this.shuffle = !this.shuffle;
        }
    }, computed: {
        nowPlayingTitle: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.title);
            } else {
                return ("");
            }
        },
        nowPlayingLength: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.playtimeString);
            } else {
                return ("");
            }
        },
        nowPlayingArtist: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.artist);
            } else {
                return ("");
            }
        },
        nowPlayingArtistAlbum: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.album);
            } else {
                return ("");
            }
        },
        nowPlayingYear: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.year);
            } else {
                return ("");
            }
        },
        nowPlayingImage: function () {
            if (this.nowPlayingTrack) {
                return (this.nowPlayingTrack.image);
            } else {
                return ("");
            }
        }
    }, mounted: function () {
        this.$watch('url', function () {
            if (this.url) {
                this.$refs.player.pause();
                this.$refs.player.load();
                this.$refs.player.play();
            } else {
                this.$refs.player.pause();
            }
        });
    }
});

var container = Vue.component('spieldose-app-component', {
    template: '#spieldose-template',
    data: function () {
        return ({
            xhr: false,
            section: "#/dashboard",
            artistList: [],
            albumList: [],
            trackList: [],
            artist: null,
            pager: {
                actualPage: 1,
                previousPage: 1,
                nextPage: 1,
                totalPages: 0,
                resultsPage: 32
            },
            filterByTextOn: "",
            filterByTextCondition: ""
        });
    },
    computed: {
    },
    created: function () {
        var self = this;
        bus.$on("loadSection", function (s) {
            self.changeSection(s);
        });
    },
    methods: {
        changeSection: function (s) {
            var self = this;
            self.section = s;
            self.filterByTextCondition = "";
            switch (s) {
                case "#/artists":
                    self.filterByTextOn = "artists";
                    self.searchArtists(1);
                    break;
                case "#/albums":
                    self.filterByTextOn = "albums";
                    self.searchAlbums(1);
                    break;
                default:
                    if (s.indexOf("#/artist") >= 0) {
                        m = s.match(/#\/artist\/(.+)/);
                        if (m && m.length == 2) {
                            self.getArtist(m[1]);
                        } else {
                            // TODO
                        }
                    }
                    break;
            }
        },
        globalSearch: function () {
            switch (this.filterByTextOn) {
                case "artists":
                    if (this.section != "#/artists") {
                        this.section = "#/artists";
                    }
                    this.searchArtists(1);
                    break;
                case "albums":
                    if (this.section != "#/albums") {
                        this.section = "#/albums";
                    }
                    this.searchAlbums(1);
                    break;
            }
        },
        searchArtists: function (page) {
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
            httpRequest("POST", "/api/artist/search.php", fData, function (httpStatusCode, response) {
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
            httpRequest("POST", "/api/album/search.php", fData, function (httpStatusCode, response) {
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
            });
        },
        searchTracks: function (page, order, text, artist, album) {
            var self = this;
            self.pager.actualPage = page;
            var fData = new FormData();
            fData.append("actualPage", self.pager.actualPage);
            fData.append("resultsPage", self.pager.resultsPage);
            if (order) {
                fData.append("orderBy", order);
            }
            if (text) {
                fData.append("text", text);
            }
            if (artist) {
                fData.append("artist", artist);
            }
            if (album) {
                fData.append("album", album);
            }
            httpRequest("POST", "/api/track/search.php", fData, function (httpStatusCode, response) {
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
        },
        getArtist: function (artist) {
            var self = this;
            var fData = new FormData();
            fData.append("name", artist);
            httpRequest("POST", "/api/artist/get.php", fData, function (httpStatusCode, response) {
                self.artist = response.artist;
                if (self.artist.bio) {
                    self.artist.bio = self.artist.bio.replace(/(?:\r\n|\r|\n)/g, '<br />')
                }
                self.section = '#/artist';
            });
        }
    }, filters: {
        encodeURI: function (str) {
            return (encodeURI(str));
        }
    }, mounted: function () {}
    , components: { }
});

var app = new Vue({
    el: "#app",
    data: {
        logged: true
    },
    functions: {
    },
    components: {
        /*
        'modal-component': modal,
        'spieldose-signin-component': signIn,
        'spieldose-app-component': container
        */
    }
});
