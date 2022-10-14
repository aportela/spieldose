export default {
    install: (app, options) => {      
        app.config.globalProperties.$spieldoseAPI = {            
            session: {
                signUp: function (email, password) {                    
                    return new Promise(function (resolve, reject) {
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
                signIn: function (email, password) {
                    return new Promise(function (resolve, reject) {
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
                signOut: function () {
                    return new Promise(function (resolve, reject) {
                        app.config.globalProperties.$axios.get("api2/user/signout").then(response => {
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
