import { defineStore } from "pinia";

export const useSpieldosePlayerStore = defineStore("spieldosePlayerStore", {
  state: () => ({
    data: {
      audio: null,
      audioMotionAnalyzerSource: null,
      userInteracted: false,
      playerStatus: "stopped",
      muted: false,
      repeatMode: "none",
      shuffle: false,
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
          currentElementIndex: 0,
          elements: [],
          shuffleIndexes: [], // stores a random elements shuffle indexes (for using when shuffle mode is on rather than sequential indexes like 1,2,3,4....n)
        },
      ],
    },
  }),
  actions: {
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
      this.playlists[0] = {
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
    },
    setPlaylistAsCurrent: function (playlist) {
      this.data.playlists[0] = {
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
      };
      this.data.currentPlaylistIndex = 0;
    },
    sendElementsToCurrentPlaylist: function (elements) {
      const hasValues =
        elements && Array.isArray(elements) && elements.length > 0;
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
    },
    appendElementsToCurrentPlaylist: function (elements) {
      const hasValues =
        elements && Array.isArray(elements) && elements.length > 0;
      if (hasValues) {
        this.data.playlists[0].elements.concat(elements);
        this.data.playlists[0].shuffleIndexes = [
          ...Array(this.data.playlists[0].elements.length).keys(),
        ].sort(function () {
          return 0.5 - Math.random();
        });
        this.data.currentPlaylistIndex = 0;
        this.data.playlists[0].lastChangeTimestamp = Date.now();
      }
    },
    getCurrentPlaylist: function () {
      return this.data.playlists[0];
    },
  },
});
