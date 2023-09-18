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
    },
  }),
});
