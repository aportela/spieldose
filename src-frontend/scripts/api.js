import axios from 'axios';

export default {
    session: {
        poll: function (callback) {
            axios.get("api/user/poll").then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        signUp: function (email, password, callback) {
            var params = {
                email: email,
                password: password
            }
            axios.post("api/user/signup", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                    response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        signIn: function (email, password, callback) {
            var params = {
                email: email,
                password: password
            }
            axios.post("api/user/signin", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                    response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        signOut: function (callback) {
            axios.get("api/user/signout").then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        }
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
        axios.post("api/search/global", params).then(
            response => {
                if (callback && typeof callback === "function") {
                    callback(response);
                }
            }
        ).catch(
            response => {
                if (callback && typeof callback === "function") {
                    callback(response);
                }
            }
        );
    },
    artist: {
        get: function (name, callback) {
            axios.get("api/artist/" + encodeURIComponent(name)).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        search: function (name, withoutMbid, actualPage, resultsPage, callback) {
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
            axios.post("api/artist/search", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        overwriteMusicBrainz: function (name, mbid, callback) {
            var params = {
                mbid: mbid
            }
            axios.put("api/artist/" + encodeURIComponent(name) + "/mbid", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        clearMusicBrainz: function (name, mbid, callback) {
            axios.put("api/artist/" + encodeURIComponent(name) + "/mbid").then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        }
    },
    track: {
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
            axios.post("api/track/search", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        getPathTracks: function (path, callback) {
            var params = {
                path: path,
                resultsPage: 0
            };
            axios.post("api/track/search", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
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
            axios.post("api/track/search", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        love: function (trackId, callback) {
            var params = {};
            axios.post("api/track/" + trackId + "/love", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        unlove: function (trackId, callback) {
            var params = {};
            axios.post("api/track/" + trackId + "/unlove", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        }
    },
    album: {
        search: function (name, artist, year, actualPage, resultsPage, callback) {
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
            axios.post("api/album/search", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        getRandomAlbumCovers: function(count, callback) {
            const params = {
                count: count
            };
            axios.post("api/random_album_covers", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        }
    },
    playlist: {
        search: function (name, actualPage, resultsPage, callback) {
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
            axios.post("api/playlist/search", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        add: function (name, tracks, callback) {
            var params = {
                name: name,
                tracks: tracks
            };
            axios.post("api/playlist/add", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        update: function (id, name, tracks, callback) {
            var params = {
                id: id,
                name: name,
                tracks: tracks
            };
            axios.post("api/playlist/update", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        remove: function (id, callback) {
            var params = {
                id: id
            };
            axios.post("api/playlist/remove", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        get: function (playlistId, callback) {
            axios.get("api/playlist/" + playlistId).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        }
    },
    radioStation: {
        search: function (name, actualPage, resultsPage, callback) {
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
            axios.post("api/radio_station/search", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        add: function (name, url, urlType, image, callback) {
            var params = {
                name: name,
                url: url,
                urlType: urlType,
                image: image
            };
            axios.post("api/radio_station/add", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        update: function (id, name, url, urlType, image, callback) {
            var params = {
                id: id,
                name: name,
                url: url,
                urlType: urlType,
                image: image
            };
            axios.post("api/radio_station/update", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        remove: function (id, callback) {
            var params = {
                id: id
            };
            axios.post("api/radio_station/remove", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        get: function (id, callback) {
            axios.get("api/radio_station/" + id).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        }
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
        axios.post("api/path/search", params).then(
            response => {
                if (callback && typeof callback === "function") {
                    callback(response);
                }
            }
        ).catch(
            response => {
                if (callback && typeof callback === "function") {
                    callback(response);
                }
            }
        );
    },
    metrics: {
        getTopPlayedTracks: function (interval, artist, callback) {
            var params = {};
            if (artist) {
                params.artist = artist;
            }
            switch (interval) {
                case 0:
                    break;
                case 1:
                    params.fromDate = dayjs().subtract(7, 'day').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 2:
                    params.fromDate = dayjs().subtract(1, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 3:
                    params.fromDate = dayjs().subtract(6, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 4:
                    params.fromDate = dayjs().subtract(1, 'year').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
            }
            axios.post("api/metrics/top_played_tracks", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        getTopPlayedArtists: function (interval, callback) {
            var params = {};
            switch (interval) {
                case 0:
                    break;
                case 1:
                    params.fromDate = dayjs().subtract(7, 'day').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 2:
                    params.fromDate = dayjs().subtract(1, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 3:
                    params.fromDate = dayjs().subtract(6, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 4:
                    params.fromDate = dayjs().subtract(1, 'year').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
            }
            axios.post("api/metrics/top_artists", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        getTopPlayedGenres: function (interval, callback) {
            var params = {};
            switch (interval) {
                case 0:
                    break;
                case 1:
                    params.fromDate = dayjs().subtract(7, 'day').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 2:
                    params.fromDate = dayjs().subtract(1, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 3:
                    params.fromDate = dayjs().subtract(6, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 4:
                    params.fromDate = dayjs().subtract(1, 'year').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
            }
            axios.post("api/metrics/top_genres", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
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
                    params.fromDate = dayjs().subtract(7, 'day').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 2:
                    params.fromDate = dayjs().subtract(1, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 3:
                    params.fromDate = dayjs().subtract(6, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 4:
                    params.fromDate = dayjs().subtract(1, 'year').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
            }
            axios.post("api/metrics/recently_added", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
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
                    params.fromDate = dayjs().subtract(7, 'day').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 2:
                    params.fromDate = dayjs().subtract(1, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 3:
                    params.fromDate = dayjs().subtract(6, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 4:
                    params.fromDate = dayjs().subtract(1, 'year').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
            }
            axios.post("api/metrics/recently_added", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
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
                    params.fromDate = dayjs().subtract(7, 'day').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 2:
                    params.fromDate = dayjs().subtract(1, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 3:
                    params.fromDate = dayjs().subtract(6, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 4:
                    params.fromDate = dayjs().subtract(1, 'year').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
            }
            axios.post("api/metrics/recently_added", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
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
                    params.fromDate = dayjs().subtract(7, 'day').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 2:
                    params.fromDate = dayjs().subtract(1, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 3:
                    params.fromDate = dayjs().subtract(6, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 4:
                    params.fromDate = dayjs().subtract(1, 'year').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
            }
            axios.post("api/metrics/recently_played", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
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
                    params.fromDate = dayjs().subtract(7, 'day').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 2:
                    params.fromDate = dayjs().subtract(1, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 3:
                    params.fromDate = dayjs().subtract(6, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 4:
                    params.fromDate = dayjs().subtract(1, 'year').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
            }
            axios.post("api/metrics/recently_played", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
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
                    params.fromDate = dayjs().subtract(7, 'day').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 2:
                    params.fromDate = dayjs().subtract(1, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 3:
                    params.fromDate = dayjs().subtract(6, 'month').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
                case 4:
                    params.fromDate = dayjs().subtract(1, 'year').format('YYYYMMDD');
                    params.toDate = dayjs().format('YYYYMMDD');
                    break;
            }
            axios.post("api/metrics/recently_played", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        getPlayStatMetricsByHour: function (callback) {
            axios.post("api/metrics/play_stats_by_hour", {}).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        getPlayStatMetricsByWeekDay: function (callback) {
            axios.post("api/metrics/play_stats_by_weekday", {}).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        getPlayStatMetricsByMonth: function (callback) {
            axios.post("api/metrics/play_stats_by_month", {}).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        getPlayStatMetricsByYear: function (callback) {
            axios.post("api/metrics/play_stats_by_year", {}).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        }
    }
};
