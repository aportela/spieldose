import { defineBoot } from '#q-app/wrappers';
import { watch } from 'vue';
import { useCurrentPlayListsStore } from 'src/stores/currentPlayLists';

const currentPlayListsStore = useCurrentPlayListsStore();

watch(
  () => currentPlayListsStore.currentFileId,
  (newValue: string | null) => {
    if (newValue) {
      currentPlayListsStore.setAudioSource('/api2/file/raw/' + newValue);
      if (currentPlayListsStore.playerHasPreviousUserInteractions) {
        currentPlayListsStore.playerActionPlay(true);
      }
    }
  },
);

export default defineBoot(() => {
  currentPlayListsStore.create();
});
