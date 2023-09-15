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
        this.fullScreenVisualizationSettings = JSON.parse(
          fullScreenVisualizationSettings
        );
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
        JSON.stringify(this.fullScreenVisualizationSettings)
      );
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
