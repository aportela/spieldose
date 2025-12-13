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

interface PlayListTableColumn {
  name: PlayListTableColumnName;
  label: string;
}

const availablePlayListTableColumns: PlayListTableColumn[] = [
  {
    name: 'index',
    label: 'Index',
  },
  {
    name: 'image',
    label: 'Image',
  },
  {
    name: 'trackTitle',
    label: 'Title',
  },
  {
    name: 'trackArtist',
    label: 'Artist',
  },
  {
    name: 'trackAlbumTitle',
    label: 'Album',
  },
  {
    name: 'trackAlbumNumber',
    label: 'Track album number',
  },
  {
    name: 'trackAlbumArtist',
    label: 'Album artist',
  },

  {
    name: 'year',
    label: 'Year',
  },
  {
    name: 'actions',
    label: 'Actions',
  },
];

const defaultVisibleColumns: PlayListTableColumnName[] = availablePlayListTableColumns
  .filter((column) => column.name !== 'image')
  .map((column) => column.name);

const localStoragePlaylistVisibleColumns = createStorageEntry<string>(
  'interface.playlist.visibleColumns',
  defaultVisibleColumns.join(','),
);

interface State {
  columns: {
    available: PlayListTableColumn[];
    visible: PlayListTableColumnName[];
  };
}

export const usePlayListVisibleColumnsStore = defineStore('playListVisibleColumnsStore', {
  state: (): State => ({
    columns: {
      available: availablePlayListTableColumns,
      visible: localStoragePlaylistVisibleColumns.get().split(',') as PlayListTableColumnName[],
    },
  }),
  getters: {
    availableColumnDefinitions: (state) => state.columns.available,
    visibleColumnDefinitions: (state) =>
      state.columns.available.filter((column) => state.columns.visible.includes(column.name)),
    visibleColumnNames: (state) => state.columns.visible,
  },
  actions: {
    toggleColumnVisibility(columnName: PlayListTableColumnName) {
      if (this.columns.visible.includes(columnName)) {
        this.columns.visible = this.columns.visible.filter((c) => c !== columnName);
      } else {
        this.columns.visible.push(columnName);
      }
      localStoragePlaylistVisibleColumns.set(this.columns.visible.join(','));
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(usePlayListVisibleColumnsStore, import.meta.hot));
}
