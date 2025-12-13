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
}

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
  type SelectorOption,
  sortOrderSelectorOptions,
  type AudioMotionAnalyzerOptionColorMode,
};
