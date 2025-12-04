import { defineStore, acceptHMRUpdate } from 'pinia';
import { showToolTips as localStorageShowToolTips } from "src/composables/localStorage";

interface State {
  tokens: {
    access: string | null;
  };
  toolTips: boolean;
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    tokens: {
      access: null,
    },
    toolTips: localStorageShowToolTips.get(),
  }),
  getters: {
    hasAccessToken(state): boolean {
      return state.tokens.access !== null;
    },
    accessToken(state): string | null {
      return state.tokens.access
    },
    toolTipsEnabled(state): boolean {
      return (state.toolTips);
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
      this.toolTips = enabled;
      localStorageShowToolTips.set(this.toolTips);
    }
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSessionStore, import.meta.hot));
}
