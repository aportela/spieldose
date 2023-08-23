import { defineStore } from "pinia";
import { usePlayerStatusStore } from "stores/playerStatus";

const playerStatus = usePlayerStatusStore();

export const usePlayer = defineStore("player", {
  state: () => ({
    element: null,
    hasPreviousUserInteractions: false,
  }),
  getters: {
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
  },
});
