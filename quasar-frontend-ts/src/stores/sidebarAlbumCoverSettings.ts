import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from 'src/composables/localStorage';

type AlbumCoverMode = "none" | "staticImage" | "vinyl" | "cassetteTape";

const localStorageVisible = createStorageEntry<boolean>("visualizations.sidebar.albumCover.visible", true);
const localStorageMode = createStorageEntry<AlbumCoverMode>("visualizations.sidebar.albumCover.mode", "staticImage");
const localStoragePlaybackAnimation = createStorageEntry<boolean>("visualizations.sidebar.albumCover.playbackAnimation", true);

interface State {
  settings: {
    visible: boolean;
    mode: AlbumCoverMode;
    playBackAnimation: boolean;
  }
};

export const useSidebarAlbumCoverSettingsStore = defineStore('sidebarAlbumCoverSettingsStore', {
  state: (): State => ({
    settings: {
      visible: localStorageVisible.get(),
      mode: localStorageMode.get(),
      playBackAnimation: localStoragePlaybackAnimation.get(),
    },
  }),
  getters: {
    hasNoImage: (state): boolean => state.settings.mode === "none",
    hasStaticImageMode: (state): boolean => state.settings.mode === "staticImage",
    hasVinilMode: (state): boolean => state.settings.mode === "vinyl",
    hasCassetteTapeMode: (state): boolean => state.settings.mode === "cassetteTape",
    hasPlayBackAnimation: (state): boolean => state.settings.playBackAnimation,
  },
  actions: {
    setVisible(visible: boolean) {
      this.settings.visible = visible;
      localStorageVisible.set(this.settings.visible);
    },
    toggleMode() {
      switch (this.settings.mode) {
        case "none":
          this.settings.mode = "staticImage";
          break;
        case "staticImage":
          this.settings.mode = "vinyl";
          break;
        case "vinyl":
          this.settings.mode = "cassetteTape";
          break;
        case "cassetteTape":
          this.settings.mode = "none";
          break;
      }
      localStorageMode.set(this.settings.mode);
    },
    togglePlaybackAnimation() {
      this.settings.playBackAnimation = !this.settings.playBackAnimation;
      localStoragePlaybackAnimation.set(this.settings.playBackAnimation);
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSidebarAlbumCoverSettingsStore, import.meta.hot));
}
