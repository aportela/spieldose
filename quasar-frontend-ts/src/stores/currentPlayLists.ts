import { defineStore, acceptHMRUpdate } from 'pinia';

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
    add(id: string, name: string) {
      this.playLists.push({ id: id, name: name });
      this.activePlayListIndex = this.playLists.length - 1;
    },
    remove(id: string) {
      this.playLists = this.playLists.filter((playList) => playList.id !== id);
      console.log(id);
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
