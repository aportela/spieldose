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

export const useSpieldoseStore = defineStore("spieldoseStore", {
  state: () => ({
    data: {
      audio: null,
      audioMotionAnalyzerSource: null,
      player: {
        userInteracted: false,
        volume: 0.1,
        status: "stopped",
        muted: false,
        repeatMode: "none",
        shuffle: false,
        sidebarAudioMotionAnalyzer: {
          visible: true,
          mode: 7,
        },
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
    getAudioInstance: (state) => state.data.audio,
    hasPreviousUserInteractions: (state) => state.data.player.userInteracted,
    getAudioMotionAnalyzerSource: (state) =>
      state.data.audioMotionAnalyzerSource,
    isSidebarAudioMotionAnalyzerVisible: (state) =>
      state.data.player.sidebarAudioMotionAnalyzer.visible,
    getSidebarAudioMotionAnalyzerMode: (state) =>
      state.data.player.sidebarAudioMotionAnalyzer.mode,
    getPlayerStatus: (state) => state.data.player.status,
    isMuted: (state) => state.data.player.muted,
    isPlaying: (state) => state.data.player.status == "playing",
    isStopped: (state) => state.data.player.status == "stopped",
    isPaused: (state) => state.data.player.status == "paused",
    getVolume: (state) => state.data.player.volume,
    getDuration: (state) => (state.data.audio ? state.data.audio.duration : 0),
    getRepeatMode: (state) => state.data.player.repeatMode,
    getShuffle: (state) => state.data.player.shuffle,
    getCurrentPlaylist: (state) => state.data.playlists[0],
    getCurrentPlaylistIndex: (state) =>
      state.data.playlists[0].currentElementIndex,
    getCurrentPlaylistLastChangedTimestamp: (state) =>
      state.data.playlists[0].lastChangeTimestamp,
    getCurrentPlaylistElement: (state) =>
      state.data.playlists[0].elements.length > 0 &&
      state.data.playlists[0].currentElementIndex >= 0
        ? state.data.playlists[0].elements[
            state.data.playlists[0].currentElementIndex
          ]
        : null,
    getCurrentPlaylistElementURL: (state) =>
      state.data.playlists[0].elements.length > 0 &&
      state.data.playlists[0].currentElementIndex >= 0
        ? state.data.playlists[0].elements[
            state.data.playlists[0].currentElementIndex
          ].url
        : null,
    hasElements: (state) =>
      state.data.playlists[0].elements &&
      state.data.playlists[0].elements.length > 0,
    allowSkipPrevious: (state) =>
      state.data.playlists[0].currentElementIndex > 0,
    allowSkipNext: (state) =>
      state.data.playlists[0].currentElementIndex <
      state.data.playlists[0].elements.length,
  },
  actions: {
    create: function (src) {
      if (this.data.audio == null) {
        if (src) {
          this.data.audio = new Audio(src);
        } else {
          this.data.audio = new Audio();
        }
      } else {
        this.data.audio.src = null;
        // required for radio stations streams
        this.data.audio.crossOrigin = "anonymous";
      }
      this.restorePlayerSettings();
      this.restoreCurrentPlaylist();
    },
    setAudioSource: function (src) {
      if (this.data.audio) {
        this.data.audio.src = src;
      }
    },
    setAudioMotionAnalyzerSource: function (source) {
      this.data.audioMotionAnalyzerSource = source;
    },
    toggleSidebarAudioMotionAnalyzer: function () {
      this.data.player.sidebarAudioMotionAnalyzer.visible =
        !this.data.player.sidebarAudioMotionAnalyzer.visible;
      this.savePlayerSettings();
    },
    setSidebarAudioMotionAnalyzerMode: function (mode) {
      this.data.player.sidebarAudioMotionAnalyzer.mode = mode;
      this.savePlayerSettings();
    },
    interact: function () {
      this.data.player.userInteracted = true;
    },
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
    setVolume: function (volume) {
      if (volume >= 0 && volume <= 1) {
        this.data.player.volume = volume;
        if (this.data.audio) {
          this.data.audio.volume = volume;
        }
        this.savePlayerSettings();
      }
    },
    toggleMute: function () {
      this.data.player.muted = !this.data.player.muted;
      if (this.data.audio) {
        this.data.audio.muted = this.data.player.muted;
      }
      this.savePlayerSettings();
    },
    setCurrentTime: function (time) {
      if (this.data.audio) {
        this.data.audio.currentTime = time;
      }
    },
    play: function (ignoreStatus) {
      if (this.data.player.userInteracted) {
        if (ignoreStatus) {
          if (this.data.audio) {
            this.data.audio.play();
          }
          this.data.player.status = "playing";
        } else {
          if (this.isPlaying) {
            if (this.data.audio) {
              this.data.audio.pause();
            }
            this.data.player.status = "paused";
          } else if (this.isPaused) {
            if (this.data.audio) {
              this.data.audio.play();
            }
            this.data.player.status = "playing";
          } else {
            // TODO: required ?
            //audio.load();
            if (this.data.audio) {
              this.data.audio.play();
            }
            this.data.player.status = "playing";
          }
        }
      } else {
        console.error("play error: no previous user interactions");
      }
    },
    pause: function () {
      if (this.isPlaying) {
        if (this.data.audio) {
          this.data.audio.pause();
        }
        this.data.player.status = "paused";
      }
    },
    resume: function () {
      if (this.isPaused) {
        if (this.data.audio) {
          this.data.audio.play();
        }
        this.data.player.status = "playing";
      }
    },
    stop: function () {
      if (!this.isStopped) {
        if (this.data.audio) {
          this.data.audio.pause();
          this.data.audio.currentTime = 0;
        }
        this.data.player.status = "stopped";
      }
    },
    toggleRepeatMode: function () {
      switch (this.data.player.repeatMode) {
        case "none":
          this.data.player.repeatMode = "track";
          // TODO: launch event
          break;
        case "track":
          this.data.player.repeatMode = "playlist";
          // TODO: launch event
          break;
        case "playlist":
          this.data.player.repeatMode = "none";
          // TODO: launch event
          break;
      }
    },
    toggleShuffeMode: function () {
      this.data.player.shuffle = !this.data.player.shuffle;
    },
    clearCurrentPlaylist: function (ignoreSave) {
      this.stop();
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
      if (!ignoreSave) {
        this.saveCurrentPlaylist();
      }
    },
    setPlaylistAsCurrent: function (playlist) {
      this.stop();
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
      this.play();
    },
    sendElementsToCurrentPlaylist: function (elements) {
      this.stop();
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
        this.play();
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
        if (!this.isPlaying) {
          this.play();
        }
      }
    },
    restoreCurrentPlaylist: function () {
      this.clearCurrentPlaylist(true);
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
        this.interact();
        this.data.playlists[0].currentElementIndex--;
        this.data.playlists[0].lastChangeTimestamp = Date.now();
        // TODO evaluate cost of this for big playlists (elements do not change)
        this.saveCurrentPlaylist();
        // TODO: required ?
        if (!this.isPlaying) {
          this.play(true);
        }
      }
    },
    skipNext: function () {
      if (this.allowSkipNext) {
        this.interact();
        this.data.playlists[0].currentElementIndex++;
        this.data.playlists[0].lastChangeTimestamp = Date.now();
        // TODO evaluate cost of this for big playlists (elements do not change)
        this.saveCurrentPlaylist();
        // TODO: required ?
        if (!this.isPlaying) {
          this.play(true);
        }
      }
    },
    skipToIndex: function (index) {
      if (index >= 0 && index < this.data.playlists[0].elements.length) {
        this.interact();
        this.data.playlists[0].currentElementIndex = index;
        this.data.playlists[0].lastChangeTimestamp = Date.now();
        // TODO evaluate cost of this for big playlists (elements do not change)
        this.saveCurrentPlaylist();
        // TODO: required ?
        if (!this.isPlaying) {
          this.play(true);
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
  },
});
