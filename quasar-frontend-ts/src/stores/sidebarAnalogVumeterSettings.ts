import { defineStore, acceptHMRUpdate } from 'pinia';
import {
  showMiniAnalogVumeter as localStorageShowMiniAnalogVumeter, sidebarAnalogVumeterSmoothFactor as localStorageSidebarAnalogVumeterSmoothFactor,
  sidebarAnalogVumeterFPS as localStorageSidebarAnalogVumeterFPS
} from 'src/composables/localStorage';

interface State {
  visible: boolean;
  smoothFactor: number;
  fps: number;
};

export const useSidebarAnalogVumeterSettingsStore = defineStore('sidebarAnalogVumeterSettingsStore', {
  state: (): State => ({
    visible: localStorageShowMiniAnalogVumeter.get(),
    smoothFactor: localStorageSidebarAnalogVumeterSmoothFactor.get(),
    fps: localStorageSidebarAnalogVumeterFPS.get(),
  }),
  getters: {
    isVisible: (state) => state.visible,
    currentSmoothFactor: (state) => state.smoothFactor,
    currentFPS: (state) => state.fps,
  },
  actions: {
    setVisibility(visible: boolean) {
      this.visible = visible;
      localStorageShowMiniAnalogVumeter.set(this.visible);
    },
    setSmoothFactor(factor: number) {
      this.smoothFactor = factor;
      localStorageSidebarAnalogVumeterSmoothFactor.set(this.smoothFactor);
    },
    setFPS(fps: number) {
      if (fps >= 0 && fps <= 144) {
        this.fps = fps;
        localStorageSidebarAnalogVumeterFPS.set(this.fps);
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
