import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from 'src/composables/localStorage';

type PlayListTableColumnName =
  | 'index'
  | 'image'
  | 'trackTitle'
  | 'trackArtist'
  | 'trackAlbumTitle'
  | 'trackAlbumNumber'
  | 'trackAlbumArtist'
  | 'year'
  | 'actions';

const availableColumns: PlayListTableColumnName[] = [
  'index',
  'image',
  'trackTitle',
  'trackArtist',
  'trackAlbumTitle',
  'trackAlbumNumber',
  'trackAlbumArtist',
  'year',
  'actions',
];
const defaultVisibleColumns: PlayListTableColumnName[] = [
  'index',
  'trackTitle',
  'trackArtist',
  'trackAlbumTitle',
  'trackAlbumNumber',
  'trackAlbumArtist',
  'year',
  'actions',
];

const localStoragePlaylistVisibleColumns = createStorageEntry<string>(
  'interface.playlist.visibleColumns',
  defaultVisibleColumns.join(','),
);

interface State {
  availableColumns: PlayListTableColumnName[];
  visibleColumns: PlayListTableColumnName[];
}

export const usePlayListVisibleColumnsStore = defineStore('playListVisibleColumnsStore', {
  state: (): State => ({
    availableColumns: availableColumns,
    visibleColumns: localStoragePlaylistVisibleColumns
      .get()
      .split(',') as PlayListTableColumnName[],
  }),
  getters: {},
  actions: {
    isColumnVisible(column: PlayListTableColumnName) {
      return this.visibleColumns.includes(column);
    },
    toggleColumnVisibility(column: PlayListTableColumnName) {
      if (this.visibleColumns.includes(column)) {
        this.visibleColumns = this.visibleColumns.filter((c) => c !== column);
      } else {
        this.visibleColumns.push(column);
      }
      localStoragePlaylistVisibleColumns.set(this.visibleColumns.join(','));
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(usePlayListVisibleColumnsStore, import.meta.hot));
}
