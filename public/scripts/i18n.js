const messages = {
    en: {
        message: {
            hello: 'hello world'
        }
    },
    es: {
        message: {
            hello: 'hola mundo'
        }
    }
};

// create VueI18n instance with options
const i18n = new VueI18n({
    locale: initialState.locale, // set locale
    messages, // set locale messages
});
