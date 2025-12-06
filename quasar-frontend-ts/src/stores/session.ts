import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from "src/composables/localStorage";

const localStorageShowToolTips = createStorageEntry<boolean>("session.showToolTips", true);

interface State {
  tokens: {
    access: string | null;
  };
  other: {
    showToolTips: boolean;
  };
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    tokens: {
      access: null,
    },
    other: {
      showToolTips: localStorageShowToolTips.get(),
    },
  }),
  getters: {
    hasAccessToken(state): boolean {
      return state.tokens.access !== null;
    },
    accessToken(state): string | null {
      return state.tokens.access
    },
    showToolTips(state): boolean {
      return (state.other.showToolTips);
    },
  },
  actions: {
    setAccessToken(token: string): void {
      this.tokens.access = token;
    },
    removeAccessToken(): void {
      this.tokens.access = null;
    },
    toggleToolTips(enabled: boolean) {
      this.other.showToolTips = enabled;
      localStorageShowToolTips.set(this.other.showToolTips);
    }
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSessionStore, import.meta.hot));
}
