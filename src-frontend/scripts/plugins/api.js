import dayjs from 'dayjs';

export default {
    install: (app, options) => {
        app.config.globalProperties.$api = {
            session: {
                signUp: (email, password) => {
                    return new Promise((resolve, reject) => {
                        const params = {
                            email: email,
                            password: password
                        }
                        app.config.globalProperties.$axios.post('api2/user/signup', params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                signIn: (email, password) => {
                    return new Promise((resolve, reject) => {
                        const params = {
                            email: email,
                            password: password
                        }
                        app.config.globalProperties.$axios.post('api2/user/signin', params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                signOut: () => {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get('api2/user/signout').then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getProfile: () => {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get('api2/user/profile').then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                setProfile: (email, password) => {
                    const params = {
                        email: email,
                        password: password
                    }
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.post('api2/user/profile', params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                }
            },
            album: {
                getRandomAlbumCoverThumbnails: () => {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get('api2/random_album_covers').then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                }
            },
            track: {
                search: (query, artist, albumArtist, album) => {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get('/api2/track/search', {
                            params: {
                                q: query || null,
                                artist: artist || null,
                                albumArtist: albumArtist || null,
                                album: album || null
                            }
                        }).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                increasePlayCount: (trackId) => {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get('/api2/increase_play_count/track/' + trackId).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                love: (trackId) => {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get('/api2/love/track/' + trackId).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                unLove: (trackId) => {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get('/api2/unlove/track/' + trackId).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                }
            },
            metrics: {
                getTopPlayedTracks: function (interval, artist) {
                    return new Promise(function (resolve, reject) {
                        const params = {};
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
                        app.config.globalProperties.$axios.post("/api2/metrics/top_played_tracks", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getTopPlayedArtists: function (interval) {
                    return new Promise(function (resolve, reject) {
                        const params = {};
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
                        app.config.globalProperties.$axios.post("/api2/metrics/top_artists", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getTopPlayedGenres: function (interval) {
                    return new Promise(function (resolve, reject) {
                        const params = {};
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
                        app.config.globalProperties.$axios.post("/api2/metrics/top_genres", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getRecentAddedTracks: function (interval) {
                    return new Promise(function (resolve, reject) {
                        const params = {
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
                        app.config.globalProperties.$axios.post("/api2/metrics/recently_added", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getRecentAddedArtists: function (interval) {
                    return new Promise(function (resolve, reject) {
                        const params = {
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
                        app.config.globalProperties.$axios.post("/api2/metrics/recently_added", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getRecentAddedAlbums: function (interval) {
                    return new Promise(function (resolve, reject) {
                        const params = {
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
                        app.config.globalProperties.$axios.post("/api2/metrics/recently_added", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getRecentPlayedTracks: function (interval) {
                    return new Promise(function (resolve, reject) {
                        const params = {
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
                        app.config.globalProperties.$axios.post("/api2/metrics/recently_played", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getRecentPlayedArtists: function (interval) {
                    return new Promise(function (resolve, reject) {
                        const params = {
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
                        app.config.globalProperties.$axios.post("/api2/metrics/recently_played", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getRecentPlayedAlbums: function (interval) {
                    return new Promise(function (resolve, reject) {
                        const params = {
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
                        app.config.globalProperties.$axios.post("/api2/metrics/recently_played", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getPlayStatMetricsByHour: function () {
                    return new Promise(function (resolve, reject) {
                        app.config.globalProperties.$axios.post("/api2/metrics/play_stats_by_hour", {}).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getPlayStatMetricsByWeekDay: function () {
                    return new Promise(function (resolve, reject) {
                        app.config.globalProperties.$axios.post("/api2/metrics/play_stats_by_weekday", {}).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getPlayStatMetricsByMonth: function () {
                    return new Promise(function (resolve, reject) {
                        app.config.globalProperties.$axios.post("/api2/metrics/play_stats_by_month", {}).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                getPlayStatMetricsByYear: function () {
                    return new Promise(function (resolve, reject) {
                        app.config.globalProperties.$axios.post("/api2/metrics/play_stats_by_year", {}).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                }
            }
        };
    }
}
