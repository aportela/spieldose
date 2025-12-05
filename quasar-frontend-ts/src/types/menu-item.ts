interface MenuItem {
  icon: string;
  text: string;
  routeName: string;
  alternateRouteNames?: string;
  action?: () => void;
};

const topHeaderMenuItems: MenuItem[] = [
  { icon: 'home', text: "Index", routeName: 'index' },
  { icon: 'search', text: "Search", routeName: 'search' },

  {
    icon: 'analytics',
    text: 'Dashboard',
    routeName: 'dashboard'
  },

  {
    icon: 'list_alt',
    text: 'Current playlist',
    routeName: 'currentPlaylist'
  },
  {
    icon: 'person',
    text: 'Browse artists',
    routeName: 'artists'
  },
  /*
  {
    icon: 'search',
    text: 'Search',
    routeName: 'search'
  },
  */
  {
    icon: 'album',
    text: 'Browse albums',
    routeName: 'albums'
  },
  {
    icon: 'folder_open',
    text: 'Browse paths',
    routeName: 'paths'
  },

  {
    icon: 'list',
    text: 'Browse playlists',
    routeName: 'playlists'
  },
  {
    icon: 'radio',
    text: 'Browse radio stations',
    routeName: 'radioStations'
  },
  { icon: 'account_circle', text: "My profile", routeName: 'profile' },
];

export { type MenuItem, topHeaderMenuItems };
