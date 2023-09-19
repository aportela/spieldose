import { spieldosePlayer } from "boot/spieldosePlayer";
import { useCurrentPlaylistStore } from "stores/currentPlaylist";
import { useSpieldosePlayerStore } from "stores/spieldosePlayer";
import { api } from "boot/axios";
import { spieldoseEvents } from "boot/events";

const currentPlaylist = useCurrentPlaylistStore();
const spieldosePlayerStore = useSpieldosePlayerStore();

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
  increasePlayCount: function (id) {
    return new Promise((resolve, reject) => {
      api.track
        .increasePlayCount(id)
        .then((success) => {
          spieldoseEvents.emit.track.increasePlayCount(id);
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
  loadPlaylist: function (id) {
    return new Promise((resolve, reject) => {
      spieldosePlayer.interact();
      api.playlist
        .get(id)
        .then((success) => {
          spieldosePlayer.actions.setPlaylist(success.data.playlist);
          resolve();
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  saveElements: function (newElements) {
    currentPlaylist.saveElements(newElements);
  },
  appendElements: function (newElements) {
    currentPlaylist.appendElements(newElements);
  },
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

const playerActions = {
  setVolume: function (volume) {

  }
};

export { playerActions, trackActions, albumActions, playListActions };
