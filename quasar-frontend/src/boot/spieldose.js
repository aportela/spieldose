import { useSpieldoseStore } from "stores/spieldose";
import { api } from "boot/axios";
import { spieldoseEvents } from "boot/events";

const spieldoseStore = useSpieldoseStore();

const trackActions = {
  setFavorite: function (id, source) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.track
        .setFavorite(id)
        .then((success) => {
          spieldoseEvents.emit.track.setFavorite(
            id,
            success.data.favorited,
            source
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  unSetFavorite: function (id, source) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.track
        .unSetFavorite(id)
        .then((success) => {
          spieldoseEvents.emit.track.unSetFavorite(id, source);
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
  play: function (id) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .setTracks([id])
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  enqueue: function (id) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .appendTracks([id])
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
};

const albumActions = {
  play: function (mbId, title, artistMBId, artistName, year) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .setAlbum(mbId, title, artistMBId, artistName, year)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  enqueue: function (mbId, title, artistMBId, artistName, year) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .appendAlbum(mbId, title, artistMBId, artistName, year)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
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
  play: function (id) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .setPlaylist(id)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  enqueue: function (id) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .appendPlaylist(id)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
};

const radioStationActions = {
  play: function (id) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .setRadioStation(id)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
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
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .discover(count, spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  randomize: function () {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .randomize(spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  skipToPreviousElement: function () {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .skipToPreviousElement(spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  skipToNextElement: function () {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .skipToNextElement(spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  skipToElementIndex: function (index) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .skipToElementAtIndex(index)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  clear: function () {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .setTracks([])
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(-1, -1, 0, null, null, null);
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  restoreCurrentPlaylistElement: function () {
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .getCurrentElement(spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  resortByIndexes: function (indexes) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .resortByIndexes(indexes, spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
  removeElementAtIndex: function (index) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .removeElementAtIndex(index, spieldoseStore.getShuffle)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
};

const pathActions = {
  play: function (id) {
    spieldoseStore.interact();
    return new Promise((resolve, reject) => {
      api.currentPlaylist
        .setPath(id)
        .then((success) => {
          spieldoseStore.setCurrentPlaylist(
            success.data.currentTrackIndex,
            success.data.currentTrackShuffledIndex,
            success.data.totalTracks,
            success.data.currentTrack,
            success.data.radioStation,
            success.data.playlist
          );
          resolve(success);
        })
        .catch((error) => {
          reject(error);
        });
    });
  },
};

export {
  trackActions,
  albumActions,
  playListActions,
  radioStationActions,
  currentPlayListActions,
  pathActions,
};
