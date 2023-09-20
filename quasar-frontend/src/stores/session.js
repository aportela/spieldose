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
  expireDays: 3650,
};

export const useSessionStore = defineStore("session", {
  state: () => ({
    loaded: false,
    jwt: null,
    locale: null,
    volume: null,
    fullScreenVisualizationSettings: null,
  }),

  getters: {
    isLoaded: (state) => state.loaded,
    isLogged: (state) => state.jwt != null,
    getJWT: (state) => state.jwt,
    getLocale: (state) => state.locale,
    getVolume: (state) => state.volume,
    getFullScreenVisualizationSettings: (state) =>
      state.fullScreenVisualizationSettings,
  },
  actions: {
    load() {
      const basil = useBasil(localStorageBasilOptions);
      const jwt = basil.get("jwt");
      if (jwt) {
        this.jwt = jwt;
      }
      const locale = basil.get("locale");
      if (locale) {
        this.locale = locale;
      }
      const volume = basil.get("volume");
      if (volume) {
        this.volume = volume;
      }
      const fullScreenVisualizationSettings = basil.get(
        "fullScreenVisualizationSettings"
      );
      if (fullScreenVisualizationSettings) {
        try {
          this.fullScreenVisualizationSettings = JSON.parse(
            fullScreenVisualizationSettings
          );
        } catch (e) {
          // console.error("error");
        }
      }
      this.loaded = true;
    },
    saveLocale(locale) {
      this.locale = locale;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("locale", locale);
    },
    saveVolume(volume) {
      this.volume = volume;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("volume", volume);
    },
    saveFullScreenVisualizationSettings(settings) {
      this.fullScreenVisualizationSettings = settings;
      const basil = useBasil(localStorageBasilOptions);
      basil.set(
        "fullScreenVisualizationSettings",
        this.fullScreenVisualizationSettings
          ? JSON.stringify(this.fullScreenVisualizationSettings)
          : null
      );
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
