import { defineBoot } from '#q-app/wrappers'

import { type GetNewAccessTokenResponse as GetNewAccessTokenResponseInterface } from "src/types/api-responses";
import { useSessionStore } from "src/stores/session";
import { api } from "src/composables/api";

const sessionStore = useSessionStore();

export default defineBoot(async ({ router }) => {
  if (!sessionStore.hasAccessToken) {
    try {
      const successResponse: GetNewAccessTokenResponseInterface = await api.auth.renewAccessToken();
      sessionStore.setAccessToken(successResponse.data.accessToken);
    } catch (e) {
      console.error(e);
    }
  }
  router.beforeEach((to, _from, next) => {
    if (!to.matched.length) {
      next({ name: "notFound" });
      return;
    }
    if (sessionStore.hasAccessToken) {
      if (to.name === "login" || to.name === "register") {
        next({ name: "index" });
      } else {
        next();
      }
    } else {
      // TODO: session.hasRefreshToken
      if (to.name === "login" || to.name === "register") {
        next();
      } else {
        next({ name: "login" });
      }
    }
  });
})
