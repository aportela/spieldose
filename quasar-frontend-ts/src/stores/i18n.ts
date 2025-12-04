import { defineStore, acceptHMRUpdate } from 'pinia';
import { locale as localStorageLocale } from 'src/composables/localStorage';
import { autodetectLocale, getMatchedLocale } from 'src/composables/i18n';

interface State {
  locale: string;
};

export const useI18nStore = defineStore('i18nStore', {
  state: (): State => ({
    locale: autodetectLocale()
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
