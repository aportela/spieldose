const routes = [
  {
    path: "/",
    component: () => import("layouts/SignInSignUpLayout.vue"),
    children: [
      {
        name: "signIn",
        path: "/sign_in",
        component: () => import("pages/SignInPage.vue"),
      },
      {
        name: "signUp",
        path: "/sign_up",
        component: () => import("pages/SignUpPage.vue"),
      },
    ],
  },
  {
    path: "/app",
    component: () => import("layouts/MainLayout.vue"),
    children: [
      {
        name: "index",
        path: "index",
        component: () => import("pages/IndexPage.vue"),
      },
      {
        name: "dashboard",
        path: "dashboard",
        component: () => import("pages/DashboardPage.vue"),
      },
    ],
  },
  // Always leave this as last one,
  // but you can also remove it
  {
    path: "/:catchAll(.*)*",
    component: () => import("pages/ErrorNotFound.vue"),
  },
];

export default routes;
