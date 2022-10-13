import { createApp } from 'vue';
import { bus } from './bus.js';
import { default as router } from './routes.js';
import axios from 'axios';
import { default as Basil } from 'Basil';
import { default as i18n } from './i18n.js';

/*
import { default as spieldoseAPI } from './api.js';


import { mixinAPIError } from './mixins.js';

import { default as audioplayer } from './plugins/audioplayer.js';


*/

window.spieldose = window.spieldose || {};

const spieldoseApp = {
    //mixins: [mixinAPIError],
    data: function () {
        return ({
            logged: false,
            jwt: null,
            //errors: false,
            //apiError: null,
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

        this.jwt = this.localStorage.get('jwt');
        if (this.jwt) {
            window.spieldose.jwt = this.jwt;
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
        if (!initialState.version.upgradeAvailable) {
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
            //this.$audioplayer.dispose();
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

//createApp(spieldoseApp).use(router).use(i18n).use(audioplayer).mount('#app');
createApp(spieldoseApp).use(router).use(i18n).mount('#app');

// prevent php session lost (TODO: better management, only poll if we are logged)
setInterval(function () {
    spieldoseAPI.session.poll(function () { });
}, 300000 // 5 mins * 60 * 1000
);