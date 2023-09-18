import { boot } from "quasar/wrappers";

let audio = null;
let userInteracted = false;
let playerStatus = "stopped";
let muted = false;
let repeatMode = "none";
let shuffle = false;
let audioMotionAnalyzerSource = null;

const spieldosePlayer = {
  create: function (src) {
    if (audio === null) {
      if (src) {
        audio = new Audio(src);
      } else {
        audio = new Audio();
      }
    } else {
      audio.src = null;
    }
    // required for radio stations streams
    audio.crossOrigin = "anonymous";
  },
  getAudioInstance: function () {
    return audio;
  },
  setAudioMotionAnalyzerSource: function (src) {
    audioMotionAnalyzerSource = src;
  },
  getAudioMotionAnalyzerSource: function () {
    return audioMotionAnalyzerSource;
  },
  setSource: function (src) {
    if (audio) {
      audio.src = src;
    }
  },
  interact: function () {
    userInteracted = true;
  },
  hasPreviousUserInteractions: function () {
    return userInteracted;
  },
  getStatus: function () {
    return playerStatus;
  },
  isMuted: function () {
    return muted;
  },
  isPlaying: function () {
    return playerStatus == "playing";
  },
  isStopped: function () {
    return playerStatus == "stopped";
  },
  isPaused: function () {
    return playerStatus == "paused";
  },
  getDuration: function () {
    return audio ? audio.duration : 0;
  },
  getRepeatMode: function () {
    return repeatMode;
  },
  getShuffle: function () {
    return shuffle;
  },
  actions: {
    setVolume: function (volume) {
      audio.volume = volume;
    },
    toggleMute: function () {
      muted = !muted;
      audio.muted = muted;
      // TODO: launch event
    },
    setCurrentTime: function (time) {
      audio.currentTime = time;
    },
    play: function (ignoreStatus) {
      if (userInteracted) {
        if (ignoreStatus) {
          audio.play();
          playerStatus = "playing";
        } else {
          if (playerStatus == "playing") {
            audio.pause();
            playerStatus = "paused";
          } else if (playerStatus == "paused") {
            audio.play();
            playerStatus = "playing";
          } else {
            // TODO: required ?
            //audio.load();
            audio.play();
            playerStatus = "playing";
          }
        }
      } else {
        console.error("play error: no previous user interactions");
      }
    },
    pause: function () {
      if (playerStatus == "playing") {
        audio.pause();
        playerStatus = "paused";
      }
    },
    resume: function () {
      if (playerStatus == "paused") {
        audio.play();
        playerStatus = "playing";
      }
    },
    stop: function () {
      if (playerStatus == "playing") {
        audio.pause();
        // TODO: check if works
        audio.currentTime = 0;
        playerStatus = "stopped";
      }
    },
    toggleRepeatMode() {
      switch (repeatMode) {
        case "none":
          repeatMode = "track";
          // TODO: launch event
          break;
        case "track":
          repeatMode = "playlist";
          // TODO: launch event
          break;
        case "playlist":
          repeatMode = "none";
          // TODO: launch event
          break;
      }
    },
    toggleShuffeMode() {
      shuffle = !shuffle;
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
