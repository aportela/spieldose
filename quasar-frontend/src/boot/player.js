import { boot } from "quasar/wrappers";
import { usePlayerStore } from "stores/player";

const playerStore = usePlayerStore();

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async (/* { app, router, ... } */) => {
  playerStore.create();
});
