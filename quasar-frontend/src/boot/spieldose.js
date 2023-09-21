import { useSpieldoseStore } from "stores/spieldose";
import { api } from "boot/axios";
import { spieldoseEvents } from "boot/events";

const spieldoseStore = useSpieldoseStore();

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
    if (!spieldoseStore.isStopped) {
      spieldoseStore.stop();
    }
    spieldoseStore.sendElementsToCurrentPlaylist(
      Array.isArray(data) ? data : [{ track: data }]
    );
  },
  enqueue: function (data) {
    spieldoseStore.interact();
    spieldoseStore.appendElementsToCurrentPlaylist(
      Array.isArray(data) ? data : [{ track: data }]
    );
  },
};

const albumActions = {
  play: function (album) {
    return new Promise((resolve, reject) => {
      // TODO: use get album api
      api.track
        // TODO: add another filters
        .search({ albumMbId: album.mbId }, 1, 0, false, "trackNumber", "ASC")
        .then((success) => {
          spieldoseStore.sendElementsToCurrentPlaylist(
            success.data.data.items.map((item) => {
              return { track: item };
            })
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  enqueue: function (data) {
    return new Promise((resolve, reject) => {
      // TODO: use get album api
      api.track
        // TODO: add another filters
        .search({ albumMbId: album.mbId }, 1, 0, false, "trackNumber", "ASC")
        .then((success) => {
          spieldoseStore.interact();
          spieldoseStore.appendElementsToCurrentPlaylist(
            success.data.data.items.map((item) => {
              return { track: item };
            })
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
};

const playListActions = {
  loadPlaylist: function (id) {
    return new Promise((resolve, reject) => {
      spieldoseStore.interact();
      api.playlist
        .get(id)
        .then((success) => {
          spieldoseStore.setPlaylistAsCurrent(success.data.playlist);
          resolve();
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  saveElements: function (data) {
    spieldoseStore.sendElementsToCurrentPlaylist(
      Array.isArray(data) ? data : [{ track: data }]
    );
  },
  appendElements: function (data) {
    spieldoseStore.appendElementsToCurrentPlaylist(
      Array.isArray(data) ? data : [{ track: data }]
    );
  },
  setRadioStation: function (radioStation) {
    spieldoseStore.interact();
    spieldoseStore.setCurrentRadioStation(radioStation);
  },
};

export { trackActions, albumActions, playListActions };
