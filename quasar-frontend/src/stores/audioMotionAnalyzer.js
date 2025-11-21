import { defineStore } from "pinia";

import { usePlayerStore } from "./player";
const playerStore = usePlayerStore();

export const useAudioMotionAnalyzerStore = defineStore("audioMotionAnalyzer", {
  state: () => ({
    audioInstance: null,
    options: {
      instanced: false,
      connectSpeakers: true,
    },
  }),
  getters: {
    audioInstance: (state) => playerStore.audioInstance,
    hasOtherRuningInstances: (state) => state.options.instanced,
    connectSpeakers: (state) => state.options.connectSpeakers,
  },
  actions: {
    setAudio(audio) {
      this.audioInstance = audio;
    },
    instance() {
      this.options.instanced = true;
      // disable connected Speakers for next instances
      this.options.connectSpeakers = false;
    },
  },
});
