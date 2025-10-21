import { boot } from "quasar/wrappers";

import { usei18n } from "src/composables/usei18n";

const { i18n } = usei18n();

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async ({ app, router, store }) => {
  app.use(i18n);
});
