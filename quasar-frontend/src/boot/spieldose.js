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
      api.currentPlaylist
        .setAlbum(album.mbId)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation
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
      api.currentPlaylist
        .appendAlbum(album.mbId)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation
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
      api.currentPlaylist
        .get()
        .then((success) => {
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  discover: function (count) {
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .discover(count, spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  skipToPreviousElement: function () {
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .skipToPreviousElement(spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  skipToNextElement: function () {
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .skipToNextElement(spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  skipToElementIndex: function (index) {
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .skipToElementAtIndex(index, spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  saveElements: function (data) {
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .setTracks(
          Array.isArray(data)
            ? success.data.data.items.map((data) => data.id)
            : [data]
        )
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  appendElements: function (data) {
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .appendTracks(
          Array.isArray(data)
            ? success.data.data.items.map((data) => data.id)
            : [data]
        )
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  setRadioStation: function (radioStation) {
    spieldoseStore.interact();
    spieldoseStore.setCurrentRadioStation(radioStation);
  },
  clear: function () {
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .setTracks([])
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(-1, 0, null, null);
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
};

export { trackActions, albumActions, playListActions, currentPlayListActions };
