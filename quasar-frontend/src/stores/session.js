import { defineStore } from "pinia";

import { useLocalStorage } from "src/composables/useLocalStorage";

const { jwt } = useLocalStorage();

export const useSessionStore = defineStore("session", {
  state: () => ({
    loaded: false,
    jwt: null,
  }),

  getters: {
    isLoaded: (state) => state.loaded,
    isLogged: (state) => state.jwt != null,
    getJWT: (state) => state.jwt,
  },
  actions: {
    load() {
      const savedJWT = jwt.get();
      if (savedJWT) {
        this.jwt = savedJWT;
      }
      this.loaded = true;
    },
    save(newJWT) {
      if (newJWT !== null) {
        jwt.set(newJWT);
      } else {
        jwt.remove();
      }
    },
    login(newJWT) {
      this.jwt = newJWT;
      this.save(newJWT);
    },
    logout() {
      this.jwt = null;
      this.save(null);
    },
  },
});
