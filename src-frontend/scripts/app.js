import { createApp } from 'vue';
import { default as router } from './routes.js';
import axios from 'axios';
import VueAxios from 'vue-axios';
import { default as i18n } from './i18n.js';
import { default as spieldoseAPI } from './api.js';
import { bus } from './bus.js';
import { mixinAPIError, mixinPlayer } from './mixins.js';

/**
 * parse vue-resource (custom) resource and return valid object for api-error component
 * @param {*} r a valid vue-resource response object
 */
const getApiErrorDataFromResponse = function (r) {
    var data = {
        request: {
            method: r.rMethod,
            url: r.rUrl,
            body: r.rBody
        },
        response: {
            status: r.status,
            statusText: r.statusText,
            text: r.bodyText
        }
    };
    data.request.headers = [];
    for (var headerName in r.rHeaders.map) {
        data.request.headers.push({ name: headerName, value: r.rHeaders.get(headerName) });
    }
    data.response.headers = [];
    for (var headerName in r.headers.map) {
        data.response.headers.push({ name: headerName, value: r.headers.get(headerName) });
    }
    return (data);
};

/**
 * vue-resource interceptor for adding (on errors) custom get data function (used in api-error component) into response
 */
/*
Vue.http.interceptors.push((request, next) => {
    next((response) => {
        if (!response.ok) {
            response.rBody = request.body;
            response.rUrl = request.url;
            response.rMethod = request.method;
            response.rHeaders = request.headers;
            response.getApiErrorData = function () {
                return (getApiErrorDataFromResponse(response));
            };
            if (response.status == 400 || response.status == 409) {
                // helper for find invalid fields on api response
                response.isFieldInvalid = function (fieldName) {
                    return (response.data.invalidOrMissingParams.indexOf(fieldName) > -1);
                }
            }
        }
        return (response);
    });
});
*/

axios.interceptors.response.use(function (response) {
    if (response.status != 200) {
        // TODO: migrate old vue resource interceptor
        response.rBody = null;
        response.rUrl = null;
        response.rMethod = null;
        response.rHeaders = null;
        response.getApiErrorData = function() {
            console.log(response);
            return (getApiErrorDataFromResponse(response));
        }
    };
    if (response.status == 400 || response.status == 409) {
        // helper for find invalid fields on api response
        response.isFieldInvalid = function (fieldName) {
            return (response.data.invalidOrMissingParams.indexOf(fieldName) > -1);
        }
    }
    return response;
}, function (error) {
    // Any status codes that falls outside the range of 2xx cause this function to trigger
    // Do something with response error
    return Promise.reject(error);
});

/**
 * main app component
 */
const spieldoseApp = {
    mixins: [mixinAPIError, mixinPlayer],
    data: function () {
        return ({
            logged: false,
            errors: false,
            apiError: null
        });
    },
    created: function () {
        bus.on("signOut", () => {
            this.signOut();
        });
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
    },
    methods: {
        signOut: function () {
            this.playerData.dispose();
            this.clearAPIErrors();
            spieldoseAPI.session.signOut((response) => {
                if (response.status == 200) {
                    this.$router.push({ path: '/signin' });
                } else {
                    this.setAPIError(response.getApiErrorData());
                }
            });
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

createApp(spieldoseApp).use(router).use(i18n).use(VueAxios, axios).mount('#app');

// prevent php session lost (TODO: better management, only poll if we are logged)
setInterval(function () {
    spieldoseAPI.session.poll(function () { });
}, 300000 // 5 mins * 60 * 1000
);