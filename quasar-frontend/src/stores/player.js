import { defineStore } from "pinia";

export const usePlayer = defineStore("player", {
  state: () => ({
    element: null,
    hasPreviousUserInteractions: false,
  }),
  getters: {
    getElement: (state) => state.element,
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
    stop() {
      this.element.pause();
    },
  },
});
