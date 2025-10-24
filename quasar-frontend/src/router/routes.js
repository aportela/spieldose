const routes = [
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
    redirect: "/dashboard",
    component: () => import("layouts/MainLayout.vue"),
    children: [
      {
        name: "dashboard",
        path: "dashboard",
        component: () => import("pages/HomePage.vue"),
      },
      {
        name: "currentPlaylist",
        path: "current_playlist",
        component: () => import("pages/CurrentPlaylistPage.vue"),
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
        name: "paths",
        path: "paths",
        component: () => import("pages/BrowsePathsPage.vue"),
      },
      {
        name: "playlists",
        path: "playlists",
        component: () => import("pages/BrowsePlaylistsPage.vue"),
      },
      {
        name: "radioStations",
        path: "radio_stations",
        component: () => import("pages/BrowseRadioStationsPage.vue"),
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
