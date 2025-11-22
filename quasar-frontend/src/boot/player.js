import { boot } from "quasar/wrappers";
import { usePlayerStore } from "stores/player";
import { useCurrentPlaylistItemStore } from "src/stores/currentPlaylistItem";

import { usePlaylistActions } from "src/composables/usePlaylistActions";

import { watch } from "vue";

const playerStore = usePlayerStore();
const currentPlaylistItemStore = useCurrentPlaylistItemStore();

const { randomTrack } = usePlaylistActions();

watch(
  () => currentPlaylistItemStore.t,
  (newValue) => {
    if (currentPlaylistItemStore.isTrack) {
      playerStore.setAudioSource(
        "/api2/file/raw/" + currentPlaylistItemStore.trackFileId,
      );
      if (playerStore.hasPreviousUserInteractions) {
        playerStore.play(true);
      }
    }
  },
);

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async (/* { app, router, ... } */) => {
  playerStore.create();
  randomTrack();
});
