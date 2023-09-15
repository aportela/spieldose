import { boot } from "quasar/wrappers";
import { bus } from "boot/bus";

const spieldoseEventNames = {
  player: {
    setVolume: "player.setVolume",
    toggleMute: "player.toggleMute",
    setCurrentTime: "player.setCurrentTime",
    play: "player.play",
    pause: "player.pause",
    resume: "player.resume",
    stop: "player.stop",
    toggleRepeatMode: "player.toggleRepeatMode",
    toggleShuffeMode: "player.toggleShuffeMode",
  },
};
const spieldoseEvents = {
  emit: {
    player: {
      setVolume: function (volume) {
        bus.emit(spieldoseEventNames.player.setVolume, { volume: volume });
      },
      toggleMute: function () {
        bus.emit(spieldoseEventNames.player.toggleMute, {});
      },
      setCurrentTime: function (second) {
        bus.emit(spieldoseEventNames.player.setCurrentTime, { second: second });
      },
      play: function (ignoreStatus) {
        bus.emit(spieldoseEventNames.player.play, {
          ignoreStatus: ignoreStatus || false,
        });
      },
      pause: function () {
        bus.emit(spieldoseEventNames.player.pause, {});
      },
      resume: function () {
        bus.emit(spieldoseEventNames.player.resume, {});
      },
      stop: function () {
        bus.emit(spieldoseEventNames.player.stop, {});
      },
      toggleRepeatMode: function () {
        bus.emit(spieldoseEventNames.player.toggleRepeatMode, {});
      },
      toggleShuffeMode: function () {
        bus.emit(spieldoseEventNames.player.toggleShuffeMode, {});
      },
    },
  },
};

export default boot(({ app }) => {
  // something to do
  app.config.globalProperties.$spieldoseEvents = spieldoseEvents;

  // for Composition API
  app.provide("spieldoseEvents", spieldoseEvents);
});

export { spieldoseEvents, spieldoseEventNames };
