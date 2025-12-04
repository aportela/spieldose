import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: "/auth",
    component: () => import("layouts/LoginRegisterLayout.vue"),
    children: [
      {
        name: "login",
        path: "login",
        component: () => import("pages/LoginPage.vue"),
      },
      {
        name: "register",
        path: "register",
        component: () => import("pages/RegisterPage.vue"),
      },
    ],
  },
  {
    path: "/",
    name: "root",
    redirect: "/index",
    component: () => import("layouts/MainLayout.vue"),
    children: [
      {
        name: "index",
        path: "index",
        component: () => import("pages/IndexPage.vue"),
      },
    ],
  },
  // Always leave this as last one,
  // but you can also remove it
  {
    path: "/:catchAll(.*)*",
    name: "notFound",
    component: () => import("layouts/ErrorNotFoundLayout.vue"),
  },
];

export default routes;
