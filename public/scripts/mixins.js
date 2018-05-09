/**
 * live searches common mixins
 */
const mixinLiveSearches = {
    computed: {
        liveSearch: function () {
            return (initialState.liveSearch);
        }
    }
};

/**
 * album entity common mixins
 */
const mixinAlbums = {
    filters: {
        getAlbumImageUrl: function (value) {
            if (value) {
                if (value.indexOf("http") == 0) {
                    return ("api/thumbnail?url=" + value);
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
const mixinArtists = {
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
const mixinPlayer = {
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
                if (this.playerData.actualTrack.title) {
                    return (this.playerData.actualTrack.title);
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
                if (this.playerData.actualTrack.playtimeString) {
                    return (this.playerData.actualTrack.playtimeString);
                } else {
                    return ("00:00");
                }
            } else {
                return ("00:00");
            }
        },
        nowPlayingArtist: function () {
            if (this.isPlaying || this.isPaused) {
                if (this.playerData.actualTrack.artist) {
                    return (this.playerData.actualTrack.artist);
                } else {
                    return ("artist unknown");
                }
            } else {
                return ("artist");
            }
        },
        nowPlayingArtistAlbum: function () {
            if (this.isPlaying || this.isPaused) {
                if (this.playerData.actualTrack.album) {
                    return (" / " + this.playerData.actualTrack.album);
                } else {
                    return ("album unknown");
                }
            } else {
                return ("album");
            }
        },
        nowPlayingYear: function () {
            if (this.isPlaying || this.isPaused) {
                if (this.playerData.actualTrack.year) {
                    return (" (" + this.playerData.actualTrack.year + ")");
                } else {
                    return (" (year unknown)");
                }
            } else {
                return (" (year)");
            }
        },
        nowPlayingLoved: function () {
            return (this.playerData.hasTracks() && this.playerData.tracks[this.playerData.actualTrackIdx].loved == '1');
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
            let self = this;
            self.clearAPIErrors();
            spieldoseAPI.getPathTracks(path, function (response) {
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        self.playerData.replace(response.body.tracks);
                    }
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
                self.loading = false;
            });
        },
        enqueuePathTracks: function (path) {
            let self = this;
            self.clearAPIErrors();
            spieldoseAPI.getPathTracks(path, function (response) {
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        self.playerData.enqueue(response.body.tracks);
                    }
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
                self.loading = false;
            });
        },
        playAlbumTracks: function (album, artist, year) {
            let self = this;
            spieldoseAPI.getAlbumTracks(album || null, artist || null, year || null, function (response) {
                self.playerData.emptyPlayList();
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        self.playerData.tracks = response.body.tracks;
                        self.playerData.play();
                    }
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
            });
        },
        enqueueAlbumTracks: function (album, artist, year) {
            let self = this;
            spieldoseAPI.getAlbumTracks(album || null, artist || null, year || null, function (response) {
                if (response.ok) {
                    if (response.body.tracks && response.body.tracks.length > 0) {
                        self.playerData.enqueue(response.body.tracks);
                    }
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
            });
        },
        playPlaylistTracks: function (id) {
            let self = this;
            if (id) {
                spieldoseAPI.getPlayList(id, function (response) {
                    if (response.ok) {
                        self.playerData.replace(response.body.playlist.tracks);
                        self.playerData.setCurrentPlayList(id, response.body.playlist.name);
                        self.$router.push({ name: 'nowPlaying' });
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                });
            } else {
                spieldoseAPI.searchTracks("", "", "", true, 1, 0, "random", function (response) {
                    if (response.ok) {
                        self.playerData.replace(response.body.tracks);
                        self.playerData.unsetCurrentPlayList();
                        self.$router.push({ name: 'nowPlaying' });
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                });
            }
        },
        enqueuePlaylistTracks: function (id) {
            let self = this;
            if (id) {
                spieldoseAPI.getPlayList(id, function (response) {
                    if (response.ok) {
                        self.playerData.enqueue(response.body.playlist.tracks);
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                });
            } else {
                spieldoseAPI.searchTracks("", "", "", true, 1, 0, "random", function (response) {
                    if (response.ok) {
                        self.playerData.enqueue(response.body.tracks);
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                });
            }
        },
        playTrack: function (track) {
            this.playerData.replace([track]);
        },
        enqueueTrack: function (track) {
            this.playerData.enqueue([track]);
        }
    }
};

/**
 * validator common mixins
 */
const mixinValidations = {
    data: function () {
        return ({
            validator: getValidator()
        });
    }
};

/**
 * api error common mixins
 */
const mixinAPIError = {
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
const mixinPagination = {
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
const mixinTopRecentCharts = {
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
            this.playerData.replace([track]);
        },
        enqueueTrack: function (track) {
            this.playerData.enqueue([track]);
        }
    }
};

/**
 * navigation mixins
 */
const mixinNavigation = {
    methods: {
        isSectionActive: function (section) {
            return (this.$route.name == section);
        },
        changeSection(routeName) {
            this.$router.push({ name: routeName });
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
const mixinSession = {
    methods: {
        signout: function () {
            bus.$emit("signOut");
        }
    }
};