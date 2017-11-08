"use strict";

const DEFAULT_SECTION_RESULTS_PAGE = 32;

/* global object for events between vuejs components */
const bus = new Vue();

var getPager = function () {
    return ({
        actualPage: 1,
        previousPage: 1,
        nextPage: 1,
        totalPages: 0,
        resultsPage: DEFAULT_SECTION_RESULTS_PAGE,
    });
}

var getPlayerData = function () {
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
        if (playerData.actualTrack) {
            window.location = "/api/track/get/" + trackId;
        }
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

const sharedPlayerData = getPlayerData();

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

const router = new VueRouter({
    routes
});

router.beforeEach((to, from, next) => {
    window.scrollTo(0, 0);
    next();
});

router.encodeSafeName = function (name) {
    if (name && name.indexOf("/") > 0) {
        return (encodeURIComponent(name));
    } else {
        return (name);
    }
}

const app = new Vue({
    router,
    data: function () {
        return ({
            loading: false,
            logged: false
        });
    },
    created: function () {
        var self = this;
        bus.$on("signOut", function () {
            self.signout();
        });
        bus.$on("changeRouterPath", function (routeName) {
            self.$router.push({ name: routeName });
        });
        this.poll(function (logged) {
            if (!logged) {
                self.$router.push({ name: 'signin' });
            } else {
                if (!self.$router.name) {
                    self.$router.push({ name: 'dashboard' });
                }
            }
        });
    },
    methods: {
        signout: function () {
            var self = this;
            self.loading = true;
            jsonHttpRequest("GET", "/api/user/signout", {}, function (httpStatusCode, response, originalResponse) {
                self.loading = false;
                self.logged = false;
                switch (httpStatusCode) {
                    case 200:
                        self.$router.push({ path: '/signin' });
                        break;
                    default:
                        bus.$emit("showModal", "Error", "Invalid server response: " + httpStatusCode + "\n" + originalResponse);
                        break;
                }
            });
        },
        poll: function (callback) {
            var self = this;
            self.loading = true;
            jsonHttpRequest("GET", "/api/user/poll", {}, function (httpStatusCode, response, originalResponse) {
                self.loading = false;
                callback(httpStatusCode == 200);
            });
        }
    }
}).$mount('#app');

