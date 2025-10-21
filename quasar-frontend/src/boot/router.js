import { boot } from "quasar/wrappers";
import { useSessionStore } from "stores/session";

const session = useSessionStore();

export default boot(({ app, router, store }) => {
  router.beforeEach((to, from, next) => {
    if (!to.matched.length) {
      next({ name: "notFound" });
      return;
    }

    if (!session.isLoaded) {
      session.load();
    }

    if (session.isLogged) {
      if (to.name === "signIn" || to.name === "signUp") {
        next({ name: "dashboard" });
      } else {
        next();
      }
    } else {
      if (to.name === "signIn" || to.name === "signUp") {
        next();
      } else {
        next({ name: "signIn" });
      }
    }
  });
});
