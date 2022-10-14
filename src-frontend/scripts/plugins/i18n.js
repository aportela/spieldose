import { createI18n } from 'vue-i18n';

import { default as messagesES } from '../locale/es.js';
import { default as messagesEN } from '../locale/en.js';
import { default as messagesGL } from '../locale/gl.js';


let messages = null;
switch (initialState.locale) {
    case "es":
        messages = messagesES;
        break;
    case "gl":
        messages = messagesGL;
        break;
    default:
        messages = messagesEN;
        break;
}

// create VueI18n instance with options
const i18n = createI18n({
    locale: initialState.locale, // set locale
    messages // set locale messages
});

export default i18n;