import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";

// https://stackoverflow.com/a/8831937
function hashCode(str) {
  let hash = 0;
  for (let i = 0, len = str.length; i < len; i++) {
    let chr = str.charCodeAt(i);
    hash = (hash << 5) - hash + chr;
    hash |= 0; // Convert to 32bit integer
  }
  return hash;
}

const hashedSite = Array.from(window.location.host).reduce(
  (hash, char) => 0 | (31 * hash + char.charCodeAt(0)),
  0
);

const localStorageBasilOptions = {
  //namespace: "spieldose#" + hashedSite,
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
    sidebarPlayerSettings: null,
  }),

  getters: {
    isLoaded: (state) => state.loaded,
    isLogged: (state) => state.jwt != null,
    getJWT: (state) => state.jwt,
    getLocale: (state) => state.locale,
    getVolume: (state) => state.volume,
    getFullScreenVisualizationSettings: (state) =>
      state.fullScreenVisualizationSettings,
    getSidebarPlayerSettings: (state) => state.sidebarPlayerSettings,
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
      const sidebarPlayerSettings = basil.get("sidebarPlayerSettings");
      if (sidebarPlayerSettings) {
        try {
          this.sidebarPlayerSettings = JSON.parse(sidebarPlayerSettings);
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
    saveSidebarPlayerSettings(settings) {
      this.sidebarPlayerSettings = settings;
      const basil = useBasil(localStorageBasilOptions);
      basil.set(
        "sidebarPlayerSettings",
        this.sidebarPlayerSettings
          ? JSON.stringify(this.sidebarPlayerSettings)
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
