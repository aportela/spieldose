import { usePlayer } from "stores/player";
import { useCurrentPlaylistStore } from "stores/currentPlaylist";
//import { api } from "boot/axios";

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const trackActions = {
  play: function (data) {
    player.stop();
    currentPlaylist.saveElements(
      Array.isArray(data) ? data : [{ track: data }]
    );
    player.interact();
    player.play(false);
  },
  enqueue: function (data) {
    currentPlaylist.appendElements(
      Array.isArray(data) ? data : [{ track: data }]
    );
    player.interact();
  },
};

export { trackActions };
