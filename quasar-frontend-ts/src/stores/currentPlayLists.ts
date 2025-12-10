import { defineStore, acceptHMRUpdate } from 'pinia';
import { api } from 'src/composables/api';

import { type PlayList, type PlayListItemClass } from 'src/types/playList';
import { type AddPlayListResponse } from 'src/types/apiResponses';


interface State {
  selectedPlayListIndex: number;
  activePlayListIndex: number;
  activePlayListItemIndex: number;
  playLists: PlayList[];
};

export const useCurrentPlayListsStore = defineStore('currentPlayListsStore', {
  state: (): State => ({
    selectedPlayListIndex: 0,
    activePlayListIndex: 0,
    activePlayListItemIndex: 0,
    playLists: [],
  }),
  getters: {
    hasPlayLists: (state): boolean => state.playLists.length > 0,
    activePlayList: (state): PlayList | null => state.playLists.length > 0 ? state.playLists[state.activePlayListIndex]! : null,
    currentFileId: (state): string | null => state.playLists[state.activePlayListIndex]?.items[state.activePlayListItemIndex]?.file?.id ?? null,
    currentActivePlayListItem: (state): PlayListItemClass | null => state.playLists[state.activePlayListIndex]?.items[state.activePlayListItemIndex] ?? null,
  },
  actions: {
    setActivePlayListIndex(index: number): boolean {
      if (index > 0 && index < this.playLists.length) {
        this.activePlayListIndex = index;
        return (true);
      } else {
        console.error("setActivePlayListIndex - Invalid index", index);
        return (false);
      }
    },
    setSelectedPlayListId(id: string): boolean {
      const index = this.playLists.findIndex((playList) => playList.id === id);
      if (index !== -1) {
        this.selectedPlayListIndex = index;
        return (true);
      } else {
        console.error("setSelectedPlayListId - Missing index for id", id);
        return (false);
      }
    },
    setActivePlayListId(id: string): boolean {
      const index = this.playLists.findIndex((playList) => playList.id === id);
      if (index !== -1) {
        this.activePlayListIndex = index;
        return (true);
      } else {
        console.error("setActivePlayListId - Missing index for id", id);
        return (false);
      }
    },
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
      this.selectedPlayListIndex = this.playLists.length - 1;
    },
    async remove(id: string) {
      console.log("remove", id);
      await api.playList.remove(id);
      this.playLists = this.playLists.filter((playList) => playList.id !== id);
      this.activePlayListIndex = 0;
    },
    async randomFill(playListId: string) {
      console.log("randomFill", playListId);
      const index = this.playLists.findIndex((playList) => playList.id === playListId);
      if (index !== -1) {
        const filledPlayList = await api.playList.randomFill(playListId);
        this.playLists[index] = filledPlayList.data.playList;
        return (true);
      } else {
        console.error("empty - Missing index for id", playListId);
        return (false);
      }
    },
    empty(playListId: string): boolean {
      console.log("empty", playListId);
      const index = this.playLists.findIndex((playList) => playList.id === playListId);
      if (index !== -1) {
        this.playLists[index]!.items.length = 0;
        return (true);
      } else {
        console.error("empty - Missing index for id", playListId);
        return (false);
      }
    },
    closePlayListAtIndex(index: number) {
      console.log("closePlayListAtIndex", index);
      this.playLists.splice(index, 1);
      this.activePlayListIndex = 0;
    },
    savePlayListAtIndex(index: number) {
      console.log("savePlayListAtIndex", index);
    },
    async removePlayListAtIndex(index: number) {
      console.log("removePlayListAtIndex", index);
      await api.playList.remove(this.playLists[index]!.id);
      this.playLists = this.playLists.filter((playList) => playList.id !== this.playLists[index]!.id);
      this.playLists.splice(index, 1);
      this.activePlayListIndex = 0;
    },
    selectPlayListItem(playListIndex: number, playListItemIndex: number) {
      console.log("selectPlayListItem", playListIndex, playListItemIndex);
      if (
        playListIndex >= 0 &&
        playListIndex < this.playLists.length &&
        playListItemIndex >= 0 &&
        playListItemIndex < this.playLists[playListIndex]!.items.length
      ) {
        this.activePlayListIndex = playListIndex;
        this.activePlayListItemIndex = playListItemIndex;
      } else {
        console.error("selectPlayListItem - Invalid playListItemIndex", playListItemIndex);
      }
    },
    moveUpPlayListItem(playListIndex: number, playListItemIndex: number) {
      console.log("moveUpPlayListItem0", playListIndex, playListItemIndex);
      if (
        playListIndex >= 0 &&
        playListIndex < this.playLists.length &&
        playListItemIndex > 0 &&
        playListItemIndex < this.playLists[playListIndex]!.items.length
      ) {
        const [removedElement] = this.playLists[playListIndex]!.items.splice(playListItemIndex, 1);
        if (removedElement) {
          this.playLists[playListIndex]!.items.splice(playListItemIndex - 1, 0, removedElement);
        } else {
          console.error("moveUpPlayListItem - error removing element");
        }
      } else {
        console.error("moveUpPlayListItem - Invalid playListItemIndex", playListItemIndex);
      }
    },
    moveDownPlayListItem(playListIndex: number, playListItemIndex: number) {
      console.log("moveDownPlayListItem", playListIndex, playListItemIndex);

      if (
        playListIndex >= 0 &&
        playListIndex < this.playLists.length &&
        playListItemIndex >= 0 &&
        playListItemIndex < this.playLists[playListIndex]!.items.length - 1
      ) {
        const [removedElement] = this.playLists[playListIndex]!.items.splice(playListItemIndex, 1);
        if (removedElement) {
          this.playLists[playListIndex]!.items.splice(playListItemIndex + 1, 0, removedElement);
        } else {
          console.error("moveDownPlayListItem - error removing element");
        }
      } else {
        console.error("moveDownPlayListItem - Invalid playListItemIndex", playListItemIndex);
      }
    },
    removePlayListItem(playListIndex: number, playListItemIndex: number) {
      console.log("removePlayListItem", playListIndex, playListItemIndex);
      if (
        playListIndex >= 0 &&
        playListIndex < this.playLists.length &&
        playListItemIndex >= 0 &&
        playListItemIndex < this.playLists[playListIndex]!.items.length
      ) {
        const removedElement = this.playLists[playListIndex]!.items.splice(playListItemIndex, 1);
        if (removedElement.length > 0) {
          console.log("Item removed", removedElement);
        } else {
          console.error("removePlayListItem - error removing element");
        }
      } else {
        console.error("removePlayListItem - Invalid playListItemIndex", playListItemIndex);
      }
    },
    toggleFavoritePlayListItem(playListIndex: number, playListItemIndex: number) {
      console.log("toggleFavoritePlayListItem", playListIndex, playListItemIndex);
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useCurrentPlayListsStore, import.meta.hot));
}
