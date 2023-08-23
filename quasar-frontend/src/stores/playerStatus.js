import { defineStore } from "pinia";

export const usePlayerStatusStore = defineStore("playerStatus", {
  state: () => ({
    status: "stopped",
  }),

  getters: {
    getStatus(state) {
      return state.status;
    },
    isPlaying(state) {
      return state.status == "playing";
    },
    isStopped(state) {
      return state.status == "stopped";
    },
    isPaused(state) {
      return state.status == "paused";
    },
  },
  actions: {
    setStatusPlaying() {
      this.status = "playing";
    },
    setStatusStopped() {
      this.status = "stopped";
    },
    setStatusPaused() {
      this.status = "paused";
    },
  },
});
