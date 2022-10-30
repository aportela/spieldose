import { createApp } from 'vue';

import { default as router } from './plugins/router.js';
import { default as i18n } from './plugins/i18n.js';
import { default as axios } from './plugins/axios.js';
import { default as api } from './plugins/api.js';
import { default as localStorage } from './plugins/localStorage.js';
import { default as bus } from './plugins/bus.js';
import { default as player } from './plugins/player.js';

const spieldoseApp = {
    data: function () {
        return ({
            logged: false,
            track: null
        });
    }
};

const localStorageBasilOptions = {
    namespace: 'spieldose',
    storages: ['local', 'cookie', 'session', 'memory'],
    storage: 'local',
    expireDays: 3650
};


createApp(spieldoseApp).use(router).use(i18n).use(localStorage, localStorageBasilOptions).use(axios, {}).use(api).use(bus).use(player).mount('#app');
