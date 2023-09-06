import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";

const localStorageBasilOptions = {
  namespace: "spieldose",
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  expireDays: 3650,
};

export const useSessionStore = defineStore("session", {
  state: () => ({
    jwt: null,
    lang: "en",
    volume: null,
  }),

  getters: {
    isLogged: (state) => state.jwt != null,
    getJWT: (state) => state.jwt,
    getVolume: (state) => state.volume,
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
      const volume = basil.get("volume");
      if (volume) {
        this.volume = volume;
      }
    },
    saveLang(lang) {
      this.lang = lang;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("lang", lang);
    },
    saveVolume(volume) {
      this.volume = volume;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("volume", volume);
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
