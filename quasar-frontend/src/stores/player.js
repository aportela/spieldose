import { defineStore } from "pinia";

export const usePlayer = defineStore("player", {
  state: () => ({
    element: null,
  }),
  getters: {
    getElement: (state) => state.element,
  },
  actions: {
    create() {
      this.element = document.createElement("audio");
      this.element.id = "audio_player";
    },
    setVolume(volume) {
      this.element.volume = volume;
    },
  },
});
