export default {
    install: (app, options) => {
        app.config.globalProperties.$api = {
            session: {
                signUp: (email, password) => {
                    return new Promise((resolve, reject) => {
                        var params = {
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
                        var params = {
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
                }
            }
        };
    }
}
