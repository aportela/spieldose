
import { createApp } from 'vue';
import { default as router } from './routes.js';
import axios from 'axios';
import { default as i18n } from './i18n.js';
import { default as spieldoseAPI } from './api.js';
import { bus } from './bus.js';

import { mixinAPIError, mixinPlayer } from './mixins.js';

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

createApp(spieldoseApp).use(router).use(i18n).mount('#app');

// prevent php session lost (TODO: better management, only poll if we are logged)
setInterval(function () {
    spieldoseAPI.session.poll(function () { });
}, 300000 // 5 mins * 60 * 1000
);