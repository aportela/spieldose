import { defineStore, acceptHMRUpdate } from 'pinia';
import { showMiniAnalogVumeter as localStorageShowMiniAnalogVumeter } from 'src/composables/localStorage';

interface State {
  visible: boolean;
};

export const useMiniAnalogVumeterSettingsStore = defineStore('miniAnalogVumeterStore', {
  state: (): State => ({
    visible: localStorageShowMiniAnalogVumeter.get(),
  }),
  getters: {
    isVisible: (state) => state.visible,
  },
  actions: {
    setVisibility(visible: boolean) {
      this.visible = visible;
      localStorageShowMiniAnalogVumeter.set(this.visible);
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useMiniAnalogVumeterSettingsStore, import.meta.hot));
}
