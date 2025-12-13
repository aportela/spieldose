import { type SortOrder } from './sort';

type EnvironmentType = 'development' | 'production';

type ValidAuthTypes = 'Bearer';

type VinylAnimation = 'vinyl' | 'cassete' | null;

type SpectrumAnalyzerChannelLayout =
  | 'single'
  | 'dual-combined'
  | 'dual-horizontal'
  | 'dual-vertical';

interface Player {
  userInteracted: boolean;
  status: PlayerStatus;
  repeatMode: PlayerRepeatMode;
  shuffle: boolean;
}

type PlayerStatus = 'stopped' | 'playing' | 'paused';
type PlayerRepeatMode = 'none' | 'track' | 'playList';

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
  index: number;
  visible: boolean;
}

const availablePlayListTableColumns: PlayListTableColumn[] = [
  {
    name: 'index',
    label: 'Index',
    index: 0,
    visible: true,
  },
  {
    name: 'image',
    label: 'Image',
    index: 1,
    visible: false,
  },
  {
    name: 'trackTitle',
    label: 'Title',
    index: 2,
    visible: true,
  },
  {
    name: 'trackArtist',
    label: 'Artist',
    index: 3,
    visible: true,
  },
  {
    name: 'trackAlbumTitle',
    label: 'Album',
    index: 4,
    visible: true,
  },
  {
    name: 'trackAlbumNumber',
    label: 'Track album number',
    index: 5,
    visible: true,
  },
  {
    name: 'trackAlbumArtist',
    label: 'Album artist',
    index: 6,
    visible: true,
  },

  {
    name: 'year',
    label: 'Year',
    index: 7,
    visible: true,
  },
  {
    name: 'actions',
    label: 'Actions',
    index: 8,
    visible: true,
  },
];

interface SelectorOption {
  label: string;
  value: SortOrder;
}

const sortOrderSelectorOptions: SelectorOption[] = [
  {
    label: 'Ascending',
    value: 'ASC',
  },
  {
    label: 'Descending',
    value: 'DESC',
  },
];

type AudioMotionAnalyzerOptionColorMode = 'gradient' | 'bar-index' | 'bar-level';

export {
  type EnvironmentType,
  type ValidAuthTypes,
  type VinylAnimation,
  type SpectrumAnalyzerChannelLayout,
  type Player,
  type PlayerStatus,
  type PlayerRepeatMode,
  type PlayListTableColumnName,
  type PlayListTableColumn,
  availablePlayListTableColumns,
  type SelectorOption,
  sortOrderSelectorOptions,
  type AudioMotionAnalyzerOptionColorMode,
};
