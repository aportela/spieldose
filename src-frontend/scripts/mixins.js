import { bus } from './bus.js';
import { default as spieldoseAPI } from './api.js';
import { default as getValidator } from './validator.js';
import { default as playerClass } from './playerData.js';

/**
 * live searches common mixins
 */
export const mixinLiveSearches = {
    computed: {
        liveSearch: function () {
            return (initialState.liveSearch);
        }
    }
};

/**
 * player common mixins
 */
 export const mixinPlayer = {
    data: function () {
        return ({
            player: playerClass,
            nowPlayingCurrentTime: "00:00",
            volume: 1
        });
    },
    computed: {
        isPlaying: function () {
            return (this.$player.isPlaying);
        },
        isPaused: function () {
            return (this.$player.isPaused);
        },
        isMuted: function () {
            return (false);
        },
        isStopped: function () {
            return (this.$player.isStopped);
        },
    },
    filters: {
        formatSeconds: function (seconds) {
            // https://stackoverflow.com/a/11234208
            function formatSecondsAsTime(secs, format) {
                const hr = Math.floor(secs / 3600);
                let min = Math.floor((secs - (hr * 3600)) / 60);
                let sec = Math.floor(secs - (hr * 3600) - (min * 60));
                if (min < 10) {
                    min = "0" + min;
                }
                if (sec < 10) {
                    sec = "0" + sec;
                }
                return (min + ':' + sec);
            }
            return (formatSecondsAsTime(seconds));
        }
    },
    methods: {
        playPathTracks: function (path) {
            this.clearAPIErrors();
            spieldoseAPI.track.getPathTracks(path, (response) => {
                if (response.status == 200) {
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.$player.stop();
                        this.$player.currentPlayList.tracks = response.data.tracks;
                        this.$player.currentPlayList.currentTrackIndex = 0;
                        this.$player.play();
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
        },
        enqueuePathTracks: function (path) {
            this.clearAPIErrors();
            spieldoseAPI.track.getPathTracks(path, (response) => {
                if (response.status == 200) {
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.$player.currentPlayList.tracks = this.$player.currentPlayList.tracks.concat(response.data.tracks);
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
        },
        playAlbumTracks: function (album, artist, year) {
            this.clearAPIErrors();
            spieldoseAPI.track.getAlbumTracks(album || null, artist || null, year || null, (response) => {
                this.$player.currentPlayList.empty();
                if (response.status == 200) {
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.$player.tracks = response.data.tracks;
                        this.$player.playback.play();
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
        },
        enqueueAlbumTracks: function (album, artist, year) {
            this.clearAPIErrors();
            spieldoseAPI.track.getAlbumTracks(album || null, artist || null, year || null, (response) => {
                if (response.status == 200) {
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.$player.currentPlayList.enqueue(response.data.tracks);
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
        },
        playPlaylistTracks: function (id) {
            this.clearAPIErrors();
            if (id) {
                spieldoseAPI.playlist.get(id, (response) => {
                    if (response.status == 200) {
                        this.$player.currentPlayList.replace(response.data.playlist.tracks);
                        this.$player.currentPlayList.set(id, response.data.playlist.name);
                    } else {
                        this.setAPIError(response.getApiErrorData());
                    }
                });
            } else {
                spieldoseAPI.track.searchTracks("", "", "", true, 1, 0, "random", (response) => {
                    if (response.status == 200) {
                        this.$player.currentPlayList.replace(response.data.tracks);
                        this.$player.currentPlayList.unset();
                    } else {
                        this.setAPIError(response.getApiErrorData());
                    }
                });
            }
        },
        enqueuePlaylistTracks: function (id) {
            this.clearAPIErrors();
            if (id) {
                spieldoseAPI.playlist.get(id, (response) => {
                    if (response.status == 200) {
                        this.$player.currentPlayList.enqueue(response.data.playlist.tracks);
                    } else {
                        this.setAPIError(response.getApiErrorData());
                    }
                });
            } else {
                spieldoseAPI.track.searchTracks("", "", "", true, 1, 0, "random", (response) => {
                    if (response.status == 200) {
                        this.$player.currentPlayList.enqueue(response.data.tracks);
                    } else {
                        this.setAPIError(response.getApiErrorData());
                    }
                });
            }
        },
        playTrack: function (track) {
            this.$player.currentPlayList.replace([track]);
        },
        enqueueTrack: function (track) {
            this.$player.currentPlayList.enqueue([track]);
        },
        playRadioStation: function(id) {
            this.clearAPIErrors();
            spieldoseAPI.radioStation.get(id, (response) => {
                if (response.status == 200) {
                    let track = {
                        title: response.data.radioStation.name,
                        artist: this.$t("commonLabels.remoteRadioStation"),
                        radioStation: response.data.radioStation
                    };
                    this.$player.currentPlayList.replace([track]);
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
        }
    }
};

/**
 * validator common mixins
 */
 export const mixinValidations = {
    data: function () {
        return ({
            validator: getValidator
        });
    }
};

/**
 * api error common mixins
 */
export const mixinAPIError = {
    data: function () {
        return ({
            apiError: null
        });
    }, computed: {
        hasAPIErrors() {
            return (this.apiError != null);
        }
    }, methods: {
        clearAPIErrors() {
            this.apiError = null;
        },
        setAPIError(err) {
            this.apiError = err;
        }
    }
};

/**
 * pagination common mixins
 */
 export const mixinPagination = {
    data: function () {
        return ({
            pager: {
                actualPage: 1,
                previousPage: 1,
                nextPage: 1,
                totalPages: 0,
                resultsPage: initialState.defaultResultsPage
            }
        });
    },
    created: function () {
        if (this.$route.params.page) {
            this.pager.actualPage = parseInt(this.$route.params.page);
        }
        if (typeof this.search === "function") {
            this.search();
        }
    },
    watch: {
        '$route'(to, from) {
            if (to.params.page) {
                this.pager.actualPage = parseInt(to.params.page);
                if (typeof this.search === "function") {
                    this.search();
                }
            }
        }
    }
};

/**
 * top & recent dashboard charts mixins
 */
 export const mixinTopRecentCharts = {
    data: function () {
        return ({
            items: []
        });
    },
    created: function () {
        this.load();
    },
    computed: {
        hasItems: function () {
            return (this.items && this.items.length > 0);
        }
    },
    methods: {
        playTrack: function (track) {
            this.$player.currentPlayList.replace([track]);
        },
        enqueueTrack: function (track) {
            this.$player.currentPlayList.enqueue([track]);
        }
    }
};

/**
 * navigation mixins
 */
 export const mixinNavigation = {
    methods: {
        isSectionActive: function (section) {
            return (this.$route.name == section);
        },
        changeSection: function (routeName) {
            this.$router.push({ name: routeName }).catch(err => {});
        },
        navigateToArtistPage: function (artist) {
            if (artist) {
                this.$router.push({ name: 'artist', params: { artist: artist } });
            }
        }
    }
};

/**
 * session mixins
 */
 export const mixinSession = {
    methods: {
        signout: function () {
            bus.emit("signOut");
        }
    }
};