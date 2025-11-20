import { LocalStorage } from "quasar";

import { LOCAL_STORAGE_NAMESPACE } from "src/constants";

export function useLocalStorage() {
  const get = (key, defaultValue = null) => {
    try {
      const savedValue = LocalStorage.getItem(LOCAL_STORAGE_NAMESPACE + key);
      return savedValue === null ? defaultValue : savedValue;
    } catch (error) {
      console.error("Error accessing localStorage:", error);
      return defaultValue;
    }
  };

  const set = (key, value) => {
    try {
      LocalStorage.setItem(LOCAL_STORAGE_NAMESPACE + key, value);
    } catch (error) {
      console.error("Error accessing localStorage:", error);
    }
  };

  const remove = (key) => {
    try {
      LocalStorage.removeItem(LOCAL_STORAGE_NAMESPACE + key);
    } catch (error) {
      console.error("Error accessing localStorage:", error);
    }
  };

  const jwt = {
    get() {
      return get("jwt");
    },
    set(value) {
      set("jwt", value);
    },
    remove() {
      remove("jwt");
    },
  };

  const email = {
    get() {
      return get("email");
    },
    set(value) {
      set("email", value);
    },
    remove() {
      remove("email");
    },
  };

  const darkMode = {
    get() {
      return get("darkMode");
    },
    set(value) {
      set("darkMode", !!value);
    },
    remove() {
      remove("darkMode");
    },
  };

  const locale = {
    get() {
      return get("locale");
    },
    set(value) {
      set("locale", value);
    },
    remove() {
      remove("locale");
    },
  };

  const showToolTips = {
    get() {
      return get("showToolTips", false);
    },
    set(value) {
      set("showToolTips", !!value);
    },
    remove() {
      remove("showToolTips");
    },
  };

  const playerVolume = {
    get() {
      return get("playerVolume", 1);
    },
    set(value) {
      set("playerVolume", value);
    },
    remove() {
      remove("playerVolume");
    },
  };

  const playerMuted = {
    get() {
      return get("playerMuted", false);
    },
    set(value) {
      set("playerMuted", !!value);
    },
    remove() {
      remove("playerMuted");
    },
  };

  const showMiniSpectrumAnalyzer = {
    get() {
      return get("showMiniSpectrumAnalyzer", true);
    },
    set(value) {
      set("showMiniSpectrumAnalyzer", !!value);
    },
    remove() {
      remove("showMiniSpectrumAnalyzer");
    },
  };

  const playerMiniAnalyzerMode = {
    get() {
      return get("playerMiniAnalyzerMode", 7);
    },
    set(value) {
      set("playerMiniAnalyzerMode", value);
    },
    remove() {
      remove("playerMiniAnalyzerMode");
    },
  };

  const playerMiniAnalyzerFPS = {
    get() {
      return get("playerMiniAnalyzerFPS", 30);
    },
    set(value) {
      set("playerMiniAnalyzerFPS", value);
    },
    remove() {
      remove("playerMiniAnalyzerFPS");
    },
  };

  const playerMiniAnalyzerBarSpace = {
    get() {
      return get("playerMiniAnalyzerBarSpace", 0.2);
    },
    set(value) {
      set("playerMiniAnalyzerBarSpace", value);
    },
    remove() {
      remove("playerMiniAnalyzerBarSpace");
    },
  };

  const playerMiniAnalyzerHeight = {
    get() {
      return get("playerMiniAnalyzerHeight", 40);
    },
    set(value) {
      set("playerMiniAnalyzerHeight", value);
    },
    remove() {
      remove("playerMiniAnalyzerHeight");
    },
  };

  const playerMiniAnalyzerChannelLayout = {
    get() {
      return get("playerMiniAnalyzerChannelLayout", "single");
    },
    set(value) {
      set("playerMiniAnalyzerChannelLayout", value);
    },
    remove() {
      remove("playerMiniAnalyzerChannelLayout");
    },
  };

  const playerMiniAnalyzerShowPeaks = {
    get() {
      return get("playerMiniAnalyzerShowPeaks", true);
    },
    set(value) {
      set("playerMiniAnalyzerShowPeaks", !!value);
    },
    remove() {
      remove("playerMiniAnalyzerShowPeaks");
    },
  };

  const playerMiniAnalyzerLedBars = {
    get() {
      return get("playerMiniAnalyzerLedBars", true);
    },
    set(value) {
      set("playerMiniAnalyzerLedBars", !!value);
    },
    remove() {
      remove("playerMiniAnalyzerLedBars");
    },
  };

  const playerMiniAnalyzerTrueLeds = {
    get() {
      return get("playerMiniAnalyzerTrueLeds", true);
    },
    set(value) {
      set("playerMiniAnalyzerTrueLeds", !!value);
    },
    remove() {
      remove("playerMiniAnalyzerTrueLeds");
    },
  };

  return {
    jwt,
    email,
    darkMode,
    locale,
    showToolTips,
    playerVolume,
    playerMuted,
    showMiniSpectrumAnalyzer,
    playerMiniAnalyzerMode,
    playerMiniAnalyzerFPS,
    playerMiniAnalyzerBarSpace,
    playerMiniAnalyzerHeight,
    playerMiniAnalyzerChannelLayout,
    playerMiniAnalyzerShowPeaks,
    playerMiniAnalyzerLedBars,
    playerMiniAnalyzerTrueLeds,
  };
}
