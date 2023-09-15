import { boot } from "quasar/wrappers";
import { bus } from "boot/bus";

const spieldosePlayer = {
  create: function () {
    console.log("created");
  },
  actions: {
    play: function () {
      console.log("player::actions::play")
    },
    pause: function () {
      console.log("player::actions::pause")
    },
    stop: function () {
      console.log("player::actions::stop")
    }
  }
};


bus.on("currentPlayList.play", () => {
  spieldosePlayer.actions.play();
});

export default boot(({ app }) => {
  // something to do
  app.config.globalProperties.$spieldosePlayer = spieldosePlayer;

  // for Composition API
  app.provide("spieldosePlayer", spieldosePlayer);
})

export { spieldosePlayer };
