import { createI18n } from "vue-i18n";
import messages from "src/i18n";

import { useSessionStore } from "stores/session";

const session = useSessionStore();

session.load();

let defaultLocale = "en-US";

if (session.getLocale) {
  defaultLocale = session.getLocale;
} else {
  switch ((navigator.language || navigator.userLanguage).substring(0, 2)) {
    case "es":
      defaultLocale = "es-ES";
      break;
    case "gl":
      defaultLocale = "gl-GL";
      break;
    default:
      defaultLocale = "en-US";
      break;
  }
}

// Create I18n instance
const i18n = createI18n({
  locale: defaultLocale,
  legacy: false, // you must set `false`, to use Composition API
  globalInjection: true,
  messages,
});

export default ({ app }) => {
  // Tell app to use the I18n instance
  app.use(i18n);
};

export { i18n, defaultLocale };
