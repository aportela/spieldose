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
            spieldoseAPI.track.getPathTracks(path, (response) => {
                if (response.status == 200) {
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.$player.stop();
                        this.$player.currentPlayList.tracks = response.data.tracks;
                        this.$player.currentPlayList.currentTrackIndex = 0;
                        this.$player.play();
                    }
                } else {
                    // TODO: show error
                    console.error(response);
                }
            });
        },
        enqueuePathTracks: function (path) {
            spieldoseAPI.track.getPathTracks(path, (response) => {
                if (response.status == 200) {
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.$player.currentPlayList.tracks = this.$player.currentPlayList.tracks.concat(response.data.tracks);
                    }
                } else {
                    // TODO: show error
                    console.error(response);
                }
            });
        },
        playAlbumTracks: function (album, artist, year) {
            spieldoseAPI.track.getAlbumTracks(album || null, artist || null, year || null, (response) => {
                this.$player.currentPlayList.clear();
                if (response.status == 200) {
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.$player.playTracks(response.data.tracks);
                    }
                } else {
                    // TODO: show error
                    console.error(response);
                }
            });
        },
        enqueueAlbumTracks: function (album, artist, year) {
            spieldoseAPI.track.getAlbumTracks(album || null, artist || null, year || null, (response) => {
                if (response.status == 200) {
                    if (response.data.tracks && response.data.tracks.length > 0) {
                        this.$player.enqueueTracks(response.data.tracks);
                    }
                } else {
                    // TODO: show error
                    console.error(response);
                }
            });
        },
        playPlaylistTracks: function (id) {
            if (id) {
                spieldoseAPI.playlist.get(id, (response) => {
                    if (response.status == 200) {
                        this.$player.currentPlayList.replace(response.data.playlist.tracks);
                        this.$player.currentPlayList.set(id, response.data.playlist.name);
                    } else {
                        // TODO: show error
                        console.error(response);
                    }
                });
            } else {
                spieldoseAPI.track.searchTracks("", "", "", true, 1, 0, "random", (response) => {
                    if (response.status == 200) {
                        this.$player.currentPlayList.replace(response.data.tracks);
                        this.$player.currentPlayList.unset();
                    } else {
                        // TODO: show error
                        console.error(response);
                    }
                });
            }
        },
        enqueuePlaylistTracks: function (id) {
            if (id) {
                spieldoseAPI.playlist.get(id, (response) => {
                    if (response.status == 200) {
                        this.$player.currentPlayList.enqueue(response.data.playlist.tracks);
                    } else {
                        // TODO: show error
                        console.error(response);
                    }
                });
            } else {
                spieldoseAPI.track.searchTracks("", "", "", true, 1, 0, "random", (response) => {
                    if (response.status == 200) {
                        this.$player.currentPlayList.enqueue(response.data.tracks);
                    } else {
                        // TODO: show error
                        console.error(response);
                    }
                });
            }
        },
        playTrack: function (track) {
            this.$player.playTracks([track]);
        },
        enqueueTrack: function (track) {
            this.$player.enqueueTracks([track]);
        },
        playRadioStation: function(id) {
            spieldoseAPI.radioStation.get(id, (response) => {
                if (response.status == 200) {
                    let track = {
                        title: response.data.radioStation.name,
                        artist: this.$t("commonLabels.remoteRadioStation"),
                        radioStation: response.data.radioStation
                    };
                    this.$player.stop();
                    this.$player.currentPlayList.currentTrackIndex = 0;
                    this.$player.currentPlayList.tracks = [track];
                    this.$player.play();
                } else {
                    // TODO: show error
                    console.error(response);
                }
            });
        }
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
            loading: false,
            errors: false,
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
        onPlayTrack: function (track) {
            this.$player.playTracks([ track ]);
        },
        onEnqueueTrack: function (track) {
            this.$player.enqueueTracks([ track ]);
        },
        onPlayAlbum: function (album) {
            // TODO
        },
        onEnqueueAlbum: function (album) {
            // TODO
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