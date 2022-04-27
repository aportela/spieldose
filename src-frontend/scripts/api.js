import axios from 'axios';
import dayjs from 'dayjs';

export default {
    session: {
        poll: function () {
            return new Promise(function (resolve, reject) {
                axios.get("api/user/poll").then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        signUp: function (email, password) {
            return new Promise(function (resolve, reject) {
                var params = {
                    email: email,
                    password: password
                }
                axios.post("api/user/signup", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        signIn: function (email, password) {
            return new Promise(function (resolve, reject) {
                var params = {
                    email: email,
                    password: password
                }
                axios.post("api/user/signin", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        signOut: function () {
            return new Promise(function (resolve, reject) {
                axios.get("api/user/signout").then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        }
    },
    globalSearch: function (text, actualPage, resultsPage) {
        return new Promise(function (resolve, reject) {
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
            axios.post("api/search/global", params).then(response => {
                resolve(response);
            }).catch(error => {
                reject(error);
            });
        });
    },
    artist: {
        get: function (name) {
            return new Promise(function (resolve, reject) {
                axios.get("api/artist/" + encodeURIComponent(name)).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        search: function (name, withoutMbid, actualPage, resultsPage) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/artist/search", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        overwriteMusicBrainz: function (name, mbid) {
            return new Promise(function (resolve, reject) {
                var params = {
                    mbid: mbid
                }
                axios.put("api/artist/" + encodeURIComponent(name) + "/mbid", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        clearMusicBrainz: function (name, mbid) {
            return new Promise(function (resolve, reject) {
                axios.put("api/artist/" + encodeURIComponent(name) + "/mbid").then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        }
    },
    track: {
        getAlbumTracks: function (album, artist, year) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/track/search", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getPathTracks: function (path) {
            return new Promise(function (resolve, reject) {
                var params = {
                    path: path,
                    resultsPage: 0
                };
                axios.post("api/track/search", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        searchTracks: function (text, artist, album, loved, actualPage, resultsPage, order) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/track/search", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        love: function (trackId) {
            return new Promise(function (resolve, reject) {
                var params = {};
                axios.post("api/track/" + trackId + "/love", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        unlove: function (trackId) {
            return new Promise(function (resolve, reject) {
                var params = {};
                axios.post("api/track/" + trackId + "/unlove", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        }
    },
    album: {
        search: function (name, artist, year, actualPage, resultsPage) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/album/search", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getRandomAlbumCovers: function (count) {
            return new Promise(function (resolve, reject) {
                const params = {
                    count: count
                };
                axios.post("api/random_album_covers", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        }
    },
    playlist: {
        search: function (name, actualPage, resultsPage) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/playlist/search", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        add: function (name, tracks) {
            return new Promise(function (resolve, reject) {
                var params = {
                    name: name,
                    tracks: tracks
                };
                axios.post("api/playlist/add", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        update: function (id, name, tracks) {
            return new Promise(function (resolve, reject) {
                var params = {
                    id: id,
                    name: name,
                    tracks: tracks
                };
                axios.post("api/playlist/update", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        remove: function (id) {
            return new Promise(function (resolve, reject) {
                var params = {
                    id: id
                };
                axios.post("api/playlist/remove", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        get: function (playlistId) {
            return new Promise(function (resolve, reject) {
                axios.get("api/playlist/" + playlistId).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        }
    },
    radioStation: {
        search: function (name, actualPage, resultsPage) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/radio_station/search", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        add: function (name, url, urlType, image) {
            return new Promise(function (resolve, reject) {
                var params = {
                    name: name,
                    url: url,
                    urlType: urlType,
                    image: image
                };
                axios.post("api/radio_station/add", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        update: function (id, name, url, urlType, image) {
            return new Promise(function (resolve, reject) {
                var params = {
                    id: id,
                    name: name,
                    url: url,
                    urlType: urlType,
                    image: image
                };
                axios.post("api/radio_station/update", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        remove: function (id) {
            return new Promise(function (resolve, reject) {
                var params = {
                    id: id
                };
                axios.post("api/radio_station/remove", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        get: function (id) {
            return new Promise(function (resolve, reject) {
                axios.get("api/radio_station/" + id).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        }
    },
    searchPaths: function (path, actualPage, resultsPage) {
        return new Promise(function (resolve, reject) {
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
            axios.post("api/path/search", params).then(response => {
                resolve(response);
            }).catch(error => {
                reject(error);
            });
        });
    },
    metrics: {
        getTopPlayedTracks: function (interval, artist) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/metrics/top_played_tracks", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getTopPlayedArtists: function (interval) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/metrics/top_artists", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getTopPlayedGenres: function (interval) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/metrics/top_genres", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getRecentAddedTracks: function (interval) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/metrics/recently_added", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getRecentAddedArtists: function (interval) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/metrics/recently_added", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getRecentAddedAlbums: function (interval) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/metrics/recently_added", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getRecentPlayedTracks: function (interval) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/metrics/recently_played", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getRecentPlayedArtists: function (interval) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/metrics/recently_played", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getRecentPlayedAlbums: function (interval) {
            return new Promise(function (resolve, reject) {
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
                axios.post("api/metrics/recently_played", params).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getPlayStatMetricsByHour: function () {
            return new Promise(function (resolve, reject) {
                axios.post("api/metrics/play_stats_by_hour", {}).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getPlayStatMetricsByWeekDay: function () {
            return new Promise(function (resolve, reject) {
                axios.post("api/metrics/play_stats_by_weekday", {}).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getPlayStatMetricsByMonth: function () {
            return new Promise(function (resolve, reject) {
                axios.post("api/metrics/play_stats_by_month", {}).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        },
        getPlayStatMetricsByYear: function () {
            return new Promise(function (resolve, reject) {
                axios.post("api/metrics/play_stats_by_year", {}).then(response => {
                    resolve(response);
                }).catch(error => {
                    reject(error);
                });
            });
        }
    }
};
