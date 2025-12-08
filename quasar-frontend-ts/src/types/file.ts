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
    image: {
      small: string | null;
      normal: string | null;
    };
    favorited: number | null;
  };
};

export { type File };
