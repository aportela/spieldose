import { usePlayer } from "stores/player";
import { useCurrentPlaylistStore } from "stores/currentPlaylist";
//import { api } from "boot/axios";

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const trackActions = {
  play: function (track) {
    player.stop();
    currentPlaylist.saveElements([{ track: track }]);
    player.interact();
    player.play(false);
  },
  enqueue: function (track) {
    currentPlaylist.appendElements([{ track: track }]);
    player.interact();
  },
};

export { trackActions };
