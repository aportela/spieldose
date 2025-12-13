import { defineStore, acceptHMRUpdate } from 'pinia';

export const usePlayListVisibleColumnsStore = defineStore('playListVisibleColumnsStore', {
  state: () => ({}),
  getters: {},
  actions: {},
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(usePlayListVisibleColumnsStore, import.meta.hot));
}
