import { defineStore, acceptHMRUpdate } from 'pinia';
import { api } from 'src/composables/api';

import { type AddPlayListResponse } from 'src/types/apiResponses';

interface PlayList {
  id: string;
  name: string;
  items: [];
}

interface State {
  activePlayListIndex: number;
  playLists: PlayList[];
};

export const useCurrentPlayListsStore = defineStore('currentPlayListsStore', {
  state: (): State => ({
    activePlayListIndex: 0,
    playLists: [],
  }),
  getters: {
    hasPlayLists: (state): boolean => state.playLists.length > 0,
    activePlayList: (state): PlayList | null => state.playLists.length > 0 ? state.playLists[state.activePlayListIndex]! : null,
  },
  actions: {
    async init() {
      const response = await api.playList.getCurrentPlayLists();
      this.playLists = response.data.playLists;
    },
    async add(id: string, name: string) {
      const PlayList: AddPlayListResponse = await api.playList.add(id, name);
      this.playLists.push(
        {
          id: PlayList.data.playList.id,
          name: PlayList.data.playList.name,
          items: PlayList.data.playList.items,
        }
      );
    },
    async remove(id: string) {
      await api.playList.remove(id);
      this.playLists = this.playLists.filter((playList) => playList.id !== id);
    },
    async randomFillActivePlayList() {
      const filledPlayList = await api.playList.randomFill(this.playLists[this.activePlayListIndex]!.id);
      console.log(this.playLists[this.activePlayListIndex]!.items.length);
      this.playLists[this.activePlayListIndex] = filledPlayList.data.playList;
      console.log(this.playLists[this.activePlayListIndex]!.items.length);
    },
    closeAtIndex(index: number) {
      this.playLists.splice(index, 1);
    },
    removeAtIndex(index: number) {
      console.debug(index);
    },
    saveAtIndex(index: number) {
      console.debug(index);
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useCurrentPlayListsStore, import.meta.hot));
}
