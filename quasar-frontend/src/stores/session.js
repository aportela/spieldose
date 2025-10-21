import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";

const hashedSite = Array.from(window.location.host).reduce(
  (hash, char) => 0 | (31 * hash + char.charCodeAt(0)),
  0
);

const localStorageBasilOptions = {
  namespace: "spieldose#" + hashedSite,
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  sameSite: "strict",
  expireDays: 3650,
};

export const useSessionStore = defineStore("session", {
  state: () => ({
    loaded: false,
    jwt: null
  }),

  getters: {
    isLoaded: (state) => state.loaded,
    isLogged: (state) => state.jwt != null,
    getJWT: (state) => state.jwt
  },
  actions: {
    load() {
      const basil = useBasil(localStorageBasilOptions);
      const jwt = basil.get("jwt");
      if (jwt) {
        this.jwt = jwt;
      }
      this.loaded = true;
    },
    save(jwt) {
      const basil = useBasil(localStorageBasilOptions);
      if (jwt !== null) {
        basil.set("jwt", jwt);
      } else {
        basil.remove("jwt");
      }
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
