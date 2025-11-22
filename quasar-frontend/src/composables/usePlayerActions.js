import { usePlaylistActions } from "./usePlaylistActions";
import { usePlayerStore } from 'src/stores/player';

const playerStore = usePlayerStore();

const { skipToPreviousItem, skipToNextItem } = usePlaylistActions();

export function usePlayerActions() {

  const togglePlay = () => {
    playerStore.interact();
    playerStore.play();
  };

  const skipPrevious = () => {
    playerStore.interact();
    skipToPreviousItem();
  };

  const skipNext = () => {
    playerStore.interact();
    skipToNextItem();
  };

  return { togglePlay, skipPrevious, skipNext };
}
