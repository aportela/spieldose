import { defineStore, acceptHMRUpdate } from 'pinia';

export const useCurrentPlaylistItem = defineStore('currentPlaylistItem', {
  state: () => ({}),
  getters: {},
  actions: {}
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useCurrentPlaylistItem, import.meta.hot));
}
