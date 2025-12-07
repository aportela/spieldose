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
      {
        name: "currentPlaylist",
        path: "current_playlist",
        component: () => import("pages/CurrentPlayListPage.vue"),
      },
      {
        name: "artists",
        path: "artists",
        component: () => import("pages/BrowseArtistsPage.vue"),
      },
      {
        name: "artist",
        path: "artist/:name",
        component: () => import("pages/ArtistPage.vue"),
      },
      {
        name: "albums",
        path: "albums",
        component: () => import("pages/BrowseAlbumsPage.vue"),
      },
      {
        name: "album",
        path: "album/:title",
        component: () => import("pages/AlbumPage.vue"),
      },
      {
        name: "profile",
        path: "profile",
        component: () => import("pages/ProfilePage.vue"),
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
