"use strict";

const DEFAULT_SECTION_RESULTS_PAGE = 32;

/**
 * global object for events between vuejs components
 */
const bus = new Vue();

/**
 * create & return a pagination object
 */
const getPager = function () {
    return ({
        actualPage: 1,
        previousPage: 1,
        nextPage: 1,
        totalPages: 0,
        resultsPage: DEFAULT_SECTION_RESULTS_PAGE,
    });
}

/**
 * create & return a player data object
 */
const getPlayerData = function () {
    var playerData = {
        loading: false,
        isPlaying: false,
        isPaused: false,
        repeatTracksMode: 'none', // none | track | all
        shuffleTracks: false,
        actualTrackIdx: 0,
        actualTrack: null,
        tracks: []
    };
    playerData.hasTracks = function () {
        return (playerData.tracks && playerData.tracks.length > 0);
    };
    playerData.loadRandomTracks = function (count, callback) {
        playerData.isPaused = false;
        playerData.isPlaying = false;
        playerData.loading = true;
        playerData.actualTrackIdx = 0;
        playerData.actualTrack = null;
        var d = {
            actualPage: 1,
            resultsPage: count,
            orderBy: "random"
        };
        jsonHttpRequest("POST", "/api/track/search", d, function (httpStatusCode, response) {
            playerData.tracks = response.tracks;
            playerData.loading = false;
            if (callback) {
                callback();
            }
        });
    };
    playerData.replace = function (tracks) {
        playerData.emptyPlayList();
        playerData.tracks = tracks;
        playerData.play();
    };
    playerData.enqueue = function (tracks) {
        if (tracks.length > 0) {
            for (var i = 0; i < tracks.length; i++) {
                playerData.tracks.push(tracks[i]);
            }
        }
    };
    playerData.emptyPlayList = function () {
        playerData.isPaused = false;
        playerData.isPlaying = false;
        playerData.actualTrackIdx = 0;
        playerData.actualTrack = null;
        playerData.tracks = [];
    };
    playerData.toggleRepeatMode = function () {
        switch (playerData.repeatTracksMode) {
            case "none":
                playerData.repeatTracksMode = "track";
                break;
            case "track":
                playerData.repeatTracksMode = "all";
                break;
            default:
                playerData.repeatTracksMode = "none";
                break;
        }
    };
    playerData.toggleShuffleMode = function () {
        playerData.shuffleTracks = !playerData.shuffleTracks
    };
    playerData.playPreviousTrack = function () {
        if (playerData.actualTrackIdx > 0) {
            playerData.actualTrackIdx--;
            playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
        }
    };
    playerData.playNextTrack = function () {
        if (playerData.tracks.length > 0 && playerData.actualTrackIdx < playerData.tracks.length - 1) {
            playerData.actualTrackIdx++;
            playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
        }
    };
    playerData.play = function () {
        playerData.actualTrackIdx = 0;
        if (playerData.tracks.length > 0) {
            playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
            playerData.isPlaying = true;
            playerData.isPaused = false;
        } else {
            playerData.isPlaying = false;
        }
    };
    playerData.playAtIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length) {
            playerData.actualTrackIdx = idx;
            playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
            playerData.isPlaying = true;
            playerData.isPaused = false;
        } else {
            playerData.isPlaying = false;
        }
    };
    playerData.pause = function () {
        playerData.isPaused = true;
        playerData.isPlaying = false;
    };
    playerData.resume = function () {
        playerData.isPaused = false;
        playerData.isPlaying = true;
    };
    playerData.stop = function () {
        playerData.isPaused = false;
        playerData.isPlaying = false;
    };
    playerData.download = function (trackId) {
        if (trackId) {
            window.location = "/api/track/get/" + trackId;
        }
    };
    playerData.love = function (track) {
        playerData.loading = true;
        jsonHttpRequest("POST", "/api/track/" + track.id + "/love", {}, function (httpStatusCode, response) {
            playerData.loading = false;
            track.loved = response.loved;
        });
    };
    playerData.unlove = function (track) {
        playerData.loading = true;
        jsonHttpRequest("POST", "/api/track/" + track.id + "/unlove", {}, function (httpStatusCode, response) {
            playerData.loading = false;
            track.loved = response.loved;
        });
    };
    playerData.advancePlayList = function () {
        if (playerData.tracks.length > 0 && playerData.actualTrackIdx < playerData.tracks.length - 1) {
            playerData.playAtIdx(playerData.actualTrackIdx + 1);
        } else {
            playerData.stop();
        }
    };
    return (playerData);
}

/**
 * the player data object for share between components
 */
const sharedPlayerData = getPlayerData();

/**
 * common object for interact with API
 * all methods return callback with vue-resource response object
 */
const spieldoseAPI = {
    poll: function (callback) {
        Vue.http.get("/api/user/poll").then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    signIn: function (email, password, callback) {
        var params = {
            email: email,
            password: password
        }
        Vue.http.post("/api/user/signin", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    signOut: function (callback) {
        Vue.http.get("/api/user/signout").then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    globalSearch: function (text, actualPage, resultsPage, callback) {
        var params = {
            actualPage: 1,
            resultsPage: DEFAULT_SECTION_RESULTS_PAGE
        };
        if (actualPage) {
            params.actualPage = parseInt(actualPage);
        }
        if (resultsPage) {
            params.resultsPage = parseInt(resultsPage);
        }
        if (text) {
            params.text = text;
        }
        Vue.http.post("/api/search/global", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getArtist: function (name, callback) {
        Vue.http.get("/api/artist/" + encodeURIComponent(name)).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getAlbumTracks: function (album, artist, year, callback) {
        var params = {};
        if (album) {
            params.album = album;
        }
        if (artist) {
            params.artist = artist;
        }
        if (year) {
            params.year = year;
        }
        Vue.http.post("/api/track/search", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    searchArtists: function (name, actualPage, resultsPage, callback) {
        var params = {
            actualPage: 1,
            resultsPage: DEFAULT_SECTION_RESULTS_PAGE
        };
        if (name) {
            params.text = name;
        }
        if (actualPage) {
            params.actualPage = parseInt(actualPage);
        }
        if (resultsPage) {
            params.resultsPage = parseInt(resultsPage);
        }
        Vue.http.post("/api/artist/search", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getPlayStatMetrics: function (callback) {
        var params = {};
        Vue.http.post("/api/metrics/play_stats", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    }
};

/**
 * vue-router route definitions
 */
const routes = [
    { path: '/signin', name: 'signin', component: signIn },
    {
        path: '/app',
        component: container,
        children: [
            {
                path: 'search',
                name: 'search',
                component: search
            },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: dashboard
            },
            {
                path: 'playlists',
                name: 'playlists',
                component: playLists
            },
            {
                path: 'artists',
                name: 'artists',
                component: browseArtists,
                children: [
                    {
                        path: 'page/:page',
                        name: 'artistsPaged',
                        component: browseArtists
                    }
                ]
            },
            {
                path: 'albums',
                name: 'albums',
                component: browseAlbums,
                children: [
                    {
                        path: 'page/:page',
                        name: 'albumsPaged',
                        component: browseAlbums
                    }
                ]
            },
            {
                path: 'artist/:artist',
                name: 'artist',
                component: browseArtist
            }]
    }
];

/**
 * main vue-router component inicialization
 */
const router = new VueRouter({
    routes
});

/**
 * top scroll window before change router page
 */
router.beforeEach((to, from, next) => {
    window.scrollTo(0, 0);
    next();
});

/**
 * get safe name (with "/" encoded) for using in routes
 * @param {*} name string to encode
 */
router.encodeSafeName = function (name) {
    if (name && name.indexOf("/") > 0) {
        return (encodeURIComponent(name));
    } else {
        return (name);
    }
}

/**
 * parse vue-resource (custom) resource and return valid object for api-error component
 * @param {*} r a valid vue-resource response object
 */
const getApiErrorDataFromResponse = function (r) {
    var data = {
        request: {
            method: r.rMethod,
            url: r.rUrl,
            body: r.rBody
        },
        response: {
            status: r.status,
            statusText: r.statusText,
            text: r.bodyText
        }
    };
    data.request.headers = [];
    for (var headerName in r.rHeaders.map) {
        data.request.headers.push({ name: headerName, value: r.rHeaders.get(headerName) });
    }
    data.response.headers = [];
    for (var headerName in r.headers.map) {
        data.response.headers.push({ name: headerName, value: r.headers.get(headerName) });
    }
    return (data);
};

/**
 * vue-resource interceptor for adding (on errors) custom get data function (used in api-error component) into response
 */
Vue.http.interceptors.push((request, next) => {
    next((response) => {
        if (!response.ok) {
            response.rBody = request.body;
            response.rUrl = request.url;
            response.rMethod = request.method;
            response.rHeaders = request.headers;
            response.getApiErrorData = function () {
                return (getApiErrorDataFromResponse(response));
            };
        }
        return (response);
    });
});

/**
 * main app component
 */
const app = new Vue({
    router,
    data: function () {
        return ({
            loading: false,
            logged: false,
            errors: false,
            apiError: null
        });
    },
    created: function () {
        var self = this;
        bus.$on("signOut", function () {
            self.signOut();
        });
        bus.$on("changeRouterPath", function (routeName) {
            self.$router.push({ name: routeName });
        });
        this.poll(function (response) {
            if (!response.ok) {
                self.$router.push({ name: 'signin' });
            } else {
                self.$router.push({ name: 'dashboard' });
            }
        });
    },
    methods: {
        signOut: function () {
            var self = this;
            self.loading = true;
            self.errors = false;
            spieldoseAPI.signOut(function (response) {
                if (response.ok) {
                    self.$router.push({ path: '/signin' });
                } else {
                    self.apiError = response.getApiErrorData();
                    self.errors = true;
                    self.loading = false;
                    // TODO: show error
                }
            });
        },
        poll: function (callback) {
            var self = this;
            self.loading = true;
            spieldoseAPI.poll(function (response) {
                self.loading = false;
                callback(response);
            });
        }
    }
}).$mount('#app');

