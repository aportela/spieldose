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
    currentTrackIndex: -1,
  }),

  getters: {
    tracks: (state) => state.tracks,
    currentTrackIndex: (state) => state.currentTrackIndex,
  },

  actions: {
    load() {
      const basil = useBasil(localStorageBasilOptions);
      const tracks = basil.get("tracks");
      if (tracks) {
        this.tracks = tracks;
      }
      const currentTrackIndex = basil.get("currentTrackIndex");
      if (currentTrackIndex) {
        this.currentTrackIndex = currentTrackIndex;
      }
    },
    saveTracks(tracks) {
      this.tracks = tracks;
      const basil = useBasil(localStorageBasilOptions);
      basil.set("tracks", tracks);
    },
  },
});
