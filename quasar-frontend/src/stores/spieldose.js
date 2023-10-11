import { defineStore } from "pinia";
import { default as useBasil } from "basil.js";
import { api } from "boot/axios";

const hashedSite = Array.from(window.location.host).reduce(
  (hash, char) => 0 | (31 * hash + char.charCodeAt(0)),
  0
);

const localStorageBasilOptions = {
  namespace: "spieldose#" + hashedSite,
  storages: ["local", "cookie", "session", "memory"],
  storage: "local",
  sameSite: "strict",
  expireDays: 3650,
};

/**
 * https://stackoverflow.com/a/6274381
 * Shuffles array in place. ES6 version
 * @param {Array} a items An array containing the items.
 */
function shuffle(a) {
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

export const useSpieldoseStore = defineStore("spieldose", {
  state: () => ({
    data: {
      audio: null,
      audioMotionAnalyzerSource: null,
      fullScreenVisualizationSettings: null,
      player: {
        userInteracted: false,
        volume: 1,
        status: "stopped",
        muted: false,
        repeatMode: "none",
        shuffle: false,
        sideBarTopArt: {
          mode: "normal",
        },
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
          currentRadioStation: null,
        },
      ],
      currentElement: {
        track: null,
        radioStation: null,
      },
      currentPlaylist: {
        lastChangeTimestamp: null,
        totalTracks: 0,
        currentTrackIndex: -1,
        currentElement: {
          track: null,
          radioStation: null,
        },
      },
    },
  }),
  getters: {
    getAudioInstance: (state) => state.data.audio,
    hasPreviousUserInteractions: (state) => state.data.player.userInteracted,
    getAudioMotionAnalyzerSource: (state) =>
      state.data.audioMotionAnalyzerSource,
    getFullScreenVisualizationSettings: (state) =>
      state.data.fullScreenVisualizationSettings,
    isSidebarAudioMotionAnalyzerVisible: (state) =>
      state.data.player.sidebarAudioMotionAnalyzer.visible,
    getSidebarAudioMotionAnalyzerMode: (state) =>
      state.data.player.sidebarAudioMotionAnalyzer.mode,
    hasSidebarTopArtAnimationMode: (state) =>
      state.data.player.sideBarTopArt.mode == "animation",
    getPlayerStatus: (state) => state.data.player.status,
    isMuted: (state) => state.data.player.muted,
    isPlaying: (state) => state.data.player.status == "playing",
    isStopped: (state) => state.data.player.status == "stopped",
    isPaused: (state) => state.data.player.status == "paused",
    getVolume: (state) => state.data.player.volume,
    getDuration: (state) => (state.data.audio ? state.data.audio.duration : 0),
    getRepeatMode: (state) => state.data.player.repeatMode,
    getShuffle: (state) => state.data.player.shuffle,
    currentPlaylistElementCount: (state) =>
      state.data.currentPlaylist.totalTracks,
    hasCurrentPlaylistElements(state) {
      return this.currentPlaylistElementCount > 0;
    },
    getCurrentPlaylist: (state) => state.data.playlists[0],
    getCurrentPlaylistIndex: (state) =>
      state.data.currentPlaylist.currentTrackIndex,
    getShuffleCurrentPlaylistIndex: (state) =>
      state.data.currentPlaylist.currentTrackIndex,
    getCurrentPlaylistLastChangedTimestamp: (state) =>
      state.data.currentPlaylist.lastChangeTimestamp,
    isCurrentPlaylistElementATrack(state) {
      return state.data.currentPlaylist.currentElement.track != null;
    },
    hasCurrentPlaylistARadioStation: (state) =>
      state.data.currentPlaylist.currentElement.radioStation != null,
    getCurrentPlaylistElement(state) {
      return state.data.currentPlaylist.currentElement;
    },
    getCurrentPlaylistElementURL(state) {
      if (this.isCurrentPlaylistElementATrack) {
        return state.data.currentPlaylist.currentElement.track.url;
      } else if (this.hasCurrentPlaylistARadioStation) {
        // TODO
        return state.data.currentPlaylist.currentElement.radioStation
          .directStream;
      } else {
        return null;
      }
    },
    getCurrentPlaylistElementNormalImage(state) {
      if (this.isCurrentPlaylistElementATrack) {
        return state.data.currentPlaylist.currentElement.track.covers.normal;
      } else if (this.hasCurrentPlaylistARadioStation) {
        return state.data.currentPlaylist.currentElement.radioStation.images
          .normal;
      } else {
        return null;
      }
    },
    getCurrentPlaylistElementSmallImage(state) {
      if (this.isCurrentPlaylistElementATrack) {
        return state.data.currentPlaylist.currentElement.track.covers.small;
      } else if (this.hasCurrentPlaylistARadioStation) {
        return state.data.currentPlaylist.currentElement.radioStation.images
          .small;
      } else {
        return null;
      }
    },
    allowSkipPrevious: (state) =>
      state.data.currentPlaylist.totalTracks > 0 &&
      state.data.currentPlaylist.currentTrackIndex > 0,
    allowSkipNext: (state) =>
      state.data.currentPlaylist.totalTracks > 0 &&
      state.data.currentPlaylist.currentTrackIndex <
        state.data.currentPlaylist.totalTracks - 1,
  },
  actions: {
    create: function (src) {
      if (this.data.audio == null) {
        if (src !== undefined) {
          this.data.audio = new Audio(src);
        } else {
          this.data.audio = new Audio();
        }
        this.data.audio.autoplay = false;
        // required for radio stations streams
        this.data.audio.crossOrigin = "anonymous";
      } else {
        this.data.audio.src = null;
        // required for radio stations streams
        this.data.audio.crossOrigin = "anonymous";
      }
      this.restoreFullScreenVisualizationSettings();
      this.restorePlayerSettings(this.hasPreviousUserInteractions);
    },
    setAudioSource(src) {
      if (src !== undefined && src) {
        if (this.data.audio) {
          this.data.audio.src = src;
        }
        if (this.hasPreviousUserInteractions && !this.isPlaying) {
          this.play(true);
        }
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
    toggleSidebarTopArtAnimationMode: function () {
      if (this.data.player.sideBarTopArt.mode == "animation") {
        this.data.player.sideBarTopArt.mode = "normal";
      } else {
        this.data.player.sideBarTopArt.mode = "animation";
      }
      this.savePlayerSettings();
    },
    interact: function () {
      this.data.player.userInteracted = true;
    },
    savePlayerSettings: function () {
      const basil = useBasil(localStorageBasilOptions);
      basil.set("playerSettings", this.data.player);
    },
    restorePlayerSettings: function (userInteracted) {
      const basil = useBasil(localStorageBasilOptions);
      const playerSettings = basil.get("playerSettings");
      if (playerSettings) {
        this.data.player = playerSettings;
        this.data.player.status = "stopped";
        this.data.player.userInteracted =
          userInteracted !== undefined ? userInteracted == true : false;
        if (this.data.audio) {
          this.data.audio.volume = this.data.player.volume;
          this.data.audio.muted = this.data.player.muted;
        }
      } else {
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
      if (this.hasPreviousUserInteractions) {
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
      this.savePlayerSettings();
    },
    toggleShuffeMode: function () {
      this.data.player.shuffle = !this.data.player.shuffle;
      this.savePlayerSettings();
    },
    setPlaylistAsCurrent: function (playlist) {
      console.error("TODO");
      /*
      this.stop();
      this.data.playlists[0] = {
        id: playlist.id,
        name: playlist.name,
        owner: playlist.owner,
        public: playlist.public || false,
        lastChangeTimestamp: Date.now(),
        currentElementIndex: playlist.tracks.length > 0 ? 0 : -1,
        elements: playlist.tracks
          ? playlist.tracks.map((track) => {
              return { track: track };
            })
          : [],
        shuffleIndexes: playlist.tracks
          ? shuffle([...Array(playlist.tracks.length).keys()])
          : [],
        currentRadioStation: null,
      };
      this.data.currentPlaylistIndex = 0;
      this.saveCurrentPlaylist();
      if (this.hasCurrentPlaylistElements) {
        this.setAudioSource(this.getCurrentPlaylistElementURL);
        this.play(true);
      }
      */
    },
    setCurrentRadioStation: function (radioStation) {
      console.error("TODO");
      /*
      this.data.playlists[0].currentRadioStation = radioStation;
      this.saveCurrentPlaylist();
      this.setAudioSource(this.getCurrentPlaylistElementURL);
      this.play(true);
      */
    },
    sendElementsToCurrentPlaylist: function (elements) {
      console.error("TODO");
      /*
      this.interact();
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
            ? shuffle([...Array(elements.length).keys()])
            : [],
          currentRadioStation: null,
        };
        this.data.currentPlaylistIndex = 0;
        this.saveCurrentPlaylist();
        this.setAudioSource(this.getCurrentPlaylistElementURL);
        this.play(true);
      }
      */
    },
    appendElementsToCurrentPlaylist: function (elements) {
      console.error("TODO");
      /*
      this.interact();
      const hasPreviousElements = this.data.playlists[0].elements.length > 0;
      const hasValues =
        elements && Array.isArray(elements) && elements.length > 0;
      if (hasValues) {
        this.data.playlists[0].elements =
          this.data.playlists[0].elements.concat(elements);
        this.data.playlists[0].shuffleIndexes = shuffle([
          ...Array(this.data.playlists[0].elements.length).keys(),
        ]);
        this.data.playlists[0].currentRadioStation = null;
        if (!hasPreviousElements) {
          this.data.playlists[0].currentElementIndex = hasValues ? 0 : -1;
        }
        this.data.currentPlaylistIndex = 0;
        this.data.playlists[0].lastChangeTimestamp = Date.now();
        this.saveCurrentPlaylist();
        if (!this.isPlaying) {
          this.setAudioSource(this.getCurrentPlaylistElementURL);
          this.play(true);
        }
      }
      */
    },
    setCurrentPlaylist: function (
      currentTrackIndex,
      totalTracks,
      track,
      radioStation
    ) {
      const oldURL = this.getCurrentPlaylistElementURL;
      this.data.currentPlaylist.currentTrackIndex = currentTrackIndex;
      this.data.currentPlaylist.totalTracks = totalTracks;
      this.data.currentPlaylist.currentElement.track = track;
      this.data.currentPlaylist.currentElement.radioStation = radioStation;
      this.data.currentPlaylist.lastChangeTimestamp = Date.now();
      if (this.getCurrentPlaylistElementURL) {
        if (oldURL != this.getCurrentPlaylistElementURL)
          this.setAudioSource(this.getCurrentPlaylistElementURL);
        if (this.hasPreviousUserInteractions) {
          this.play(true);
        }
      } else if (!this.isStopped) {
        this.stop();
      }
    },
    saveCurrentPlaylist: function () {
      console.error("TODO");
      /*
      const basil = useBasil(localStorageBasilOptions);
      basil.set("currentPlaylist", this.data.playlists[0]);
      */
    },
    skipPrevious: function () {
      console.error("TODO");
    },
    skipNext: function () {
      console.error("TODO");
    },
    skipToIndex: function (index) {
      console.error("TODO");
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
    restoreFullScreenVisualizationSettings: function () {
      const basil = useBasil(localStorageBasilOptions);
      const fullScreenVisualizationSettings = basil.get(
        "fullScreenVisualizationSettings"
      );
      if (fullScreenVisualizationSettings) {
        try {
          this.data.fullScreenVisualizationSettings = JSON.parse(
            fullScreenVisualizationSettings
          );
        } catch (e) {
          // console.error("error");
        }
      }
    },
    saveFullScreenVisualizationSettings(settings) {
      this.data.fullScreenVisualizationSettings = settings;
      const basil = useBasil(localStorageBasilOptions);
      basil.set(
        "fullScreenVisualizationSettings",
        this.data.fullScreenVisualizationSettings
          ? JSON.stringify(this.data.fullScreenVisualizationSettings)
          : null
      );
    },
  },
});
