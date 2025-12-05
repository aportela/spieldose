import { skipToPreviousItem, skipToNextItem } from './playlistActions';
import { usePlayerStore } from 'src/stores/player';

const playerStore = usePlayerStore();

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

export { togglePlay, skipPrevious, skipNext };
