import { defineStore, acceptHMRUpdate } from 'pinia';
import { Lang } from "quasar";
import { createStorageEntry } from 'src/composables/localStorage';
import { availableSystemLocales } from "src/i18n";
import { DEFAULT_LOCALE } from 'src/constants';

const localStorageLocale = createStorageEntry<string | null>("locale", null);

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

interface State {
  locale: string;
};

export const useI18nStore = defineStore('i18nStore', {
  state: (): State => ({
    locale: getMatchedLocale(localStorageLocale.get() || Lang.getLocale() || "") ?? DEFAULT_LOCALE,
  }),
  getters: {
    currentLocale(state): string {
      return state.locale;
    },
  },
  actions: {
    setLocale(locale: string): boolean {
      const matched = getMatchedLocale(locale);
      if (matched !== null) {
        this.locale = matched;
        localStorageLocale.set(this.locale);
        return (true);
      } else {
        return (false);
      }
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useI18nStore, import.meta.hot));
}
