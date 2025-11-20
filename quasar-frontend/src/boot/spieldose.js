import { boot } from "quasar/wrappers";
import { useSpieldoseStore } from "stores/spieldose";

const spieldoseStore = useSpieldoseStore();

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async (/* { app, router, ... } */) => {
  spieldoseStore.create();
});
