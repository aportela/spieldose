"use strict";

/**
 * common object for interact with API
 * all methods return callback with vue-resource response object
 */
const spieldoseAPI = {
    poll: function (callback) {
        Vue.http.get("api/user/poll").then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    signUp: function (email, password, callback) {
        var params = {
            email: email,
            password: password
        }
        Vue.http.post("api/user/signup", params).then(
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
        Vue.http.post("api/user/signin", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    signOut: function (callback) {
        Vue.http.get("api/user/signout").then(
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
            resultsPage: initialState.defaultResultsPage
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
        Vue.http.post("api/search/global", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getArtist: function (name, callback) {
        Vue.http.get("api/artist/" + encodeURIComponent(name)).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    overwriteMusicBrainzArtist: function (name, mbid, callback) {
        var params = {
            mbid: mbid
        }
        Vue.http.put("api/artist/" + encodeURIComponent(name) + "/mbid", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    clearMusicBrainzArtist: function (name, mbid, callback) {
        Vue.http.put("api/artist/" + encodeURIComponent(name) + "/mbid").then(
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
        Vue.http.post("api/track/search", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getPathTracks: function (path, trackCount, callback) {
        var params = {
            path: path
        };
        if (trackCount) {
            params.resultsPage = trackCount;
        }
        Vue.http.post("api/track/search", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    searchTracks: function (text, artist, album, loved, actualPage, resultsPage, order, callback) {
        var params = {
            actualPage: 1,
            resultsPage: initialState.defaultResultsPage
        };
        if (text) {
            params.text = text;
        }
        if (artist) {
            params.artist = artist;
        }
        if (album) {
            params.album = album;
        }
        if (loved) {
            params.loved = 1;
        }
        if (actualPage) {
            params.actualPage = actualPage;
        }
        if (resultsPage) {
            params.resultsPage = resultsPage;
        }
        if (order) {
            params.orderBy = order;
        }
        Vue.http.post("api/track/search", params).then(
            response => {
                if (callback && typeof callback === "function") {
                    callback(response);
                }
            },
            response => {
                if (callback && typeof callback === "function") {
                    callback(response);
                }
            }
        );
    },
    searchArtists: function (name, withoutMbid, actualPage, resultsPage, callback) {
        var params = {
            actualPage: 1,
            resultsPage: initialState.defaultResultsPage
        };
        if (withoutMbid) {
            params.withoutMbid = true
        }
        if (name) {
            params.partialName = name;
        }
        if (actualPage) {
            params.actualPage = parseInt(actualPage);
        }
        if (resultsPage) {
            params.resultsPage = parseInt(resultsPage);
        }
        Vue.http.post("api/artist/search", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    searchAlbums: function (name, artist, year, actualPage, resultsPage, callback) {
        var params = {
            actualPage: 1,
            resultsPage: initialState.defaultResultsPage
        };
        if (name) {
            params.partialName = name;
        }
        if (artist) {
            params.partialArtist = artist;
        }
        if (year && year > 1000 && year < 9999) {
            params.year = year;
        }
        if (actualPage) {
            params.actualPage = parseInt(actualPage);
        }
        if (resultsPage) {
            params.resultsPage = parseInt(resultsPage);
        }
        Vue.http.post("api/album/search", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    searchPlaylists: function (name, actualPage, resultsPage, callback) {
        var params = {
            actualPage: 1,
            resultsPage: initialState.defaultResultsPage
        };
        if (name) {
            params.partialName = name;
        }
        if (actualPage) {
            params.actualPage = parseInt(actualPage);
        }
        if (resultsPage) {
            params.resultsPage = parseInt(resultsPage);
        }
        Vue.http.post("api/playlist/search", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    addPlaylist: function (name, tracks, callback) {
        var params = {
            name: name,
            tracks: tracks
        };
        Vue.http.post("api/playlist/add", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    updatePlaylist: function (id, name, tracks, callback) {
        var params = {
            id: id,
            name: name,
            tracks: tracks
        };
        Vue.http.post("api/playlist/update", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    removePlaylist: function (id, callback) {
        var params = {
            id: id
        };
        Vue.http.post("api/playlist/remove", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getPlayList: function (playlistId, callback) {
        Vue.http.get("api/playlist/" + playlistId).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    searchPaths: function (path, actualPage, resultsPage, callback) {
        var params = {};
        if (path) {
            params.partialName = path;
        }
        if (actualPage) {
            params.actualPage = parseInt(actualPage);
        }
        if (resultsPage) {
            params.resultsPage = parseInt(resultsPage);
        }
        Vue.http.post("api/path/search", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    loveTrack: function (trackId, callback) {
        var params = {};
        Vue.http.post("api/track/" + trackId + "/love", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    unLoveTrack: function (trackId, callback) {
        var params = {};
        Vue.http.post("api/track/" + trackId + "/unlove", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getTopPlayedTracks: function (interval, artist, callback) {
        var params = {};
        if (artist) {
            params.artist = artist;
        }
        switch (interval) {
            case 0:
                break;
            case 1:
                params.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 2:
                params.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 3:
                params.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 4:
                params.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
        }
        Vue.http.post("api/metrics/top_played_tracks", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getTopPlayedArtists: function (interval, callback) {
        var params = {};
        switch (interval) {
            case 0:
                break;
            case 1:
                params.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 2:
                params.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 3:
                params.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 4:
                params.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
        }
        Vue.http.post("api/metrics/top_artists", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getTopPlayedGenres: function (interval, callback) {
        var params = {};
        switch (interval) {
            case 0:
                break;
            case 1:
                params.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 2:
                params.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 3:
                params.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 4:
                params.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
        }
        Vue.http.post("api/metrics/top_genres", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getRecentAddedTracks: function (interval, callback) {
        var params = {
            entity: "tracks"
        };
        switch (interval) {
            case 0:
                break;
            case 1:
                params.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 2:
                params.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 3:
                params.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 4:
                params.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
        }
        Vue.http.post("api/metrics/recently_added", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getRecentAddedArtists: function (interval, callback) {
        var params = {
            entity: "artists"
        };
        switch (interval) {
            case 0:
                break;
            case 1:
                params.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 2:
                params.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 3:
                params.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 4:
                params.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
        }
        Vue.http.post("api/metrics/recently_added", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getRecentAddedAlbums: function (interval, callback) {
        var params = {
            entity: "albums"
        };
        switch (interval) {
            case 0:
                break;
            case 1:
                params.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 2:
                params.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 3:
                params.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 4:
                params.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
        }
        Vue.http.post("api/metrics/recently_added", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getRecentPlayedTracks: function (interval, callback) {
        var params = {
            entity: "tracks"
        };
        switch (interval) {
            case 0:
                break;
            case 1:
                params.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 2:
                params.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 3:
                params.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 4:
                params.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
        }
        Vue.http.post("api/metrics/recently_played", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getRecentPlayedArtists: function (interval, callback) {
        var params = {
            entity: "albums"
        };
        switch (interval) {
            case 0:
                break;
            case 1:
                params.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 2:
                params.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 3:
                params.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 4:
                params.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
        }
        Vue.http.post("api/metrics/recently_played", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getRecentPlayedAlbums: function (interval, callback) {
        var params = {
            entity: "albums"
        };
        switch (interval) {
            case 0:
                break;
            case 1:
                params.fromDate = moment().subtract(7, 'days').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 2:
                params.fromDate = moment().subtract(1, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 3:
                params.fromDate = moment().subtract(6, 'months').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
            case 4:
                params.fromDate = moment().subtract(1, 'year').format('YYYYMMDD');
                params.toDate = moment().format('YYYYMMDD');
                break;
        }
        Vue.http.post("api/metrics/recently_played", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getPlayStatMetricsByHour: function (callback) {
        var params = {};
        Vue.http.post("api/metrics/play_stats_by_hour", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getPlayStatMetricsByWeekDay: function (callback) {
        var params = {};
        Vue.http.post("api/metrics/play_stats_by_weekday", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getPlayStatMetricsByMonth: function (callback) {
        var params = {};
        Vue.http.post("api/metrics/play_stats_by_month", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    },
    getPlayStatMetricsByYear: function (callback) {
        var params = {};
        Vue.http.post("api/metrics/play_stats_by_year", params).then(
            response => {
                callback(response);
            },
            response => {
                callback(response);
            }
        );
    }
};
