"use strict";

/**
 * global object for events between vuejs components
 */
const bus = new Vue();

/**
 * create & return a player data object
 */
const getPlayerData = function () {
    var playerData = {
        loading: false,
        isPlaying: false,
        isPaused: false,
        isStopped: true,
        repeatTracksMode: 'none', // none | track | all
        actualTrackIdx: 0,
        actualTrack: null,
        currentPlaylistId: null,
        currentPlaylistName: null,
        tracks: []
    };
    playerData.dispose = function () {
        this.stop();
        this.actualTrackIdx = 0;
        this.actualTrack = null;
        this.tracks = [];
    };
    playerData.hasTracks = function () {
        return (playerData.tracks && playerData.tracks.length > 0);
    };
    playerData.isLastTrack = function () {
        if (playerData.tracks.length > 0) {
            if (playerData.actualTrackIdx < playerData.tracks.length - 1) {
                return (false);
            } else {
                return (true);
            }
        } else {
            return (true);
        }
    };
    playerData.loadRandomTracks = function (count, callback) {
        playerData.stop();
        playerData.tracks = [];
        playerData.loading = true;
        playerData.actualTrackIdx = 0;
        playerData.actualTrack = null;
        var d = {
            actualPage: 1,
            resultsPage: count,
            orderBy: "random"
        };
        spieldoseAPI.track.searchTracks("", "", "", false, 1, count, "random", function (response) {
            if (response.ok) {
                if (response.body.tracks && response.body.tracks.length > 0) {
                    playerData.tracks = response.body.tracks;
                    playerData.play();
                    /*
                    let songs = [];
                    for (let i = 0; i < playerData.tracks.length; i++) {
                        songs.push(
                        {
                            "id": playerData.tracks[i].id,
                            "playtimeString": playerData.tracks[i].playtimeString,
                            "name": playerData.tracks[i].title,
                            "artist": playerData.tracks[i].artist,
                            "albumArtist": playerData.tracks[i].albumArtist,
                            "album": playerData.tracks[i].album,
                            "year": playerData.tracks[i].year,
                            "genre": playerData.tracks[i].genre,
                            "url": "/api/track/get/" + playerData.tracks[i].id,
                            "image": playerData.tracks[i].image
                        }
                    );
                    }
                    bus.$emit("setPlayList", songs);
                    */
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
    playerData.hasCurrentPlayList = function () {
        if (playerData.currentPlaylistId) {
            return (true);
        } else {
            return (false);
        }
    };
    playerData.setCurrentPlayList = function (id, name) {
        playerData.currentPlaylistId = id;
        playerData.currentPlaylistName = name;
    };
    playerData.unsetCurrentPlayList = function () {
        playerData.currentPlaylistId = null;
        playerData.currentPlaylistName = null;
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
    /**
     * https://stackoverflow.com/a/6274381
     * @param {*} a
     */
    function shuffle(a) {
        var j, x, i;
        for (i = a.length - 1; i > 0; i--) {
            j = Math.floor(Math.random() * (i + 1));
            x = a[i];
            a[i] = a[j];
            a[j] = x;
        }
        return a;
    };
    playerData.shufflePlayList = function () {
        shuffle(playerData.tracks);
        playerData.playAtIdx(0);
    };
    playerData.playPreviousTrack = function () {
        if (playerData.actualTrackIdx > 0) {
            playerData.actualTrackIdx--;
            playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
            if (playerData.isPaused) {
                playerData.isPaused = false;
                playerData.isPlaying = true;
            }
        }
    };
    playerData.playNextTrack = function () {
        if (playerData.tracks.length > 0 && playerData.actualTrackIdx < playerData.tracks.length - 1) {
            playerData.actualTrackIdx++;
            playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
            if (playerData.isPaused) {
                playerData.isPaused = false;
                playerData.isPlaying = true;
            }
        }
    };
    playerData.play = function () {
        if (playerData.tracks.length > 0) {
            playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
            playerData.isPlaying = true;
            playerData.isPaused = false;
            playerData.isStopped = false;
        }
    };
    playerData.playAtIdx = function (idx) {
        if (playerData.tracks.length > 0 && idx < playerData.tracks.length) {
            playerData.actualTrackIdx = idx;
            playerData.actualTrack = playerData.tracks[playerData.actualTrackIdx];
            playerData.isPlaying = true;
            playerData.isPaused = false;
            playerData.isStopped = false;
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
            playerData.isStopped = false;
        }
    };
    playerData.resume = function () {
        if (playerData.isPaused) {
            playerData.isPaused = false;
            playerData.isPlaying = true;
            playerData.isStopped = false;
        }
    };
    playerData.stop = function () {
        if (playerData.isPlaying || playerData.isPaused) {
            playerData.isPaused = false;
            playerData.isPlaying = false;
            playerData.isStopped = true;
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
        spieldoseAPI.track.love(track.id, function (response) {
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
        spieldoseAPI.track.unlove(track.id, function (response) {
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
            if (response.status == 400 || response.status == 409) {
                // helper for find invalid fields on api response
                response.isFieldInvalid = function (fieldName) {
                    return (response.body.invalidOrMissingParams.indexOf(fieldName) > -1);
                }
            }
        }
        return (response);
    });
});

/**
 * main app component
 */
const app = new Vue({
    router,
    i18n,
    mixins: [mixinAPIError, mixinPlayer],
    data: function () {
        return ({
            logged: false,
            errors: false,
            apiError: null
        });
    },
    created: function () {
        bus.$on("signOut", () => {
            this.signOut();
        });
        if (!initialState.upgradeAvailable) {
            if (!initialState.logged) {
                this.$router.push({ name: 'signin' });
            } else {
                if (!this.$route.name) {
                    this.$router.push({ name: 'dashboard' });
                }
            }
        } else {
            this.$router.push({ name: 'upgrade' });
        }
    },
    methods: {
        signOut: function () {
            this.playerData.dispose();
            this.clearAPIErrors();
            spieldoseAPI.session.signOut((response) => {
                if (response.ok) {
                    this.$router.push({ path: '/signin' });
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
        },
        poll: function (callback) {
            spieldoseAPI.session.poll((response) => {
                if (callback && typeof callback === "function") {
                    callback(response);
                }
            });
        }
    }
}).$mount('#app');

// prevent php session lost (TODO: better management, only poll if we are logged)
setInterval(function () {
    spieldoseAPI.session.poll(function () { });
}, 300000 // 5 mins * 60 * 1000
);