import { boot } from "quasar/wrappers";
import { useSpieldosePlayerStore } from "stores/spieldosePlayer";

const spieldosePlayerStore = useSpieldosePlayerStore();

const spieldosePlayer = {
  create: function (src) {
    if (spieldosePlayerStore.data.audio === null) {
      if (src) {
        spieldosePlayerStore.data.audio = new Audio(src);
      } else {
        spieldosePlayerStore.data.audio = new Audio();
      }
    } else {
      spieldosePlayerStore.data.audio.src = null;
    }
    // required for radio stations streams
    spieldosePlayerStore.data.audio.crossOrigin = "anonymous";
    spieldosePlayerStore.restorePlayerSettings();
    spieldosePlayerStore.restoreCurrentPlaylist();
  },
  getAudioInstance: function () {
    return spieldosePlayerStore.data.audio;
  },
  setAudioMotionAnalyzerSource: function (src) {
    spieldosePlayerStore.data.audioMotionAnalyzerSource = src;
  },
  getAudioMotionAnalyzerSource: function () {
    return spieldosePlayerStore.data.audioMotionAnalyzerSource;
  },
  setSource: function (src) {
    if (spieldosePlayerStore.data.audio) {
      spieldosePlayerStore.data.audio.src = src;
    }
  },
  // TODO: move to actions ?
  interact: function () {
    spieldosePlayerStore.data.player.userInteracted = true;
  },
  hasPreviousUserInteractions: function () {
    return (spieldosePlayerStore.data.player.userInteracted == true);
  },
  getStatus: function () {
    return (spieldosePlayerStore.data.player.status);
  },
  isMuted: function () {
    return (spieldosePlayerStore.data.player.muted);
  },
  isPlaying: function () {
    return (spieldosePlayerStore.data.player.status == "playing");
  },
  isStopped: function () {
    return (spieldosePlayerStore.data.player.status == "stopped");
  },
  isPaused: function () {
    return spieldosePlayerStore.data.player.status == "paused";
  },
  getVolume: function () {
    return (spieldosePlayerStore.data.player.volume);
  },
  getDuration: function () {
    return spieldosePlayerStore.data.audio
      ? spieldosePlayerStore.data.audio.duration
      : 0;
  },
  getRepeatMode: function () {
    return (spieldosePlayerStore.data.player.repeatMode);
  },
  getShuffle: function () {
    return (spieldosePlayerStore.data.player.shuffle == true);
  },
  getPlaylists: function () {
    return spieldosePlayerStore.data.playlists.map((playlist) => {
      return {
        id: playlist.id,
        name: playlist.name,
        totalTracks: playlist.elements.length,
        public: playlist.public,
        owner: playlist.owner,
      };
    });
  },
  getCurrentPlaylist: function () {
    return (spieldosePlayerStore.getCurrentPlaylist());
  },
  getCurrentPlaylistElement: function () {
    return (spieldosePlayerStore.getCurrentPlaylistElement());
  },
  allowSkipPrevious: function () {
    return (spieldosePlayerStore.allowSkipPrevious);
  },
  allowSkipNext: function () {
    return (spieldosePlayerStore.allowSkipNext);
  },
  actions: {
    setVolume: function (volume) {
      spieldosePlayerStore.setVolume(volume);
    },
    toggleMute: function () {
      spieldosePlayerStore.toggleMute();
    },
    setCurrentTime: function (time) {
      spieldosePlayerStore.data.audio.currentTime = time;
    },
    play: function (ignoreStatus) {
      if (spieldosePlayerStore.data.player.userInteracted) {
        if (ignoreStatus) {
          spieldosePlayerStore.data.audio.play();
          spieldosePlayerStore.data.player.status = "playing";
        } else {
          if (spieldosePlayerStore.data.player.status == "playing") {
            spieldosePlayerStore.data.audio.pause();
            spieldosePlayerStore.data.player.status = "paused";
          } else if (spieldosePlayerStore.data.player.status == "paused") {
            spieldosePlayerStore.data.audio.play();
            spieldosePlayerStore.data.player.status = "playing";
          } else {
            // TODO: required ?
            //audio.load();
            spieldosePlayerStore.data.audio.play();
            spieldosePlayerStore.data.player.status = "playing";
          }
        }
      } else {
        console.error("play error: no previous user interactions");
      }
    },
    pause: function () {
      if (spieldosePlayerStore.data.player.status == "playing") {
        spieldosePlayerStore.data.audio.pause();
        spieldosePlayerStore.data.player.status = "paused";
      }
    },
    resume: function () {
      if (spieldosePlayerStore.data.player.status == "paused") {
        spieldosePlayerStore.data.audio.play();
        spieldosePlayerStore.data.player.status = "playing";
      }
    },
    stop: function () {
      if (spieldosePlayerStore.data.player.status == "playing") {
        spieldosePlayerStore.data.audio.pause();
        // TODO: check if works
      }
      spieldosePlayerStore.data.audio.currentTime = 0;
      spieldosePlayerStore.data.player.status = "stopped";
    },
    skipPrevious: function () {
      spieldosePlayerStore.skipPrevious();
    },
    skipNext: function () {
      spieldosePlayerStore.skipNext();
    },
    toggleRepeatMode() {
      switch (spieldosePlayerStore.data.player.repeatMode) {
        case "none":
          spieldosePlayerStore.data.player.repeatMode = "track";
          // TODO: launch event
          break;
        case "track":
          spieldosePlayerStore.data.player.repeatMode = "playlist";
          // TODO: launch event
          break;
        case "playlist":
          spieldosePlayerStore.data.player.repeatMode = "none";
          // TODO: launch event
          break;
      }
    },
    toggleShuffeMode() {
      spieldosePlayerStore.data.player.shuffle = !spieldosePlayerStore.data.player.shuffle;
      // TODO: launch event
    },
    setPlaylist: function (playlist) {
      if (!spieldosePlayerStore.data.player.status == "stopped") {
        this.stop();
      }
      spieldosePlayerStore.setPlaylistAsCurrent(playlist);
      this.play();
    },
    sendToPlayList(newElements) {
      if (!spieldosePlayerStore.data.player.status == "stopped") {
        this.stop();
      }
      spieldosePlayerStore.sendElementsToCurrentPlaylist(newElements);
      this.play();
    },
    appendToPlayList(newElements) {
      spieldosePlayerStore.appendElementsToCurrentPlaylist(newElements);
      if (!spieldosePlayerStore.data.player.status == "playing") {
        this.play();
      }
    },
  },
};

export default boot(({ app }) => {
  // something to do
  app.config.globalProperties.$spieldosePlayer = spieldosePlayer;

  // for Composition API
  app.provide("spieldosePlayer", spieldosePlayer);
});

export { spieldosePlayer };
