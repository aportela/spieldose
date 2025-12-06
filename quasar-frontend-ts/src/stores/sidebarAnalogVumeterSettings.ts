import { defineStore, acceptHMRUpdate } from 'pinia';
import { showMiniAnalogVumeter as localStorageShowMiniAnalogVumeter, sidebarAnalogVumeterSmoothFactor as localStorageSidebarAnalogVumeterSmoothFactor } from 'src/composables/localStorage';

interface State {
  visible: boolean;
  smoothFactor: number;
};

export const useSidebarAnalogVumeterSettingsStore = defineStore('sidebarAnalogVumeterSettingsStore', {
  state: (): State => ({
    visible: localStorageShowMiniAnalogVumeter.get(),
    smoothFactor: localStorageSidebarAnalogVumeterSmoothFactor.get(),
  }),
  getters: {
    isVisible: (state) => state.visible,
    currentSmoothFactor: (state) => state.smoothFactor,
  },
  actions: {
    setVisibility(visible: boolean) {
      this.visible = visible;
      localStorageShowMiniAnalogVumeter.set(this.visible);
    },
    setSmoothFactor(factor: number) {
      this.smoothFactor = factor;
      localStorageSidebarAnalogVumeterSmoothFactor.set(this.smoothFactor);
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSidebarAnalogVumeterSettingsStore, import.meta.hot));
}
