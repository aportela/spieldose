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
                        app.config.globalProperties.$axios.post("api2/user/signup", params).then(response => {
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
                        app.config.globalProperties.$axios.post("api2/user/signin", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                signOut: () => {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get("api2/user/signout").then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                }
            },
            album: {
                getRandomAlbumCoverThumbnails: (count, width, height) => {
                    return new Promise((resolve, reject) => {
                        const params = {
                            count: count,
                            width: width,
                            height: height
                        }
                        app.config.globalProperties.$axios.post("api2/random_album_cover_hashes", params).then(response => {
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
