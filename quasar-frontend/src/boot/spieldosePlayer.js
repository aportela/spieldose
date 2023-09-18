import { boot } from "quasar/wrappers";
import { useSpieldosePlayerStore } from "src/stores/spieldosePlayer";

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
  interact: function () {
    spieldosePlayerStore.data.userInteracted = true;
  },
  hasPreviousUserInteractions: function () {
    return spieldosePlayerStore.data.userInteracted;
  },
  getStatus: function () {
    return spieldosePlayerStore.data.playerStatus;
  },
  isMuted: function () {
    return spieldosePlayerStore.data.muted;
  },
  isPlaying: function () {
    return spieldosePlayerStore.data.playerStatus == "playing";
  },
  isStopped: function () {
    return spieldosePlayerStore.data.playerStatus == "stopped";
  },
  isPaused: function () {
    return spieldosePlayerStore.data.playerStatus == "paused";
  },
  getDuration: function () {
    return spieldosePlayerStore.data.audio
      ? spieldosePlayerStore.data.audio.duration
      : 0;
  },
  getRepeatMode: function () {
    return spieldosePlayerStore.data.repeatMode;
  },
  getShuffle: function () {
    return spieldosePlayerStore.data.shuffle;
  },
  actions: {
    setVolume: function (volume) {
      spieldosePlayerStore.data.audio.volume = volume;
    },
    toggleMute: function () {
      spieldosePlayerStore.data.muted = !spieldosePlayerStore.data.muted;
      spieldosePlayerStore.data.audio.spieldosePlayerStore.data.muted =
        spieldosePlayerStore.data.muted;
      // TODO: launch event
    },
    setCurrentTime: function (time) {
      spieldosePlayerStore.data.audio.currentTime = time;
    },
    play: function (ignoreStatus) {
      if (spieldosePlayerStore.data.userInteracted) {
        if (ignoreStatus) {
          spieldosePlayerStore.data.audio.play();
          spieldosePlayerStore.data.playerStatus = "playing";
        } else {
          if (spieldosePlayerStore.data.playerStatus == "playing") {
            spieldosePlayerStore.data.audio.pause();
            spieldosePlayerStore.data.playerStatus = "paused";
          } else if (spieldosePlayerStore.data.playerStatus == "paused") {
            spieldosePlayerStore.data.audio.play();
            spieldosePlayerStore.data.playerStatus = "playing";
          } else {
            // TODO: required ?
            //audio.load();
            spieldosePlayerStore.data.audio.play();
            spieldosePlayerStore.data.playerStatus = "playing";
          }
        }
      } else {
        console.error("play error: no previous user interactions");
      }
    },
    pause: function () {
      if (spieldosePlayerStore.data.playerStatus == "playing") {
        spieldosePlayerStore.data.audio.pause();
        spieldosePlayerStore.data.playerStatus = "paused";
      }
    },
    resume: function () {
      if (spieldosePlayerStore.data.playerStatus == "paused") {
        spieldosePlayerStore.data.audio.play();
        spieldosePlayerStore.data.playerStatus = "playing";
      }
    },
    stop: function () {
      if (spieldosePlayerStore.data.playerStatus == "playing") {
        spieldosePlayerStore.data.audio.pause();
        // TODO: check if works
        spieldosePlayerStore.data.audio.currentTime = 0;
        spieldosePlayerStore.data.playerStatus = "stopped";
      }
    },
    toggleRepeatMode() {
      switch (spieldosePlayerStore.data.repeatMode) {
        case "none":
          spieldosePlayerStore.data.repeatMode = "track";
          // TODO: launch event
          break;
        case "track":
          spieldosePlayerStore.data.repeatMode = "playlist";
          // TODO: launch event
          break;
        case "playlist":
          spieldosePlayerStore.data.repeatMode = "none";
          // TODO: launch event
          break;
      }
    },
    toggleShuffeMode() {
      spieldosePlayerStore.data.shuffle = !spieldosePlayerStore.data.shuffle;
      // TODO: launch event
    },
  },
};

/*
bus.on("currentPlayList.play", () => {
  spieldosePlayer.actions.play();
});

*/

export default boot(({ app }) => {
  // something to do
  app.config.globalProperties.$spieldosePlayer = spieldosePlayer;

  // for Composition API
  app.provide("spieldosePlayer", spieldosePlayer);
});

export { spieldosePlayer };
