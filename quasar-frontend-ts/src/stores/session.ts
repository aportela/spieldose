import { defineStore, acceptHMRUpdate } from 'pinia';

interface State {
  tokens: {
    access: string | null;
  }
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    tokens: {
      access: null,
    }
  }),
  getters: {
    hasAccessToken(state): boolean {
      return state.tokens.access !== null;
    },
    accessToken(state): string | null {
      return state.tokens.access
    },
  },
  actions: {
    setAccessToken(token: string): void {
      this.tokens.access = token;
    },
    removeAccessToken(): void {
      this.tokens.access = null;
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSessionStore, import.meta.hot));
}
