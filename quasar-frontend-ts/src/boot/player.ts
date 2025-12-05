import { defineBoot } from '#q-app/wrappers'
import { watch } from 'vue';
import { usePlayerStore } from 'src/stores/player';
import { useCurrentPlaylistItemStore } from 'src/stores/currentPlaylistItem';

const playerStore = usePlayerStore();
const currentPlaylistItemStore = useCurrentPlaylistItemStore();

watch(
  () => currentPlaylistItemStore.t,
  (newValue: number) => {
    console.debug(`Last change: ${newValue}`);
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


export default defineBoot((/* { app, router, ... } */) => {
  playerStore.create();
})
