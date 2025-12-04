import { defineBoot } from '#q-app/wrappers'
import { type GetNewAccessTokenResponse as GetNewAccessTokenResponseInterface } from "src/types/api-responses";
import { useSessionStore } from "src/stores/session";
import { api } from "src/composables/api";
import { AxiosError } from "axios";

const sessionStore = useSessionStore();

export default defineBoot(async ({ router }) => {

  if (!sessionStore.hasAccessToken) {
    try {
      const successResponse: GetNewAccessTokenResponseInterface = await api.auth.renewAccessToken();
      sessionStore.setAccessToken(successResponse.data.accessToken);
    } catch (e: unknown) {
      if (e instanceof AxiosError) {
        if (e.status !== 401) { // 401 is the "normal" error response if we do not have a previous refresh token
          console.error("Invalid error http response code", e.status);
        }
      } else {
        console.error("Invalid error response", e);
      }
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
