import { defineStore, acceptHMRUpdate } from 'pinia';
import { playerVinylAnimation as localStoragePlayerVinylAnimation } from 'src/composables/localStorage';
import { type VinylAnimation, type PlayerStatus } from 'src/types/common';
import { playerVolume as localStoragePlayerVolume, playerMuted as localStoragePlayerMuted } from 'src/composables/localStorage';
import { skipToNextItem } from 'src/composables/playlistActions';

interface Player {
  userInteracted: boolean;
  volume: number;
  status: PlayerStatus;
  muted: boolean;
  repeatMode: string;
  shuffle: boolean;
  sideBarTopArt: {
    mode: string;
  };
};

interface State {
  data: {
    audio: HTMLAudioElement;
    audioCurrentTime: number;
    audioDuration: number;
    player: Player;
    vinylAnimation: VinylAnimation;
  }
};

export const usePlayerStore = defineStore('playerStore', {
  state: (): State => ({
    data: {
      audio: new Audio(),
      audioCurrentTime: 0,
      audioDuration: 0,
      //fullScreenVisualizationSettings: null,
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
        /*
        sidebarAudioMotionAnalyzer: {
          visible: true,
          mode: 7,
        },
        */
      },
      vinylAnimation: localStoragePlayerVinylAnimation.get(),
      /*
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
      currentPlaylist: {
        lastChangeTimestamp: null,
        totalTracks: 0,
        currentTrackIndex: -1,
        currentTrackShuffledIndex: -1,
        currentElement: {
          track: null,
          radioStation: null,
        },
        playlist: null,
      },
      */
    },
  }),
  getters: {
    audioInstance: (state) => state.data.audio,
    audioCurrentTime: (state) =>
      state.data.audio !== null ? state.data.audioCurrentTime : 0,
    audioDuration: (state) =>
      state.data.audio !== null ? state.data.audioDuration : 0,
    hasPreviousUserInteractions: (state) => state.data.player.userInteracted,
    sidebarTopArtAnimated: (state) =>
      state.data.player.sideBarTopArt.mode == "animation",
    status: (state) => state.data.player.status,
    isMuted: (state) => state.data.player.muted,
    isPlaying: (state) => state.data.player.status == "playing",
    isStopped: (state) => state.data.player.status == "stopped",
    isPaused: (state) => state.data.player.status == "paused",
    volume: (state) => state.data.player.volume,
    duration: (state) => (state.data.audio ? state.data.audio.duration : 0),
    currentVinylAnimation: (state) => state.data.vinylAnimation,
    shuffleMode: (state) => state.data.player.shuffle,
    repeatMode: (state) => state.data.player.repeatMode,
    /*
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
    getCurrentPlaylistShuffledIndex: (state) =>
      state.data.currentPlaylist.currentTrackShuffledIndex,
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
    getCurrentPlaylistTrackId(state) {
      if (this.isCurrentPlaylistElementATrack) {
        return state.data.currentPlaylist.currentElement.track.id;
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
    getCurrentPlaylistLinkedPlaylist(state) {
      return state.data.currentPlaylist.playlist;
    },
    */
    allowSkipPrevious: () => true,
    /*
      state.data.currentPlaylist.totalTracks > 0 &&
      state.data.currentPlaylist.currentTrackIndex > 0
      */ allowSkipNext: () =>
      /*
      state.data.currentPlaylist.totalTracks > 0 &&
      state.data.currentPlaylist.currentTrackIndex <
      state.data.currentPlaylist.totalTracks - 1,
      */
      true,
  },
  actions: {
    create: function (src?: string | null) {
      this.data.audio.autoplay = false;
      if (src) {
        this.data.audio.src = src;
      }
      this.setVolume(localStoragePlayerVolume.get());
      this.setMute(localStoragePlayerMuted.get());
      // required for radio stations streams
      this.data.audio.crossOrigin = "anonymous";
      //this.restoreFullScreenVisualizationSettings();
      //this.restorePlayerSettings(this.hasPreviousUserInteractions);
      this.data.audio.addEventListener("ended", () => {
        this.onAudioEndEvent();
      });
      this.data.audio.addEventListener("error", (event) => {
        this.onAudioErrorEvent(event);
      });
      this.data.audio.addEventListener("timeupdate", () => {
        this.data.audioCurrentTime = !isNaN(this.data.audio.currentTime)
          ? this.data.audio.currentTime
          : 0;
        this.data.audioDuration = !isNaN(this.data.audio.duration)
          ? this.data.audio.duration
          : 0;
      });
    },
    destroy: function () {
      this.data.audio.removeEventListener("ended", () => { });
      this.data.audio.removeEventListener("error", () => { });
      this.data.audio.removeEventListener("timeupdate", () => { });
    },
    setAudioSource(src: string) {
      if (this.data.audio) {
        this.data.audio.src = src;
      }
      if (this.hasPreviousUserInteractions && !this.isPlaying) {
        this.play(true);
      }
    },
    toggleSidebarAudioMotionAnalyzer: function () {
      /*
      this.data.player.sidebarAudioMotionAnalyzer.visible =
        !this.data.player.sidebarAudioMotionAnalyzer.visible;
      this.savePlayerSettings();
      */
    },
    setSidebarAudioMotionAnalyzerMode: function () {
      /*
      this.data.player.sidebarAudioMotionAnalyzer.mode = mode;
      this.savePlayerSettings();
      */
    },
    toggleSidebarTopArtAnimationMode: function () {
      if (this.data.player.sideBarTopArt.mode == "animation") {
        this.data.player.sideBarTopArt.mode = "normal";
      } else {
        this.data.player.sideBarTopArt.mode = "animation";
      }
      //this.savePlayerSettings();
    },
    interact: function () {
      this.data.player.userInteracted = true;
    },
    /*
    savePlayerSettings: function () {
      // TODO: BASIL
      //const basil = useBasil(localStorageBasilOptions);
      //basil.set("playerSettings", this.data.player);
    },
    restorePlayerSettings: function (userInteracted) {
      // TODO: BASIL
    },
    */
    setVolume: function (volume: number) {
      if (volume >= 0 && volume <= 1) {
        this.data.player.volume = volume;
        if (this.data.audio) {
          this.data.audio.volume = volume;
        }
        localStoragePlayerVolume.set(volume);
      }
    },
    setMute: function (isMuted: boolean) {
      this.data.player.muted = isMuted;
      if (this.data.audio) {
        this.data.audio.muted = this.data.player.muted;
      }
    },
    toggleMute: function () {
      this.data.player.muted = !this.data.player.muted;
      if (this.data.audio) {
        this.data.audio.muted = this.data.player.muted;
      }
      localStoragePlayerMuted.set(this.data.player.muted);
    },
    seek: function (time: number | null) {
      if (this.data.audio && time !== null && time > 0 && time <= this.audioDuration) {
        this.data.audio.currentTime = time;
      }
    },
    play: function (ignoreStatus: boolean) {
      if (this.hasPreviousUserInteractions) {
        if (ignoreStatus) {
          if (this.data.audio) {
            this.data.audio
              .play()
              .then(() => (this.data.player.status = "playing"))
              .catch((error: Error) => {
                console.error(error);
                // TODO: show error ?
              });
          }
        } else {
          if (this.isPlaying) {
            if (this.data.audio) {
              this.data.audio.pause();
            }
            this.data.player.status = "paused";
          } else if (this.isPaused) {
            if (this.data.audio) {
              this.data.audio
                .play()
                .then(() => (this.data.player.status = "playing"))
                .catch((error: Error) => {
                  console.error(error);
                  // TODO: show error ?
                });
            }
            this.data.player.status = "playing";
          } else {
            if (this.data.audio) {
              this.data.audio
                .play()
                .then(() => (this.data.player.status = "playing"))
                .catch((error: Error) => {
                  console.error(error);
                  // TODO: show error ?
                });
            }
            this.data.player.status = "playing";
          }
        }
      } else {
        console.error("play error: no previous user interactions");
      }
    },
    pause: function () {
      this.interact();
      if (this.isPlaying) {
        if (this.data.audio) {
          this.data.audio.pause();
        }
        this.data.player.status = "paused";
      }
    },
    resume: function () {
      this.interact();
      if (this.isPaused) {
        if (this.data.audio) {
          this.data.audio
            .play()
            .then(() => (this.data.player.status = "playing"))
            .catch((error: Error) => {
              console.error(error);
              // TODO: show error ?
            });
        }
        this.data.player.status = "playing";
      }
    },
    stop: function () {
      this.interact();
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
      // TODO
      //this.savePlayerSettings();
    },
    toggleShuffeMode: function () {
      /*
      if (this.data.player.shuffle) {
        this.data.currentPlaylist.currentTrackIndex =
          this.data.currentPlaylist.currentTrackShuffledIndex;
      } else {
        this.data.currentPlaylist.currentTrackShuffledIndex =
          this.data.currentPlaylist.currentTrackIndex;
      }
          */
      this.data.player.shuffle = !this.data.player.shuffle;
      // TODO
      //this.savePlayerSettings();
    },
    /*
    toggleFavoriteOnCurrentTrack: function (timestamp) {
      if (this.isCurrentPlaylistElementATrack) {
        this.data.currentPlaylist.currentElement.track.favorited = timestamp;
      }
    },
    setCurrentPlaylist: function (
      currentTrackIndex,
      currentTrackShuffledIndex,
      totalTracks,
      track,
      radioStation,
      playlist,
    ) {
      const oldURL = this.getCurrentPlaylistElementURL;
      this.data.currentPlaylist.currentTrackIndex = currentTrackIndex;
      this.data.currentPlaylist.currentTrackShuffledIndex =
        currentTrackShuffledIndex;
      this.data.currentPlaylist.totalTracks = totalTracks;
      this.data.currentPlaylist.currentElement.track = track;
      this.data.currentPlaylist.currentElement.radioStation = radioStation;
      this.data.currentPlaylist.playlist = playlist;
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
    restoreFullScreenVisualizationSettings: function () {
      // TODO: BASIL
    },
    saveFullScreenVisualizationSettings(settings) {
      this.data.fullScreenVisualizationSettings = settings;
      // TODO: BASIL
    },
    */
    setCurrentVinylAnimation(animation: VinylAnimation) {
      this.data.vinylAnimation = animation;
      localStoragePlayerVinylAnimation.set(this.data.vinylAnimation);
    },
    onAudioEndEvent: function () {
      this.stop();
      skipToNextItem();
    },
    onAudioErrorEvent: function (event: Event) {
      console.error("Audio loading error", event);
    },
    onAudioTimeUpdateEvent: function () {
      console.log(this.audioInstance.currentTime, this.audioInstance.duration);
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(usePlayerStore, import.meta.hot));
}
