
import { type File as FileInterface } from 'src/types/file';
import { type Stream as StreamInterface } from 'src/types/stream';

interface PlayListItem {
  file: FileInterface | null;
  stream: StreamInterface | null;
};

interface PlayList {
  id: string | null;
  name: string | null;
  items: PlayListItem[];
};

class PlayListClass implements PlayList {
  id: string | null;
  name: string | null;
  items: PlayListItem[];

  constructor(id: string | null, name: string | null, items: PlayListItem[]) {
    this.id = id;
    this.name = name;
    this.items = items;
  }
}

export { type PlayListItem, type PlayList, PlayListClass };
