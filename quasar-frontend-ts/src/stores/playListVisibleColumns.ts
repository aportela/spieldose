import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from 'src/composables/localStorage';

export type PlayListTableColumnName =
  | 'index'
  | 'image'
  | 'trackTitle'
  | 'trackArtist'
  | 'albumTitle'
  | 'albumArtist'
  | 'albumTrackNumber'
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
    name: 'albumTitle',
    label: 'Album',
  },
  {
    name: 'albumTrackNumber',
    label: 'Track album number',
  },
  {
    name: 'albumArtist',
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
    availableColumnDefinitions: (state): PlayListTableColumn[] => state.columns.available,
    visibleColumnDefinitions: (state): PlayListTableColumn[] =>
      state.columns.available.filter((column) => state.columns.visible.includes(column.name)),
    visibleColumnNames: (state): string[] => state.columns.visible,

    isIndexColumnVisible: (state): boolean => state.columns.visible.includes('index'),
    isImageColumnVisible: (state): boolean => state.columns.visible.includes('image'),
    isTrackTitleColumnVisible: (state): boolean => state.columns.visible.includes('trackTitle'),
    isTrackArtistColumnVisible: (state): boolean => state.columns.visible.includes('trackArtist'),
    isAlbumTitleColumnVisible: (state): boolean => state.columns.visible.includes('albumTitle'),
    isAlbumArtistColumnVisible: (state): boolean => state.columns.visible.includes('albumArtist'),
    isAlbumTrackNumberColumnVisible: (state): boolean =>
      state.columns.visible.includes('albumTrackNumber'),
    isYearColumnVisible: (state): boolean => state.columns.visible.includes('year'),
    isActionsColumnVisible: (state): boolean => state.columns.visible.includes('actions'),
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
