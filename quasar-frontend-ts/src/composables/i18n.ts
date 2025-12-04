import { Lang } from "quasar";
import { default as messages } from "src/i18n";
import { locale as localStorageLocale } from "./localStorage";
import { DEFAULT_LOCALE } from 'src/constants';
// @ts-expect-error: missing TypeScript type definitions
import { default as enUS_Quasar } from "../../node_modules/quasar/lang/en-US";
// @ts-expect-error: missing TypeScript type definitions
import { default as esES_Quasar } from "../../node_modules/quasar/lang/es";

const availableSystemLocales: string[] = Object.keys(messages);

const getMatchedLocale = (locale: string): string | null => {
  if (availableSystemLocales.includes(locale)) {
    return locale;
  } else if (locale.length >= 2) {
    // try similar match, example: locale es-MX (spanish, mexico) return es-ES (spanish, spain) if availableSystemLocales only contains [ 'en-US', 'es-ES', 'gl-GL']
    const shortLocale = locale.substring(0, 2).toLocaleLowerCase();
    const match = availableSystemLocales.find((locale) => locale.substring(0, 2).toLocaleLowerCase() === shortLocale);
    return (match ?? null);
  } else {
    return null;
  }
};

const autodetectLocale = (): string => {
  return getMatchedLocale(localStorageLocale.get() || Lang.getLocale() || "") ?? DEFAULT_LOCALE;
};

const quasarLanguages = {
  "en-US": enUS_Quasar,
  "es-ES": esES_Quasar,
  "gl-GL": esES_Quasar,
};

const setQuasarLanguage = (locale: string) => {
  switch (locale) {
    case "en-US":
      Lang.set(quasarLanguages["en-US"]);
      break;
    case "es-ES":
      Lang.set(quasarLanguages["es-ES"]);
      break;
    case "gl-GL":
      Lang.set(quasarLanguages["gl-GL"]);
      break;
    default:
      console.error("Invalid locale for quasar language:", locale);
      Lang.set(quasarLanguages["en-US"]);
      break;
  }
};

export { availableSystemLocales, autodetectLocale, getMatchedLocale, setQuasarLanguage };
