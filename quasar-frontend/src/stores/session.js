import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";

const localStorageBasilOptions = {
  namespace: "homedocs",
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  expireDays: 3650,
};

export const useSessionStore = defineStore("session", {
  state: () => ({
    jwt: null,
    lang: "en",
  }),

  getters: {
    isLogged: (state) => state.jwt != null,
    getJWT: (state) => state.jwt,
  },
  actions: {
    load() {
      const basil = useBasil(localStorageBasilOptions);
      const jwt = basil.get("jwt");
      if (jwt) {
        this.jwt = jwt;
      }
      const lang = basil.get("lang");
      this.lang = lang || "en-US";
    },
    saveLang(lang) {
      this.lang = lang;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("lang", lang);
    },
    save(jwt) {
      const basil = useBasil(localStorageBasilOptions);
      basil.set("jwt", jwt);
    },
    signIn(jwt) {
      this.jwt = jwt;
      this.save(jwt);
    },
    signOut() {
      this.jwt = null;
      this.save(null);
    },
  },
});
