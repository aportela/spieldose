
import { createApp } from 'vue';
import { default as router } from './routes.js';
import axios from 'axios';
import { default as Basil } from 'Basil';
import { default as i18n } from './i18n.js';
import { default as spieldoseAPI } from './api.js';
import { bus } from './bus.js';

import { mixinAPIError } from './mixins.js';

import { default as audioplayer } from './plugins/audioplayer.js';

window.spieldose = window.spieldose || {};

const getApiErrorDataFromAxiosResponse = function (r) {
    var data = {
        request: {
            //method: r.rMethod,
            //url: r.rUrl,
            //body: r.rBody
        },
        response: {
            //status: r.status,
            //statusText: r.statusText,
            //text: r.bodyText
        }
    };
    data.request.headers = [];
    /*
    for (var headerName in r.rHeaders.map) {
        //data.request.headers.push({ name: headerName, value: r.rHeaders.get(headerName) });
    }
    */
    data.response.headers = [];
    /*
    for (var headerName in r.headers.map) {
        //data.response.headers.push({ name: headerName, value: r.headers.get(headerName) });
    }
    */
    return (data);
};

axios.interceptors.response.use(function (response) {
    response.getApiErrorData = function () {
        return (getApiErrorDataFromAxiosResponse(response));
    };
    return response;
}, function (error) {
    console.log(error);
    // Any status codes that falls outside the range of 2xx cause this function to trigger
    // Do something with response error

    // Any status code that lie within the range of 2xx cause this function to trigger
    // Do something with response data
    //error.rBody = request.body;
    //error.rUrl = request.url;
    //error.rMethod = request.method;
    //error.rHeaders = request.headers;
    error.getApiErrorData = function () {
        return (getApiErrorDataFromAxiosResponse(error));
    };
    if (error.response.status == 400 || error.response.status == 409) {
        // helper for find invalid fields on api response
        error.isFieldInvalid = function (fieldName) {
            return (error.response.data.invalidOrMissingParams.indexOf(fieldName) > -1);
        }
    }
    return Promise.reject(error);
});

const spieldoseApp = {
    mixins: [mixinAPIError],
    data: function () {
        return ({
            logged: false,
            errors: false,
            apiError: null,
            localStorage: null,
            axios: null
        });
    },
    created: function () {
        this.localStorage = new Basil({
            namespace: 'spieldose',
            storages: ['local', 'cookie', 'session', 'memory'],
            storage: 'local',
            expireDays: 3650
        });

        this.axios = axios.create({});

        const jwt = this.localStorage.get('jwt');
        if (jwt) {
            window.spieldose.jwt = jwt;            
            if (this.jwt) {
                this.axios.interceptors.request.use((config) => {
                    config.headers["SPIELDOSE-JWT"] = this.jwt;
                    return (config);
                }, (error) => {
                    return Promise.reject(error);
                });
            }
        }

        this.axios.interceptors.response.use((response) => {
            // warning: axios lowercase received header names
            const apiResponseJWT = response.headers["spieldose-jwt"] || null;
            if (apiResponseJWT) {
                if (apiResponseJWT && apiResponseJWT != this.jwt) {
                    this.localStorage.set("jwt", apiResponseJWT);
                    window.spieldose.jwt = apiResponseJWT;
                }
            }
            return response;
        }, (error) => {
            return Promise.reject(error.message);
        });
        bus.on("signOut", () => {
            this.signOut();
        });
        /*
        if (!initialState.upgradeAvailable) {
            if (!initialState.logged) {
                if (this.$route.name != 'signin') {
                    this.$router.push({ name: 'signin' });
                }
            } else {
                if (!this.$route.name) {
                    this.$router.push({ name: 'dashboard' });
                }
            }
        } else {
            this.$router.push({ name: 'upgrade' });
        }
        */
        this.$router.push({ name: 'signin' });
    },
    methods: {
        signOut: function () {
            this.$audioplayer.dispose();
            this.clearAPIErrors();
            spieldoseAPI.session.signOut().then(response => {
                if (response.status == 200) {
                    this.$router.push({ path: '/signin' });
                } else {
                    console.log(response);
                    //this.setAPIError(response.getApiErrorData());
                }
            }).catch(error => { console.log(error); });
        },
        poll: function (callback) {
            spieldoseAPI.session.poll((response) => {
                if (callback && typeof callback === "function") {
                    callback(response);
                }
            });
        }
    }
};

createApp(spieldoseApp).use(router).use(i18n).use(audioplayer).mount('#app');

// prevent php session lost (TODO: better management, only poll if we are logged)
setInterval(function () {
    spieldoseAPI.session.poll(function () { });
}, 300000 // 5 mins * 60 * 1000
);