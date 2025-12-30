import { defineBoot } from '#q-app/wrappers';
import { watch } from 'vue';
import { useCurrentPlayListsStore } from 'src/stores/currentPlayLists';

const currentPlayListsStore = useCurrentPlayListsStore();

watch(
  () => currentPlayListsStore.currentRAWFileURL,
  (newValue: string | null) => {
    if (newValue) {
      currentPlayListsStore.setAudioSource(newValue);
      if (currentPlayListsStore.playerHasPreviousUserInteractions) {
        currentPlayListsStore.playerActionPlay(true);
      }
    }
  },
);

export default defineBoot(() => {
  currentPlayListsStore.create();
});
