import { defineStore, acceptHMRUpdate } from 'pinia';
import { usePlayerStore } from "./player";

const playerStore = usePlayerStore();

interface State {
  audioInstance: HTMLAudioElement | null,
  options: {
    instanced: boolean;
    connectSpeakers: boolean;
  };
};

export const useAudioMotionAnalyzerStore = defineStore('audioMotionAnalyzerStore', {
  state: (): State => ({
    audioInstance: null,
    options: {
      instanced: false,
      connectSpeakers: true,
    },
  }),
  getters: {
    audioInstance: () => playerStore.audioInstance,
    hasOtherRuningInstances: (state) => state.options.instanced,
    connectSpeakers: (state) => state.options.connectSpeakers,
  },
  actions: {
    setAudio(audio: HTMLAudioElement) {
      this.audioInstance = audio;
    },
    instance() {
      this.options.instanced = true;
      // disable connected Speakers for next instances
      this.options.connectSpeakers = false;
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useAudioMotionAnalyzerStore, import.meta.hot));
}
