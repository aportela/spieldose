import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";

const localStorageBasilOptions = {
  namespace: "spieldose",
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  expireDays: 3650,
};

export const useCurrentPlaylistStore = defineStore("currentPlaylist", {
  state: () => ({
    tracks: [],
    currentIndex: -1,
  }),

  getters: {
    getTracks: (state) => state.tracks,
    getCurrentIndex: (state) => state.currentIndex,
    getCurrentTrack: (state) =>
      state.currentIndex >= 0 && state.tracks.length > 0
        ? state.tracks[state.currentIndex]
        : null,
    allowSkipPrevious: (state) =>
      state.currentIndex > 0 && state.tracks.length > 0,
    allowSkipNext: (state) =>
      state.currentIndex >= 0 &&
      state.tracks.length > 0 &&
      state.currentIndex < state.tracks.length - 1,
  },

  actions: {
    load() {
      const basil = useBasil(localStorageBasilOptions);
      const currentPlaylistTracks = basil.get("currentPlaylistTracks");
      if (currentPlaylistTracks) {
        this.tracks = currentPlaylistTracks;
      }
      const currentPlaylistTrackIndex = basil.get("currentPlaylistTrackIndex");
      if (currentPlaylistTrackIndex >= 0) {
        this.currentIndex = currentPlaylistTrackIndex;
      }
    },
    saveTracks(newTracks) {
      this.tracks = newTracks;
      this.currentIndex = newTracks && newTracks.length > 0 ? 0 : -1;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("currentPlaylistTracks", newTracks);
      basil.set(
        "currentPlaylistTrackIndex",
        newTracks && newTracks.length > 0 ? 0 : -1
      );
    },
    saveCurrentTrackIndex(newIndex) {
      this.currentIndex = newIndex >= 0 ? newIndex : -1;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("currentPlaylistTrackIndex", newIndex >= 0 ? newIndex : -1);
    },
  },
});
