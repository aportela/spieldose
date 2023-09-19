import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";

const hashedSite = Array.from(window.location.host).reduce(
  (hash, char) => 0 | (31 * hash + char.charCodeAt(0)),
  0
);

const localStorageBasilOptions = {
  namespace: "spieldose#" + hashedSite,
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  expireDays: 3650,
};

export const useSpieldosePlayerStore = defineStore("spieldosePlayerStore", {
  state: () => ({
    data: {
      audio: null,
      audioMotionAnalyzerSource: null,
      player: {
        userInteracted: false,
        volume: 1,
        status: "stopped",
        muted: false,
        repeatMode: "none",
        shuffle: false,
      },
      currentPlaylistIndex: 0,
      playlists: [
        {
          id: null,
          name: null,
          owner: {
            id: null,
            name: null,
          },
          public: false,
          lastChangeTimestamp: null,
          currentElementIndex: -1,
          elements: [],
          shuffleIndexes: [], // stores a random elements shuffle indexes (for using when shuffle mode is on rather than sequential indexes like 1,2,3,4....n)
        },
      ],
    },
  }),
  getters: {
    allowSkipPrevious: (state) => state.data.playlists[0].currentElementIndex > 0,
    allowSkipNext: (state) => state.data.playlists[0].currentElementIndex < state.data.playlists[0].elements.length,
  },
  actions: {
    savePlayerSettings: function () {
      const basil = useBasil(localStorageBasilOptions);
      basil.set("playerSettings", this.data.player);
    },
    restorePlayerSettings: function () {
      const basil = useBasil(localStorageBasilOptions);
      const playerSettings = basil.get("playerSettings");
      if (playerSettings) {
        this.data.player = playerSettings;
        if (this.data.audio) {
          this.data.audio.volume = this.data.player.volume;
          this.data.audio.muted = this.data.player.muted;
        }
      }
    },
    /*
    hasPlaylistId: function (id) {
      return (
        this.data.playlists.length > 1 &&
        this.data.playlists.findIndex((playlist) => playlist.id == id) !== -1
      );
    },
    addNewPlaylist: function (playlist) {
      this.data.playlists.push({
        id: playlist.id,
        name: playlist.name,
        owner: playlist.owner,
        public: playlist.public || false,
        lastChangeTimestamp: Date.now(),
        currentElementIndex: 0,
        elements: playlist.tracks.map((track) => {
          return { track: track };
        }),
        shuffleIndexes: [...Array(playlist.tracks.length).keys()].sort(
          function () {
            return 0.5 - Math.random();
          }
        ),
      });
      this.data.currentPlaylistIndex = this.data.playlists.length - 1;
    },
    removePlaylist: function (id) {
      if (id) {
        const index =
          this.data.playlists.findIndex((playlist) => playlist.id == id) !== -1;
        if (index !== -1) {
          this.data.playlists.slice(index, 1);
        }
      }
    },
    */
    clearCurrentPlaylist: function () {
      this.data.playlists[0] = {
        id: null,
        name: null,
        owner: {
          id: null,
          name: null,
        },
        public: false,
        lastChangeTimestamp: Date.now(),
        currentIndex: -1,
        elements: [],
        shuffleIndexes: [],
      };
      this.data.currentPlaylistIndex = 0;
      this.saveCurrentPlaylist();
    },
    setPlaylistAsCurrent: function (playlist) {
      this.data.playlists[0] = {
        id: playlist.id,
        name: playlist.name,
        owner: playlist.owner,
        public: playlist.public || false,
        lastChangeTimestamp: Date.now(),
        currentElementIndex: playlist.tracks.length > 0 ? 0 : -1,
        elements: playlist.tracks.map((track) => {
          return { track: track };
        }),
        shuffleIndexes: [...Array(playlist.tracks.length).keys()].sort(
          function () {
            return 0.5 - Math.random();
          }
        ),
      };
      this.data.currentPlaylistIndex = 0;
      this.saveCurrentPlaylist();
    },
    sendElementsToCurrentPlaylist: function (elements) {
      const hasValues =
        elements && Array.isArray(elements) && elements.length > 0;
      if (hasValues) {
        this.data.playlists[0] = {
          id: null,
          name: null,
          owner: {
            id: null,
            name: null,
          },
          public: false,
          lastChangeTimestamp: Date.now(),
          currentElementIndex: hasValues ? 0 : -1,
          elements: hasValues ? elements : [],
          shuffleIndexes: hasValues
            ? [...Array(elements.length).keys()].sort(function () {
              return 0.5 - Math.random();
            })
            : [],
        };
        this.data.currentPlaylistIndex = 0;
        this.saveCurrentPlaylist();
      }
    },
    appendElementsToCurrentPlaylist: function (elements) {
      const hasPreviousElements = this.data.playlists[0].elements.length > 0;
      const hasValues =
        elements && Array.isArray(elements) && elements.length > 0;
      if (hasValues) {
        this.data.playlists[0].elements.concat(elements);
        this.data.playlists[0].shuffleIndexes = [
          ...Array(this.data.playlists[0].elements.length).keys(),
        ].sort(function () {
          return 0.5 - Math.random();
        });
        if (!hasPreviousElements) {
          this.data.playlists[0].currentElementIndex = hasValues ? 0 : -1;
        }
        this.data.currentPlaylistIndex = 0;
        this.data.playlists[0].lastChangeTimestamp = Date.now();
        this.saveCurrentPlaylist();
      }
    },
    getCurrentPlaylist: function () {
      return this.data.playlists[0];
    },
    getCurrentPlaylistElement: function () {
      if (this.data.playlists[0].elements.length > 0 && this.data.playlists[0].currentElementIndex >= 0) {
        return (this.data.playlists[0].elements[this.data.playlists[0].currentElementIndex]);
      } else {
        return (null);
      }
    },
    restoreCurrentPlaylist: function () {
      this.clearCurrentPlaylist();
      const basil = useBasil(localStorageBasilOptions);
      const currentPlaylist = basil.get("currentPlaylist");
      if (currentPlaylist) {
        this.data.playlists[0] = currentPlaylist;
      }
    },
    saveCurrentPlaylist: function () {
      const basil = useBasil(localStorageBasilOptions);
      basil.set("currentPlaylist", this.data.playlists[0]);
    },
    skipPrevious: function () {
      if (this.allowSkipPrevious) {
        this.data.playlists[0].currentElementIndex--;
        this.data.playlists[0].lastChangeTimestamp = Date.now();
        this.saveCurrentPlaylist();
      }
    },
    skipNext: function () {
      if (this.allowSkipNext) {
        this.data.playlists[0].currentElementIndex++;
        this.data.playlists[0].lastChangeTimestamp = Date.now();
        this.saveCurrentPlaylist();
      }
    },
    setVolume: function (volume) {
      if (volume >= 0 && volume <= 1) {
        this.data.player.volume = volume;
        this.data.audio.volume = volume;
        this.savePlayerSettings();
      }
    },
    toggleMute: function () {
      this.data.player.muted = !this.data.player.muted;
      this.data.audio.muted = this.data.player.muted;
      this.savePlayerSettings();
    }
  },
});
