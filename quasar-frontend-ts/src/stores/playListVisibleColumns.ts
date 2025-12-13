import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from 'src/composables/localStorage';

const localStoragePlaylistIndexColumnVisibility = createStorageEntry<boolean>(
  'interface.playlist.columnVisibility.index',
  true,
);

const localStoragePlaylistImageColumnVisibility = createStorageEntry<boolean>(
  'interface.playlist.columnVisibility.image',
  false,
);

const localStoragePlaylistTrackTitleColumnVisibility = createStorageEntry<boolean>(
  'interface.playlist.columnVisibility.trackTitle',
  true,
);

const localStoragePlaylistTrackArtistColumnVisibility = createStorageEntry<boolean>(
  'interface.playlist.columnVisibility.trackArtist',
  true,
);

const localStoragePlaylistTrackAlbumTitleColumnVisibility = createStorageEntry<boolean>(
  'interface.playlist.columnVisibility.trackAlbumTitle',
  true,
);

const localStoragePlaylistTrackAlbumNumberColumnVisibility = createStorageEntry<boolean>(
  'interface.playlist.columnVisibility.trackAlbumNumber',
  true,
);

const localStoragePlaylistTrackAlbumArtistColumnVisibility = createStorageEntry<boolean>(
  'interface.playlist.columnVisibility.trackAlbumArtist',
  true,
);

const localStoragePlaylistTrackYear = createStorageEntry<boolean>(
  'interface.playlist.columnVisibility.year',
  true,
);

const localStoragePlaylistActions = createStorageEntry<boolean>(
  'interface.playlist.columnVisibility.actions',
  true,
);

interface State {
  columns: {
    index: boolean;
    image: boolean;
    trackTitle: boolean;
    trackArtist: boolean;
    trackAlbumTitle: boolean;
    trackAlbumNumber: boolean;
    trackAlbumArtist: boolean;
    year: boolean;
    actions: boolean;
  };
}
export const usePlayListVisibleColumnsStore = defineStore('playListVisibleColumnsStore', {
  state: (): State => ({
    columns: {
      index: localStoragePlaylistIndexColumnVisibility.get(),
      image: localStoragePlaylistImageColumnVisibility.get(),
      trackTitle: localStoragePlaylistTrackTitleColumnVisibility.get(),
      trackArtist: localStoragePlaylistTrackArtistColumnVisibility.get(),
      trackAlbumTitle: localStoragePlaylistTrackAlbumTitleColumnVisibility.get(),
      trackAlbumNumber: localStoragePlaylistTrackAlbumNumberColumnVisibility.get(),
      trackAlbumArtist: localStoragePlaylistTrackAlbumArtistColumnVisibility.get(),
      year: localStoragePlaylistTrackYear.get(),
      actions: localStoragePlaylistActions.get(),
    },
  }),
  getters: {},
  actions: {},
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(usePlayListVisibleColumnsStore, import.meta.hot));
}
