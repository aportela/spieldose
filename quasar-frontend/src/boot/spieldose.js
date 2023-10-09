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
    api.currentPlaylist.setTracks([data.id]);
  },
  enqueue: function (data) {
    spieldoseStore.interact();
    spieldoseStore.appendElementsToCurrentPlaylist(
      Array.isArray(data) ? data : [{ track: data }]
    );
    api.currentPlaylist.appendTracks([data.id]);
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
          api.currentPlaylist.setTracks(
            success.data.data.items.map((item) => item.id)
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
          api.currentPlaylist.appendElements(
            success.data.data.items.map((item) => item.id)
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
    api.currentPlaylist.setTracks(
      success.data.data.items.map((data) => data.id)
    );
  },
  appendElements: function (data) {
    spieldoseStore.appendElementsToCurrentPlaylist(
      Array.isArray(data) ? data : [{ track: data }]
    );
    api.currentPlaylist.appendTracks(
      success.data.data.items.map((data) => data.id)
    );
  },
  setRadioStation: function (radioStation) {
    spieldoseStore.interact();
    spieldoseStore.setCurrentRadioStation(radioStation);
  },
};

const currentPlayListActions = {
  get: function () {
    return new Promise((resolve, reject) => {
      spieldoseStore.interact();
      api.currentPlaylist
        .get()
        .then((success) => {
          //TODO
          resolve();
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  getCurrentElement: function () {
    return new Promise((resolve, reject) => {
      spieldoseStore.interact();
      api.currentPlaylist
        .getCurrentElement()
        .then((success) => {
          spieldoseStore.setCurrentElement(success.data);
          resolve();
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  skipToPreviousElement: function () {
    return new Promise((resolve, reject) => {
      spieldoseStore.interact();
      api.currentPlaylist
        .skipToPreviousElement()
        .then((success) => {
          spieldoseStore.setCurrentElement(success.data);
          resolve();
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  skipToNextElement: function () {
    return new Promise((resolve, reject) => {
      spieldoseStore.interact();
      api.currentPlaylist
        .skipToNextElement()
        .then((success) => {
          spieldoseStore.setCurrentElement(success.data);
          resolve();
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  skipToElementAtIndex: function (index) {
    return new Promise((resolve, reject) => {
      spieldoseStore.interact();
      api.currentPlaylist
        .skipToElementAtIndex(index)
        .then((success) => {
          spieldoseStore.setCurrentElement(success.data);
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
    api.currentPlaylist.setTracks(
      Array.isArray(data) ? success.data.data.items.map((data) => data.id) : [data]
    );
  },
  appendElements: function (data) {
    spieldoseStore.appendElementsToCurrentPlaylist(
      Array.isArray(data) ? data : [{ track: data }]
    );
    api.currentPlaylist.appendTracks(
      Array.isArray(data) ? success.data.data.items.map((data) => data.id) : [data]
    );
  },
  setRadioStation: function (radioStation) {
    spieldoseStore.interact();
    spieldoseStore.setCurrentRadioStation(radioStation);
  },
};

export { trackActions, albumActions, playListActions };
