import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from 'src/composables/localStorage';

const localStorageShowMiniAnalogVumeter = createStorageEntry<boolean>("visualizations.sidebar.analogVumeter.visible", false);
const localStorageSidebarAnalogVumeterSmoothFactor = createStorageEntry<number>("visualizations.sidebar.analogVumeter.smoothFactor", 0.1);
const localStorageSidebarAnalogVumeterFPS = createStorageEntry<number>("visualizations.sidebar.analogVumeter.fps", 30);

interface State {
  settings: {
    visible: boolean;
    smoothFactor: number;
    fps: number;
  }
};

export const useSidebarAnalogVumeterSettingsStore = defineStore('sidebarAnalogVumeterSettingsStore', {
  state: (): State => ({
    settings: {
      visible: localStorageShowMiniAnalogVumeter.get(),
      smoothFactor: localStorageSidebarAnalogVumeterSmoothFactor.get(),
      fps: localStorageSidebarAnalogVumeterFPS.get(),
    },
  }),
  getters: {
    visible: (state) => state.settings.visible,
    smoothFactor: (state) => state.settings.smoothFactor,
    fps: (state) => state.settings.fps,
  },
  actions: {
    setVisibility(visible: boolean) {
      this.settings.visible = visible;
      localStorageShowMiniAnalogVumeter.set(this.settings.visible);
    },
    setSmoothFactor(factor: number) {
      this.settings.smoothFactor = factor;
      localStorageSidebarAnalogVumeterSmoothFactor.set(this.settings.smoothFactor);
    },
    setFPS(fps: number) {
      if (fps >= 0 && fps <= 144) {
        this.settings.fps = fps;
        localStorageSidebarAnalogVumeterFPS.set(this.settings.fps);
      } else {
        console.error(
          "Sidebar analog vumeter settings = setFPS() => invalid fps",
          fps,
        );
      }
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSidebarAnalogVumeterSettingsStore, import.meta.hot));
}
