import { usePlayer } from "stores/player";
import { useCurrentPlaylistStore } from "stores/currentPlaylist";
import { api } from "boot/axios";
import { bus } from "boot/bus";

const player = usePlayer();
const currentPlaylist = useCurrentPlaylistStore();

const trackActions = {
  setFavorite: function (id) {
    return new Promise((resolve, reject) => {
      api.track
        .setFavorite(id)
        .then((success) => {
          bus.emit('setFavoriteTrack', { trackId: id, timestamp: success.data.favorited });
          currentPlaylist.setFavoriteTrack(id, success.data.favorited);
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  unSetFavorite: function (id) {
    return new Promise((resolve, reject) => {
      api.track
        .unSetFavorite(id)
        .then((success) => {
          bus.emit('unSetFavoriteTrack', { trackId: id });
          currentPlaylist.unSetFavoriteTrack(id);
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
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
