import { defineStore } from "pinia";
import { usePlayerStatusStore } from "stores/playerStatus";

const playerStatus = usePlayerStatusStore();

export const usePlayer = defineStore("player", {
  state: () => ({
    repeatMode: null,
    shuffle: false,
    element: null,
    hasPreviousUserInteractions: false,
    originalAudioMotionAnalyzerSource: null,
  }),
  getters: {
    getRepeatMode: (state) => state.repeatMode,
    getShuffle: (state) => state.shuffle,
    getElement: (state) => state.element,
    getDuration: (state) => state.element.duration,
  },
  actions: {
    interact() {
      this.hasPreviousUserInteractions = true;
    },
    create() {
      this.element = document.createElement("audio");
      this.element.id = "audio_player";
      // required for radio stations streams
      this.element.crossOrigin = "anonymous";
    },
    setVolume(volume) {
      this.element.volume = volume;
    },
    setCurrentTime(time) {
      this.element.currentTime = time;
    },
    stop() {
      this.element.pause();
      playerStatus.setStatusStopped();
    },
    load() {
      // TODO:
    },
    play(ignoreStatus) {
      if (ignoreStatus) {
        this.element.play();
        playerStatus.setStatusPlaying();
      } else {
        if (playerStatus.isPlaying) {
          this.element.pause();
          playerStatus.setStatusPaused();
        } else if (playerStatus.isPaused) {
          this.element.play();
          playerStatus.setStatusPlaying();
        } else {
          this.element.load();
          this.element.play();
          playerStatus.setStatusPlaying();
        }
      }
    },
    setRepeatMode(mode) {
      this.repeatMode = mode;
    },
    toggleShuffle() {
      this.shuffle = !this.shuffle;
    },
    setAudioMotionAnalyzerSource: function (source) {
      this.originalAudioMotionAnalyzerSource = source;
    },
    getAudioMotionAnalyzerSource: function () {
      return this.originalAudioMotionAnalyzerSource;
    },
  },
});
