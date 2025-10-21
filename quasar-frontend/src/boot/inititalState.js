import { boot } from "quasar/wrappers";

import { useAPI } from "src/composables/useAPI";

const { api } = useAPI();

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async (/* { app, router, ... } */) => {
  try {
    // on useAxios composable we declare a response interceptor that assign initialState (if found) for every response
    await api.common.initialState();
  } catch (error) {
    console.error(error);
  }
});
