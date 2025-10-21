import { createI18n } from "vue-i18n";
import messages from "src/i18n";
import { Lang, LocalStorage } from "quasar";

const availableLocales = Object.keys(messages);

const localeMappings = {
  es: 'es-ES',
  en: 'en-EN',
  gl: 'gl-GL'
};

const browserLocale = Lang.getLocale();
const normalizedBrowsedLocale = browserLocale.length >= 2 ? (localeMappings[browserLocale.substring(0, 2)] || browserLocale) : browserLocale;

const savedLocale = LocalStorage.getItem("locale");

let currentLocale = savedLocale || normalizedBrowsedLocale;

currentLocale = availableLocales.includes(currentLocale)
  ? currentLocale
  : "en-US";

// Create I18n instance
const i18n = createI18n({
  locale: currentLocale,
  legacy: false, // you must set `false`, to use Composition API
  globalInjection: true,
  messages,
});

export function usei18n() {
  return { i18n };
}
