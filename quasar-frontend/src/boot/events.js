import { boot } from "quasar/wrappers";
import { bus } from "boot/bus";

const spieldoseEvents = {
  emit: {
    player: {

    },
    currentPlaylist: {
      play: function () {
        bus.emit('currentPlayList.play');
      },
      pause: function () {
        bus.emit('currentPlayList.pause');
      },
      stop: function () {
        bus.emit('currentPlayList.stop');
      },
      skipToPreviousTrack: function () {
        bus.emit("currentPlaylist.skipToPreviousTrack", { oldIndex: 0, currentIndex: 0 });
      },
      skipToNextTrack: function () {
        bus.emit("currentPlaylist.skipToNextTrack", { oldIndex: 0, currentIndex: 0 });
      }
    }
  }
};

export default boot(({ app }) => {
  // something to do
  app.config.globalProperties.$spieldoseEvents = spieldoseEvents;

  // for Composition API
  app.provide("spieldoseEvents", spieldoseEvents);
})

export { spieldoseEvents };
