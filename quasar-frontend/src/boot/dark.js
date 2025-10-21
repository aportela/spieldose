import { boot } from "quasar/wrappers";

import { useDarkMode } from "src/composables/useDarkMode";

const { initDarkMode } = useDarkMode();

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async (/* { app, router, ... } */) => {
  initDarkMode();
});
