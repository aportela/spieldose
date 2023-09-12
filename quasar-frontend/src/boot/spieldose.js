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
    player.play(true);
  },
  enqueue: function (data) {
    currentPlaylist.appendElements(
      Array.isArray(data) ? data : [{ track: data }]
    );
    player.interact();
  },
};

const albumActions = {
  play: function (data) {
    player.stop();
    currentPlaylist.saveElements(
      Array.isArray(data) ? data : [{ track: data }]
    );
    player.interact();
    player.play(true);
  },
  enqueue: function (data) {
    currentPlaylist.appendElements(
      Array.isArray(data) ? data : [{ track: data }]
    );
    player.interact();
  },
};

const playListActions = {
  clear: function () {
    currentPlaylist.clear();
  },
};

export { trackActions, albumActions, playListActions };
