import { defineStore, acceptHMRUpdate } from 'pinia';

export const usePlayerStore = defineStore('playerStore', {
  state: () => ({}),
  getters: {},
  actions: {}
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(usePlayerStore, import.meta.hot));
}
