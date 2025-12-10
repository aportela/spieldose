import { defineBoot } from '#q-app/wrappers'
import { watch } from 'vue';
import { usePlayerStore } from 'src/stores/player';
//import { useCurrentPlaylistItemStore } from 'src/stores/currentPlaylistItem';
import { useCurrentPlayListsStore } from 'src/stores/currentPlayLists';

const playerStore = usePlayerStore();
//const currentPlaylistItemStore = useCurrentPlaylistItemStore();
const currentPlayListsStore = useCurrentPlayListsStore();

/*
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
*/

watch(
  () => currentPlayListsStore.currentFileId,
  (newValue: string | null) => {
    if (newValue) {
      playerStore.setAudioSource(
        "/api2/file/raw/" + newValue,
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
