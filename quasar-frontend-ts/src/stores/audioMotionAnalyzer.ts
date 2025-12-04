import { defineStore, acceptHMRUpdate } from 'pinia';

export const useAudioMotionAnalyzerStore = defineStore('audioMotionAnalyzerStore', {
  state: () => ({}),
  getters: {},
  actions: {}
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useAudioMotionAnalyzerStore, import.meta.hot));
}
