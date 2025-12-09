import { defineStore, acceptHMRUpdate } from 'pinia';
import { api } from 'src/composables/api';

import { type AddPlayListResponse } from 'src/types/apiResponses';

interface PlayList {
  id: string;
  name: string;
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
  },
  actions: {
    async add(id: string, name: string) {
      const newPlayList: AddPlayListResponse = await api.playList.add(id, name);
      this.playLists.push(
        {
          id: newPlayList.data.playList.id,
          name: newPlayList.data.playList.name
        }
      );
    },
    async remove(id: string) {
      await api.playList.remove(id);
      this.playLists = this.playLists.filter((playList) => playList.id !== id);
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
