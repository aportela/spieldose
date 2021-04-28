import { bus } from './bus.js';
import { default as spieldoseAPI } from './api.js';
import { default as sharedPlayerData } from './playerData.js';
import { default as getValidator } from './validator.js';

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
 * album entity common mixins
 */
 export const mixinAlbums = {
    filters: {
        getAlbumImageUrl: function (value) {
            if (value) {
                if (value.indexOf("http") == 0) {
                    return ("api/thumbnail?url=" + encodeURIComponent(value));
                } else {
                    return ("api/thumbnail?hash=" + value);
                }
            } else {
                /**
                 * Vinyl disc icon credits: Jordan Green (http://www.jordangreenphoto.com/)
                 * https://jordygreen.deviantart.com/art/Vinyl-Disc-Icon-Updated-57968239
                 */
                return ("images/image-album-not-set.png");
            }
        }
    }
};

/**
 * artist entity common mixins
 */
 export const mixinArtists = {
    filters: {
        getArtistImageUrl: function (value) {
            if (value) {
                return ("api/thumbnail?url=" + value);
            } else {
                /**
                 * Music band icon credits: adiante apps (http://www.adianteapps.com/)
                 * https://www.iconfinder.com/icons/339940/band_festival_music_rock_stage_icon
                 */
                return ("images/image-artist-not-set.png");
            }
        }
    }
};

/**
 * player common mixins
 */
 export const mixinPlayer = {
    data: function () {
        return ({
            playerData: sharedPlayerData,
            currentPlayedSeconds: null,
            volume: 1,
            songProgress: 0
        });
    },
    computed: {
        isPlaying: function () {
            return (this.playerData.isPlaying);
        },
        isPaused: function () {
            return (this.playerData.isPaused);
        },
        isMuted: function () {
            return (false);
        },
        isStopped: function () {
            return (this.playerData.isStopped);
        },
        nowPlayingTitle: function () {
            if (this.isPlaying || this.isPaused) {
                if (this.playerData.currentTrack.track.title) {
                    return (this.playerData.currentTrack.track.title);
                } else {
                    return ("track title unknown");
                }
            } else {
                return ("track title");
            }
        },
        nowPlayingSeconds: function () {
            return (this.currentPlayedSeconds);
        },
        nowPlayingLength: function () {
            if (this.isPlaying || this.isPaused) {
                if (this.playerData.currentTrack.track.playtimeString) {
                    return (this.playerData.currentTrack.track.playtimeString);
                } else {
                    return ("00:00");
                }
            } else {
                return ("00:00");
            }
        },
        nowPlayingArtist: function () {
            if (this.isPlaying || this.isPaused) {
                if (this.playerData.currentTrack.track.artist) {
                    return (this.playerData.currentTrack.track.artist);
                } else {
                    return ("artist unknown");
                }
            } else {
                return ("artist");
            }
        },
        nowPlayingArtistAlbum: function () {
            if (this.isPlaying || this.isPaused) {
                if (this.playerData.currentTrack.track.album) {
                    return (" / " + this.playerData.currentTrack.track.album);
                } else {
                    return ("album unknown");
                }
            } else {
                return ("album");
            }
        },
        nowPlayingYear: function () {
            if (this.isPlaying || this.isPaused) {
                if (this.playerData.currentTrack.track.year) {
                    return (" (" + this.playerData.currentTrack.track.year + ")");
                } else {
                    return (" (year unknown)");
                }
            } else {
                return (" (year)");
            }
        },
        nowPlayingLoved: function () {
            return (this.playerData.currentTrack.track && this.playerData.currentTrack.track.loved == '1');
        }
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
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        this.playerData.currentPlaylist.replace(response.body.tracks);
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
        },
        enqueuePathTracks: function (path) {
            this.clearAPIErrors();
            spieldoseAPI.track.getPathTracks(path, (response) => {
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        this.playerData.currentPlaylist.enqueue(response.body.tracks);
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
        },
        playAlbumTracks: function (album, artist, year) {
            this.clearAPIErrors();
            spieldoseAPI.track.getAlbumTracks(album || null, artist || null, year || null, (response) => {
                this.playerData.currentPlaylist.empty();
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        this.playerData.tracks = response.body.tracks;
                        this.playerData.playback.play();
                    }
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
        },
        enqueueAlbumTracks: function (album, artist, year) {
            this.clearAPIErrors();
            spieldoseAPI.track.getAlbumTracks(album || null, artist || null, year || null, (response) => {
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        this.playerData.currentPlaylist.enqueue(response.body.tracks);
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
                    if (response.ok) {
                        this.playerData.currentPlaylist.replace(response.body.playlist.tracks);
                        this.playerData.currentPlaylist.set(id, response.body.playlist.name);
                    } else {
                        this.setAPIError(response.getApiErrorData());
                    }
                });
            } else {
                spieldoseAPI.track.searchTracks("", "", "", true, 1, 0, "random", (response) => {
                    if (response.ok) {
                        this.playerData.currentPlaylist.replace(response.body.tracks);
                        this.playerData.currentPlaylist.unset();
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
                    if (response.ok) {
                        this.playerData.currentPlaylist.enqueue(response.body.playlist.tracks);
                    } else {
                        this.setAPIError(response.getApiErrorData());
                    }
                });
            } else {
                spieldoseAPI.track.searchTracks("", "", "", true, 1, 0, "random", (response) => {
                    if (response.ok) {
                        this.playerData.currentPlaylist.enqueue(response.body.tracks);
                    } else {
                        this.setAPIError(response.getApiErrorData());
                    }
                });
            }
        },
        playTrack: function (track) {
            this.playerData.currentPlaylist.replace([track]);
        },
        enqueueTrack: function (track) {
            this.playerData.currentPlaylist.enqueue([track]);
        },
        playRadioStation: function(id) {
            this.clearAPIErrors();
            let self = this;
            spieldoseAPI.radioStation.get(id, (response) => {
                if (response.ok) {
                    let track = {
                        title: response.body.radioStation.name,
                        artist: self.$t("commonLabels.remoteRadioStation"),
                        radioStation: response.body.radioStation
                    };
                    this.playerData.currentPlaylist.replace([track]);
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
            this.playerData.currentPlaylist.replace([track]);
        },
        enqueueTrack: function (track) {
            this.playerData.currentPlaylist.enqueue([track]);
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
            bus.$emit("signOut");
        }
    }
};