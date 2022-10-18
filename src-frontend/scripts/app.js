import { createApp } from 'vue';

import { default as router } from './plugins/router.js';
import { default as i18n } from './plugins/i18n.js';
import { default as axios } from './plugins/axios.js';
import { default as api } from './plugins/api.js';
import { default as localStorage } from './plugins/localStorage.js';
import { default as bus } from './plugins/bus.js';

const spieldoseApp = {
    data: function () {
        return ({
            logged: false,
            track: null
        });
    },
    created: function () {
        if (!initialState.version.upgradeAvailable) {
            if (!initialState.logged) {
                if (this.$route.name != 'signin') {
                    this.$router.push({ name: 'signin' });
                }
            } else {
                if (!this.$router.currentRoute.name) {
                    this.$router.push({ name: 'nowPlaying' });
                }
            }
        } else {
            this.$router.push({ name: 'upgrade' });
        }
    }
};

const localStorageBasilOptions = {
    namespace: 'spieldose',
    storages: ['local', 'cookie', 'session', 'memory'],
    storage: 'local',
    expireDays: 3650
};


createApp(spieldoseApp).use(router).use(i18n).use(localStorage, localStorageBasilOptions).use(axios, {}).use(api).use(bus).mount('#app');
