import { defineStore, acceptHMRUpdate } from 'pinia';
import { type PlayList as PlayListInterface } from 'src/types/playList';
interface State {
  playList: PlayListInterface | null
};

export const useCurrentPlayListStore = defineStore('currentPlayListStore', {
  state: (): State => ({
    playList: null,
  }),
  getters: {
    hasItems: (state): boolean => state.playList !== null && state.playList.items.length > 0,
  },
  actions: {
    init() {
      // TODO: load playlist
    },
    empty() {
      // TODO: empty playlist
    },
    addTracks(trackIds: string[], append: boolean, autoPlay: boolean) {
      if (this.playList !== null) {
        console.log("addTracks", trackIds, append, autoPlay);
      } else {
        console.error("Playlist not initializated");
      }
    },
    addRadioStation(radioStationId: string, append: boolean, autoPlay: boolean) {
      if (this.playList !== null) {
        console.log("addRadioStation", radioStationId, append, autoPlay);
      } else {
        console.error("Playlist not initializated");
      }
    },
    removeItemAtIndex(index: number) {
      if (this.playList !== null) {
        if (index >= 0 && index <= this.playList.items.length - 1) {
          console.log("removeItemAtIndex", index);
        } else {
          console.error("removeItemAtIndex, invalid index", index);
        }
      } else {
        console.error("Playlist not initializated");
      }
    },
    moveUpItemAtIndex(index: number) {
      if (this.playList !== null) {
        if (index > 0) {
          console.log("moveUpItemAtIndex", index);
        } else {
          console.error("moveUpItemAtIndex, invalid index", index);
        }
      } else {
        console.error("Playlist not initializated");
      }
    },
    moveDownItemAtIndex(index: number) {
      if (this.playList !== null) {
        if (index >= 0 && index <= this.playList.items.length - 1) {
          console.log("moveDownItemAtIndex", index);
        } else {
          console.error("moveDownItemAtIndex, invalid index", index);
        }
      } else {
        console.error("Playlist not initializated");
      }
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useCurrentPlayListStore, import.meta.hot));
}
