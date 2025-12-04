import { defineStore, acceptHMRUpdate } from 'pinia';

export const useMiniSpectrumAnalyzerSettingsStore = defineStore('miniSpectrumAnalyzerSettingsStore', {
  state: () => ({}),
  getters: {},
  actions: {}
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useMiniSpectrumAnalyzerSettingsStore, import.meta.hot));
}
