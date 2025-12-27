import { uid } from 'quasar';

interface File {
  id: string;
  name: string;
  size: number;
  mime: string;
  trackInfo: {
    playTimeSeconds: number;
    title: string | null;
    artist: {
      name: string | null;
      mbId: string | null;
    };
    album: {
      title: string | null;
      mbId: string | null;
      year: number | null;
      artist: {
        name: string | null;
        mbId: string | null;
      };
    };
    favorited: boolean;
  };
}

interface Stream {
  id: string;
  name: string;
  url: string;
  image: string;
}

interface PlayListItemImages {
  small: string | null;
  medium: string | null;
  big: string | null;
}

interface PlayListItem {
  file: File | null;
  stream: Stream | null;
  images: PlayListItemImages | null;
}

class PlayListItemClass implements PlayListItem {
  _id: string;
  file: File | null;
  stream: Stream | null;
  images: PlayListItemImages | null;

  constructor(file: File | null, stream: Stream | null, images: PlayListItemImages | null) {
    this._id = uid();
    this.file = file;
    this.stream = stream;
    this.images = images;
  }

  get isFile(): boolean {
    return this.file !== null;
  }

  get isStream(): boolean {
    return this.stream !== null;
  }

  get smallImage(): string | null {
    return this.images !== null ? this.images.small : null;
  }

  get mediumImage(): string | null {
    return this.images !== null ? this.images.medium : null;
  }

  get bigImage(): string | null {
    return this.images !== null ? this.images.big : null;
  }
}

interface PlayListFlags {
  isMine: boolean;
  opened: boolean;
  actived: boolean;
  published: boolean;
  shared: boolean;
  isFavorites: boolean;
}

interface PlayList {
  id: string;
  name: string;
  items: PlayListItemClass[];
  flags: PlayListFlags;
  currentItemIndex: null | number;
  currentItemPosition: null | number;
}

class PlayListClass implements PlayList {
  id: string;
  name: string;
  items: PlayListItemClass[];
  flags: PlayListFlags;
  currentItemIndex: null | number;
  currentItemPosition: null | number;

  constructor(
    id: string,
    name: string,
    items: PlayListItem[],
    flags: PlayListFlags,
    currentItemIndex: null | number,
    currentItemPosition: null | number,
  ) {
    this.id = id;
    this.name = name;
    this.items = items.map((item) => new PlayListItemClass(item.file, item.stream, item.images));
    this.flags = flags;
    this.currentItemIndex = currentItemIndex;
    this.currentItemPosition = currentItemPosition;
  }

  get hasItems(): boolean {
    return this.items.length > 0;
  }

  get allowSkipNext(): boolean {
    return (
      this.hasItems && (this.currentItemIndex === null || this.currentItemIndex < this.items.length)
    );
  }

  get allowSkipPrevious(): boolean {
    return this.hasItems && this.currentItemIndex !== null && this.currentItemIndex > 0;
  }
}

export { type PlayListItem, PlayListItemClass, type PlayList, PlayListClass };
