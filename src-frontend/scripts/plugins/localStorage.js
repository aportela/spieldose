import { default as Basil } from 'basil';

export default {
    install: (app, options) => {
        app.config.globalProperties.$localStorage = new Basil(options);
    }
}
