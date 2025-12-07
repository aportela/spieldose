import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from "src/composables/localStorage";

const localStorageShowToolTips = createStorageEntry<boolean>("session.showToolTips", true);

interface State {
  session: {
    accessToken: string | null;
    showToolTips: boolean;
  };
};

export const useSessionStore = defineStore("sessionStore", {
  state: (): State => ({
    session: {
      accessToken: null,
      showToolTips: localStorageShowToolTips.get(),
    },
  }),
  getters: {
    hasAccessToken: (state): boolean =>
      state.session.accessToken !== null,
    accessToken: (state): string | null =>
      state.session.accessToken,
    showToolTips: (state): boolean =>
      state.session.showToolTips,
  },
  actions: {
    setAccessToken(token: string): void {
      this.session.accessToken = token;
    },
    removeAccessToken(): void {
      this.session.accessToken = null;
    },
    toggleToolTips(enabled: boolean) {
      this.session.showToolTips = enabled;
      localStorageShowToolTips.set(this.session.showToolTips);
    }
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSessionStore, import.meta.hot));
}
