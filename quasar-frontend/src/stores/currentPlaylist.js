import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";
import { spieldosePlayer } from "src/boot/spieldosePlayer";

const localStorageBasilOptions = {
  namespace: "spieldose",
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  expireDays: 3650,
};

export const useCurrentPlaylistStore = defineStore("currentPlaylist", {
  state: () => ({
    elements: [],
    shuffleIndexes: [],
    currentIndex: -1,
    elementsLastChangeTimestamp: null,
  }),

  getters: {
    hasElements: (state) => state.elements && state.elements.length > 0,
    getElements: (state) => state.elements,
    elementCount: (state) => (state.elements ? state.elements.length : 0),
    getElementsLastChangeTimestamp: (state) =>
      state.elementsLastChangeTimestamp,
    getCurrentIndex: (state) =>
      spieldosePlayer.getShuffle()
        ? state.shuffleIndexes[state.currentIndex]
        : state.currentIndex,
    getCurrentElement: (state) =>
      state.currentIndex >= 0 && state.elements.length > 0
        ? state.elements[state.currentIndex]
        : null,
    getCurrentElementURL: (state) =>
      state.currentIndex >= 0 && state.elements.length > 0
        ? state.elements[state.currentIndex].url
        : null,
    allowSkipPrevious: (state) =>
      state.currentIndex > 0 && state.elements.length > 0,
    allowSkipNext: (state) =>
      state.currentIndex >= 0 &&
      state.elements.length > 0 &&
      state.currentIndex < state.elements.length - 1,
    allowPlay: (state) => true,
    allowPause: (state) => true,
    allowResume: (state) => true,
    allowStop: (state) => true,
  },

  actions: {
    load() {
      const basil = useBasil(localStorageBasilOptions);
      const currentPlaylistElements = basil.get("currentPlaylistElements");
      if (currentPlaylistElements) {
        this.elements = currentPlaylistElements;
      }
      const shuffleIndexes = basil.get("shuffleIndexes");
      if (shuffleIndexes) {
        this.shuffleIndexes = shuffleIndexes;
      }
      const currentPlaylistElementIndex = basil.get(
        "currentPlaylistElementIndex"
      );
      if (currentPlaylistElementIndex >= 0) {
        this.currentIndex = currentPlaylistElementIndex;
      }
    },
    saveCurrentElements() {
      const basil = useBasil(localStorageBasilOptions);
      basil.set("currentPlaylistElements", this.elements);
      basil.set("currentPlaylistElementIndex", this.currentIndex);
      basil.set("shuffleIndexes", this.shuffleIndexes);
      this.elementsLastChangeTimestamp = Date.now();
    },
    saveElements(newElements) {
      this.elements = newElements;
      this.shuffleIndexes = [...Array(newElements.length).keys()].sort(
        function () {
          return 0.5 - Math.random();
        }
      );
      this.currentIndex = newElements && newElements.length > 0 ? 0 : -1;
      this.saveCurrentElements();
    },
    appendElements(newElements) {
      this.elements = this.elements.concat(newElements);
      // TODO: rebuild/append shuffle index
      this.saveCurrentElements();
    },
    saveCurrentTrackIndex(newIndex) {
      this.currentIndex = newIndex >= 0 ? newIndex : -1;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("currentPlaylistElementIndex", this.currentIndex);
      /*
      if (this.currentIndex >= 0) {
        spieldosePlayer.actions.play(true);
      }
      */
    },

    // TODO: move to boot / spieldose
    skipPrevious() {
      this.saveCurrentTrackIndex(--this.currentIndex);
    },
    skipNext() {
      this.saveCurrentTrackIndex(++this.currentIndex);
    },
    clear() {
      this.elements = [];
      this.saveCurrentElements();
    },
  },
});
