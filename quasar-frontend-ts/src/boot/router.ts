import { defineBoot } from '#q-app/wrappers';
import { useSessionStore } from 'src/stores/session';

const sessionStore = useSessionStore();
const accessTokenCheckInterval = 300; // check every 5 min (300 seconds)

export default defineBoot(async ({ router }) => {
  // at startup, if a refresh token is available, we generate a new access token
  if (!sessionStore.hasAccessToken) {
    await sessionStore.refreshAccessToken();
  }

  const refreshAccessTokenIfNeeded = async (): Promise<boolean> => {
    if (
      sessionStore.hasAccessToken &&
      sessionStore.accessTokenExpiresBeforeInterval(accessTokenCheckInterval)
    ) {
      return await sessionStore.refreshAccessToken();
    } else {
      return false;
    }
  };

  // create interval for checking && auto-refresh access token
  setInterval(() => {
    refreshAccessTokenIfNeeded()
      .then(() => {})
      .catch((e: Error) => {
        console.error('An unhandled exception occurred during access token refresh', e);
      })
      .finally(() => {});
  }, accessTokenCheckInterval * 1000);

  router.beforeEach((to, _from, next) => {
    if (!to.matched.length) {
      next({ name: 'notFound' });
      return;
    }
    if (sessionStore.hasAccessToken) {
      if (to.name === 'login' || to.name === 'register') {
        next({ name: 'index' });
      } else {
        next();
      }
    } else {
      if (to.name === 'login' || to.name === 'register') {
        next();
      } else {
        next({ name: 'login' });
      }
    }
  });
});
