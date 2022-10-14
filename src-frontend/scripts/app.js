import { createApp } from 'vue';
import { bus } from './bus.js';
import { default as router } from './routes.js';

import { default as i18n } from './i18n.js';

/*
import { default as spieldoseAPI } from './api.js';


import { mixinAPIError } from './mixins.js';

import { default as audioplayer } from './plugins/audioplayer.js';


*/
import { default as axios } from './plugins/axios.js';
import { default as api } from './plugins/api.js';
import { default as localStorage } from './plugins/localStorage.js';

window.spieldose = window.spieldose || {};

const spieldoseApp = {
    //mixins: [mixinAPIError],
    data: function () {
        return ({
            logged: false,
            jwt: null,
            //errors: false,
            //apiError: null,
            localStorage: null
        });
    },
    created: function () {
        bus.on("signOut", () => {
            this.signOut();
        });
        this.$router.push({ name: 'signin' });
        /*
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
        */
    },
    methods: {
        signOut: function () {
            //this.$audioplayer.dispose();
            //this.clearAPIErrors();
            this.$spieldoseAPI.session.signOut().then(response => { console.log(response); }).catch(error => { console.log(error); });
        }
    }
};

const localStorageBasilOptions = {
    namespace: 'spieldose',
    storages: ['local', 'cookie', 'session', 'memory'],
    storage: 'local',
    expireDays: 3650
};

//createApp(spieldoseApp).use(router).use(i18n).use(audioplayer).mount('#app');
createApp(spieldoseApp).use(router).use(i18n).use(localStorage, localStorageBasilOptions).use(axios, {}).use(api).mount('#app');
