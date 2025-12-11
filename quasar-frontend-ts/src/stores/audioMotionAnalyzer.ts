import { defineStore, acceptHMRUpdate } from 'pinia';
import { useCurrentPlayListsStore } from './currentPlayLists';

const currentPlayListsStore = useCurrentPlayListsStore();

interface State {
  options: {
    instanced: boolean;
    connectSpeakers: boolean;
  };
}

export const useAudioMotionAnalyzerStore = defineStore('audioMotionAnalyzerStore', {
  state: (): State => ({
    options: {
      instanced: false,
      connectSpeakers: true,
    },
  }),
  getters: {
    audioInstance: () => currentPlayListsStore.audioInstance,
    hasOtherRuningInstances: (state: State) => state.options.instanced,
    connectSpeakers: (state: State) => state.options.connectSpeakers,
  },
  actions: {
    instance() {
      this.options.instanced = true;
      // disable connected Speakers for next instances
      this.options.connectSpeakers = false;
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useAudioMotionAnalyzerStore, import.meta.hot));
}
