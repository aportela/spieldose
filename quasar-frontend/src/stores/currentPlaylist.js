import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";
//import { usePlayer } from "stores/player";

//const player = usePlayer();

const localStorageBasilOptions = {
  namespace: "spieldose",
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  expireDays: 3650,
};

export const useCurrentPlaylistStore = defineStore("currentPlaylist", {
  state: () => ({
    elements: [],
    currentIndex: -1,
    elementsLastChangeTimestamp: null,
  }),

  getters: {
    hasElements: (state) => state.elements && state.elements.length > 0,
    getElements: (state) => state.elements,
    elementCount: (state) => (state.elements ? state.elements.length : 0),
    getElementsLastChangeTimestamp: (state) =>
      state.elementsLastChangeTimestamp,
    getCurrentIndex: (state) => state.currentIndex,
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
      this.elementsLastChangeTimestamp = Date.now();
    },
    saveElements(newElements) {
      this.elements = newElements;
      this.currentIndex = newElements && newElements.length > 0 ? 0 : -1;
      this.saveCurrentElements();
    },
    appendElements(newElements) {
      this.elements = this.elements.concat(newElements);
      this.saveCurrentElements();
    },
    saveCurrentTrackIndex(newIndex) {
      this.currentIndex = newIndex >= 0 ? newIndex : -1;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("currentPlaylistElementIndex", this.currentIndex);
      /*
      if (this.currentIndex >= 0) {
        player.play(true);
      }
      */
    },
    skipPrevious() {
      this.saveCurrentTrackIndex(--this.currentIndex);
    },
    skipNext() {
      this.saveCurrentTrackIndex(++this.currentIndex);
    },
    clear() {
      this.elements = [];
      this.currentIndex = -1;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("currentPlaylistElements", []);
      basil.set(-1);
      this.elementsLastChangeTimestamp = Date.now();
    },
    setFavoriteTrack(trackId, favorited) {
      const index = this.elements.findIndex(
        (element) => element.track && element.track.id == trackId
      );
      if (index !== -1) {
        this.elements[index].track.favorited = favorited;
        this.saveCurrentElements();
      }
    },
    unSetFavoriteTrack(trackId) {
      const index = this.elements.findIndex(
        (element) => element.track && element.track.id == trackId
      );
      if (index !== -1) {
        this.elements[index].track.favorited = null;
        this.saveCurrentElements();
      }
    },
  },
});
