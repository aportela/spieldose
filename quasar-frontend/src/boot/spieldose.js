import { spieldosePlayer } from "boot/player";
import { useCurrentPlaylistStore } from "stores/currentPlaylist";
import { api } from "boot/axios";
import { spieldoseEvents } from "boot/events";

const currentPlaylist = useCurrentPlaylistStore();

const trackActions = {
  setFavorite: function (id) {
    return new Promise((resolve, reject) => {
      api.track
        .setFavorite(id)
        .then((success) => {
          spieldoseEvents.emit.track.setFavorite(id, success.data.favorited);
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
          spieldoseEvents.emit.track.unSetFavorite(id);
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  play: function (data) {
    spieldosePlayer.interact();
    if (!spieldosePlayer.isStopped()) {
      spieldosePlayer.actions.stop();
    }
    // save element on current playlist
    currentPlaylist.saveElements(
      Array.isArray(data) ? data : [{ track: data }]
    );
    // emit event to play ¿?
    // TODO: required here or by events on current track change ?
    spieldosePlayer.actions.play(true);
  },
  enqueue: function (data) {
    player.interact();
    currentPlaylist.appendElements(
      Array.isArray(data) ? data : [{ track: data }]
    );
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
  skipPrevious: function () {
    currentPlaylist.skipPrevious();
  },
  skipNext: function () {
    currentPlaylist.skipNext();
  },
};

export { trackActions, albumActions, playListActions };
