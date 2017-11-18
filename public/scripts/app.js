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
        playerData.tracks = [];
        playerData.loading = true;
        playerData.actualTrackIdx = 0;
        playerData.actualTrack = null;
        var d = {
            actualPage: 1,
            resultsPage: count,
            orderBy: "random"
        };
        spieldoseAPI.searchTracks(1, DEFAULT_SECTION_RESULTS_PAGE, "random", function (response) {
            if (response.ok) {
                if (response.body.tracks && response.body.tracks.length > 0) {
                    playerData.tracks = response.body.tracks;
                }
                playerData.loading = false;
                if (callback && typeof callback === "function") {
                    callback();
                }
            } else {
                // TODO: errors
                playerData.loading = false;
                if (callback && typeof callback === "function") {
                    callback();
                }
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
        if (playerData.isPlaying) {
            if (playerData.actualTrackIdx > 0) {
                playerData.actualTrackIdx--;
                playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
            }
        }
    };
    playerData.playNextTrack = function () {
        if (playerData.isPlaying) {
            if (playerData.tracks.length > 0 && playerData.actualTrackIdx < playerData.tracks.length - 1) {
                playerData.actualTrackIdx++;
                playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
            }
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
    playerData.moveUpIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx > 0) {
            var tmpTrack = playerData.tracks[idx - 1];
            playerData.tracks.splice(idx - 1, 1);
            playerData.tracks.splice(idx, 0, tmpTrack);
        }
    };
    playerData.moveDownIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length - 1) {
            var tmpTrack = playerData.tracks[idx];
            playerData.tracks.splice(idx, 1);
            playerData.tracks.splice(idx + 1, 0, tmpTrack);
        }
    };
    playerData.removeAtIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length) {
            if (idx == playerData.actualTrackIdx && (playerData.isPlaying || playerData.isPaused)) {
                playerData.playNextTrack();
            }
            playerData.tracks.splice(idx, 1);
        }
    };
    playerData.pause = function () {
        if (playerData.isPlaying) {
            playerData.isPaused = true;
            playerData.isPlaying = false;
        } else if (playerData.isPaused) {
            playerData.resume();
        }
    };
    playerData.resume = function () {
        if (playerData.isPlaying) {
            playerData.isPaused = false;
            playerData.isPlaying = true;
        }
    };
    playerData.stop = function () {
        if (playerData.isPlaying) {
            playerData.isPaused = false;
            playerData.isPlaying = false;
        }
    };
    playerData.download = function (trackId) {
        if (trackId) {
            window.location = "api/track/get/" + trackId;
        }
    };
    playerData.downloadActualTrack = function () {
        if (playerData.hasTracks()) {
            playerData.download(playerData.tracks[playerData.actualTrackIdx].id);
        }
    };
    playerData.love = function (track) {
        this.loading = true;
        spieldoseAPI.loveTrack(track.id, function (response) {
            if (response.ok) {
                playerData.loading = false;
                track.loved = response.body.loved;
            } else {
                // TODO: ERRORS
                playerData.loading = false;
            }
        });
    };
    playerData.loveActualTrack = function () {
        if (playerData.hasTracks()) {
            playerData.love(playerData.tracks[playerData.actualTrackIdx]);
        }
    };
    playerData.unlove = function (track) {
        this.loading = true;
        spieldoseAPI.unLoveTrack(track.id, function (response) {
            if (response.ok) {
                playerData.loading = false;
                track.loved = response.body.loved;
            } else {
                // TODO: ERRORS
                playerData.loading = false;
            }
        });
    };
    playerData.unLoveActualTrack = function () {
        if (playerData.hasTracks()) {
            playerData.unlove(playerData.tracks[playerData.actualTrackIdx]);
        }
    },
        playerData.advancePlayList = function () {
            if (playerData.tracks.length > 0 && playerData.actualTrackIdx < playerData.tracks.length - 1) {
                playerData.playAtIdx(playerData.actualTrackIdx + 1);
            } else {
                playerData.stop();
                playerData.play();
            }
        };
    return (playerData);
}

/**
 * the player data object for share between components
 */
const sharedPlayerData = getPlayerData();

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
        this.poll(function (response) {
            if (!response.ok) {
                self.$router.push({ name: 'signin' });
            } else {
                if (!self.$route.name) {
                    self.$router.push({ name: 'dashboard' });
                }
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
                if (callback && typeof callback === "function") {
                    callback(response);
                }
            });
        }
    }
}).$mount('#app');

