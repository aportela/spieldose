import { defineBoot } from '#q-app/wrappers'

import { api } from "src/composables/api";
import { useServerEnvironmentStore } from "src/stores/serverEnvironment";
import { type getServerEnvironmentResponseData } from "src/types/apiResponses";

const serverEnvironment = useServerEnvironmentStore();

export default defineBoot(async (/* { app, router, ... } */) => {
  try {
    const response: getServerEnvironmentResponseData = await api.common.getServerEnvironment();
    serverEnvironment.set(
      response.data.serverEnvironment.allowSignUp,
      response.data.serverEnvironment.environment,
    );
  } catch (error) {
    console.error(error);
  }
})
