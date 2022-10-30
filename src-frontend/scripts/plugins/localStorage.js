import { default as Basil } from 'basil';

export default {
    install: (app, options) => {
        app.config.globalProperties.$spieldoseLocalStorage = new Basil(options);
    }
}
