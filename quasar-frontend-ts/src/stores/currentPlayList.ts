import { defineStore, acceptHMRUpdate } from 'pinia';
import { type PlayList as PlayListInterface, PlayListClass } from 'src/types/playList';

import { api } from 'src/composables/api';

interface State {
  playList: PlayListInterface,
  currentItemIndex: number;
};

export const useCurrentPlayListStore = defineStore('currentPlayListStore', {
  state: (): State => ({
    playList: new PlayListClass(null, null, []),
    currentItemIndex: 0,
  }),
  getters: {
    hasItems: (state): boolean => state.playList !== null && state.playList.items.length > 0,
  },
  actions: {
    async init() {
      return await api.currentPlayList.get();
    },
    set(playList: PlayListInterface, currentItemIndex: number) {
      this.playList = playList;
      this.currentItemIndex = currentItemIndex;
      // TODO: playerStore & others
    },
    empty() {
      this.playList.items.length = 0;
      this.currentItemIndex = 0;
      // TODO: playerStore & others
    },
    async addTracks(trackIds: string[], append: boolean, autoPlay: boolean) {
      if (trackIds.length > 0) {
        if (append) {
          // TODO
        } else {
          // TODO
        }
      } else {
        console.error("No tracks specified");
      }
      console.log("addTracks", trackIds, append, autoPlay);
      const response = await api.currentPlayList.get();
      console.log(response.data);
    },
    addRadioStation(radioStationId: string, append: boolean, autoPlay: boolean) {
      console.log("addRadioStation", radioStationId, append, autoPlay);
    },
    removeItemAtIndex(index: number) {
      if (index >= 0 && index <= this.playList.items.length - 1) {
        // TODO: check currentItemIndex
        console.log("removeItemAtIndex", index);
      } else {
        console.error("removeItemAtIndex, invalid index", index);
      }
    },
    moveUpItemAtIndex(index: number) {
      if (index > 0) {
        // TODO: check currentItemIndex
        console.log("moveUpItemAtIndex", index);
      } else {
        console.error("moveUpItemAtIndex, invalid index", index);
      }
    },
    moveDownItemAtIndex(index: number) {
      if (index >= 0 && index <= this.playList.items.length - 1) {
        // TODO: check currentItemIndex
        console.log("moveDownItemAtIndex", index);
      } else {
        console.error("moveDownItemAtIndex, invalid index", index);
      }
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useCurrentPlayListStore, import.meta.hot));
}
