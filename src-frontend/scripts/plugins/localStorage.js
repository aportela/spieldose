import { default as Basil } from 'Basil';

export default {
    install: (app, options) => {
        app.config.globalProperties.$spieldoseLocalStorage = new Basil(options);
    }
}
