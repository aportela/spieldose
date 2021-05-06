import { createApp, reactive } from 'vue';
import { default as router } from './routes.js';
import { default as i18n } from './i18n.js';
import { bus } from './bus.js';
import { default as spieldoseAPI } from './api.js';
import { mixinAPIError, mixinPlayer } from './mixins.js';
import { default as spieldoseSettings } from './settings.js';
import { TimeScale } from 'chart.js/auto';

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
/*
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
                    return (response.data.invalidOrMissingParams.indexOf(fieldName) > -1);
                }
            }
        }
        return (response);
    });
});
*/

/**
 * main app component
 */
const spieldoseApp = {
    mixins: [mixinAPIError, mixinPlayer],
    data: function () {
        return ({
            logged: false,
            errors: false,
            apiError: null
        });
    },
    created: function () {
        bus.on("signOut", () => {
            this.signOut();
        });
        if (!initialState.upgradeAvailable) {
            if (!initialState.logged) {
                if (this.$route.name != 'signin') {
                    this.$router.push({ name: 'signin' });
                }
            } else {
                this.$router.push({ name: 'nowPlaying' });
            }
        } else {
            this.$router.push({ name: 'upgrade' });
        }
    },
    methods: {
        signOut: function () {
            //this.player.dispose();
            this.clearAPIErrors();
            spieldoseAPI.session.signOut((response) => {
                if (response.status == 200) {
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
};

let app = createApp(spieldoseApp);

let reactivePlayer = reactive({
    loading: false,
    audio: document.createElement('audio'),
    audioSettings: {
        currentVolume: spieldoseSettings.getCurrentSessionVolume(),
        preMuteVolume: 1
    },
    status: 'stopped',
    /* computed properties */
    get isPlaying() {
        return (this.status == 'playing');
    },
    get isPaused() {
        return (this.status == 'paused');
    },
    get isStopped() {
        return (this.status == 'stopped');
    },
    currentPlayList: {
        id: null,
        name: null,
        tracks: [],
        currentTrackIndex: -1,
        repeatMode: "none",
        toggleRepeatMode: function() {
            switch (this.repeatMode) {
                case "none":
                    this.repeatMode = "track";
                    break;
                case "track":
                    this.repeatMode = "all";
                    break;
                default:
                    this.repeatMode = "none";
                    break;
            }
        },
        isSet: function() {
            return(this.id ? true: false);
        },
        unset: function() {
            this.id = null;
            this.name = null;
        },
        clear: function() {
            this.tracks = [];
        },
        moveItemUp: function (idx) {
            if (this.tracks.length > 0 && idx > 0) {
                var tmpTrack = this.tracks[idx - 1];
                this.tracks.splice(idx - 1, 1);
                this.tracks.splice(idx, 0, tmpTrack);
            }
        },
        moveItemDown: function (idx) {
            if (this.tracks.length > 0 && idx < this.tracks.length - 1) {
                var tmpTrack = this.tracks[idx];
                this.tracks.splice(idx, 1);
                this.tracks.splice(idx + 1, 0, tmpTrack);
            }
        },
        removeItem: function (idx) {
            if (this.tracks.length > 0 && idx < this.tracks.length) {
                if (idx == this.currentTrackIndex) {
                    //playerData.currentPlaylist.playNext();
                    console.log("TODO: play next track after remove")
                }
                this.tracks.splice(idx, 1);
            }
        }
    },
    playPreviousTrack: function() {
        if (this.currentPlayList.currentTrackIndex > 0) {
            this.currentPlayList.currentTrackIndex--;
            this.changeCurrentTime(0);
            if (this.status == "paused") {
                this.play();
            }
        }
    },
    playNextTrack: function() {
        if (this.currentPlayList.repeatMode != 'track') {
            if (this.currentPlayList.currentTrackIndex < this.currentPlayList.tracks.length -1) {
                this.currentPlayList.currentTrackIndex++;
                this.changeCurrentTime(0);
                if (this.status == "paused") {
                    this.play();
                }
            } else if (this.repeatMode == 'all') {
                this.currentPlayList.currentTrackIndex = 0;
                this.changeCurrentTime(0);
                if (this.status == "paused") {
                    this.play();
                }
            }
        }
    },
    setLovedCurrentTrack: function () {
        if (this.currentPlayList.currentTrackIndex != -1) {
            this.loading = true;
            spieldoseAPI.track.love(this.currentPlayList.tracks[this.currentPlayList.currentTrackIndex].id, (response) => {
                if (response.status == 200) {
                    this.currentPlayList.tracks[this.currentPlayList.currentTrackIndex].loved = response.data.loved;
                } else {
                    // TODO: ERRORS
                }
                this.loading = false;
            });
        }
    },
    unSetLovedCurrentTrack: function () {
        if (this.currentPlayList.currentTrackIndex != -1) {
            this.loading = true;
            spieldoseAPI.track.unlove(this.currentPlayList.tracks[this.currentPlayList.currentTrackIndex].id, (response) => {
                if (response.status == 200) {
                    this.currentPlayList.tracks[this.currentPlayList.currentTrackIndex].loved = response.data.loved;
                } else {
                    // TODO: ERRORS
                }
                this.loading = false;
            });
        }
    },
    downloadTrack: function(id) {
        window.location = "api/track/get/" + id;
    },
    nowPlayingCurrentTime: 0,
    nowPlayingCurrentProgress: 0,
    /* computed properties */
    get currentTrack () {
        if (this.currentPlayList.currentTrackIndex < this.currentPlayList.tracks.length) {
            return(this.currentPlayList.tracks[this.currentPlayList.currentTrackIndex]);
        } else {
            return(null);
        }
    },
    play: function () {
        this.status = "playing";
        this.audio.play();
    },
    stop: function () {
        this.status = "stopped";
        this.changeCurrentTime(0);
    },
    pause: function () {
        this.status = "paused";
        this.audio.pause();
    },
    /*
    setCurrentTrackFromPlayListIndex: function (index) {
        if (index < this.currentPlayList.tracks.length) {
            this.currentPlayList.currentTrackIndex = index;
        }
    },
    */
    loadRandomTracksIntoCurrentPlayList: function (count3) {
        this.stop();
        this.currentPlayList.tracks = [];
        this.currentPlayList.currentTrackIndex = -1;
        this.loading = true;
        spieldoseAPI.track.searchTracks("", "", "", false, 1, count3, "random", (response) => {
            if (response.status == 200) {
                if (response.data.tracks && response.data.tracks.length > 0) {
                    if (response.data.tracks.length > 0) {
                        this.currentPlayList.tracks = response.data.tracks;
                        this.currentPlayList.currentTrackIndex = 0;
                    }
                }
            } else {
                // TODO: errors
            }
            this.loading = false;
        });
    },
    shuffleCurrentPlayList: function() {
        function shuffleArray(a) {
            var j, x, i;
            for (i = a.length - 1; i > 0; i--) {
                j = Math.floor(Math.random() * (i + 1));
                x = a[i];
                a[i] = a[j];
                a[j] = x;
            }
            return a;
        };
        shuffleArray(this.currentPlayList.tracks);
    },
    init() {
        this.audio.volume = this.audioSettings.currentVolume;
        this.audio.addEventListener('timeupdate', (track) => {
            this.nowPlayingCurrentTime = this.audio.currentTime;
            this.nowPlayingCurrentProgress = this.audio.currentTime / this.audio.duration;
        });
        this.audio.addEventListener('ended', () => {
            if (this.currentPlayList.repeatMode == 'track') {
                this.audio.pause();
                this.audio.currentTime = 0;
                this.audio.play();
            } else {
                this.playNextTrack();
            }
        });
        this.audio.addEventListener('error', (e) => {
            // try to load next song on playlist if errors found
            if (this.currentPlaylist) {
                this.currentPlaylist.playNextTrack();
            } else {
                // TODO
                console.error("player audio load error");
                console.error(e);
            }
        });
    },
    changeCurrentTime: function(t) {
        this.nowPlayingCurrentProgress = t;
        this.audio.currentTime = t;
    },
    changeVolume: function(volume) {
        this.audio.volume = volume;
        this.audioSettings.currentVolume = volume;
        this.audioSettings.preMuteVolume = volume;
        spieldoseSettings.setCurrentSessionVolume(volume);
    },
    get isAudioMuted() {
        return(this.audio.muted);
    },
    toggleAudioMute: function() {
        this.audio.muted = !this.audio.muted;
    }
});

app.config.globalProperties.$player = reactivePlayer;

app.use(router).use(i18n).mount('#app');

// prevent php session lost (TODO: better management, only poll if we are logged)
setInterval(
    function () {
        spieldoseAPI.session.poll(function () { });
    },
    300000 // 5 mins * 60 * 1000
);