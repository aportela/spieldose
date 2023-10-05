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
        name: "dashboard",
        path: "dashboard",
        component: () => import("pages/DashboardPage.vue"),
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
        name: "playlistsByUserId",
        path: "playlists/user/:id",
        component: () => import("pages/BrowsePlaylistsPage.vue"),
      },
      {
        name: "radioStations",
        path: "radio_stations",
        component: () => import("pages/BrowseRadioStationsPage.vue"),
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
