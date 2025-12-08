import { defineStore, acceptHMRUpdate } from 'pinia';
import { date } from "quasar";
import { setFavoriteTrack, unsetFavoriteTrack } from 'src/composables/trackActions';
import { buildDownloadTrackURL } from 'src/composables/common';
import { type File as FileInterface } from 'src/types/file';
import { type Stream as StreamInterface } from 'src/types/stream';

interface State {
  file: FileInterface | null;
  stream: StreamInterface | null;
  lastTimestamp: number;
};

export const useCurrentPlaylistItemStore = defineStore('currentPlaylistItemStore', {
  state: (): State => ({
    file: null,
    stream: null,
    lastTimestamp: 0,
  }),
  getters: {
    t: (state) => state.lastTimestamp,
    isTrack: (state) => state.file !== null,
    isRadioStation: (state) => state.file !== null,
    trackFileId: (state) => (state.file !== null ? state.file.id : null),
    trackFileName: (state) => (state.file !== null ? state.file.name : null),
    trackFileSize: (state) => (state.file !== null ? state.file.size : 0),
    trackMimeType: (state) => (state.file !== null ? state.file.mime : null),
    trackPlayTimeSeconds: (state) =>
      state.file !== null ? state.file.trackInfo.playTimeSeconds : null,
    trackTitle: (state) =>
      state.file !== null ? state.file.trackInfo.title : null,
    trackArtistName: (state) =>
      state.file !== null ? state.file.trackInfo.artist.name : null,
    trackArtistMBId: (state) =>
      state.file !== null ? state.file.trackInfo.artist.mbId : null,
    trackAlbumTitle: (state) =>
      state.file !== null ? state.file.trackInfo.album.title : null,
    trackAlbumMBId: (state) =>
      state.file !== null ? state.file.trackInfo.album.mbId : null,
    trackAlbumYear: (state) =>
      state.file !== null ? state.file.trackInfo.album.year : null,
    trackAlbumArtistName: (state) =>
      state.file !== null ? state.file.trackInfo.album.artist.name : null,
    trackAlbumArtistMBId: (state) =>
      state.file !== null ? state.file.trackInfo.album.artist.mbId : null,
    trackImageSmall: (state) =>
      state.file !== null ? state.file.trackInfo.image.small : null,
    trackImageNormal: (state) =>
      state.file !== null ? state.file.trackInfo.image.normal : null,
    trackDownloadURL: (state) =>
      state.file !== null ? buildDownloadTrackURL(state.file.id) : null,
    trackFavorited: (state) =>
      state.file !== null ? state.file.trackInfo.favorited : null,
  },
  actions: {
    setTrack(
      fileId: string,
      fileName: string,
      fileSize: number,
      mime: string,
      playTimeSeconds: number,
      title: string | null,
      artistName: string | null,
      artistMBId: string | null,
      albumTitle: string | null,
      albumMBId: string | null,
      albumYear: number | null,
      albumArtistName: string | null,
      albumArtistMBId: string | null,
      imageSmall: string | null,
      imageNormal: string | null,
      favorited: number | null,
    ) {
      this.lastTimestamp = Number(date.formatDate(new Date(), "x"));
      this.stream = null;
      this.file = {
        id: fileId,
        name: fileName,
        size: fileSize,
        mime: mime,
        trackInfo: {
          playTimeSeconds: playTimeSeconds,
          title: title,
          artist: {
            name: artistName,
            mbId: artistMBId,
          },
          album: {
            title: albumTitle,
            mbId: albumMBId,
            year: albumYear,
            artist: {
              name: albumArtistName,
              mbId: albumArtistMBId,
            },
          },
          image: {
            small: imageSmall,
            normal: imageNormal,
          },
          favorited: favorited,
        },
      };
    },
    setRadioStation(id: string, name: string, url: string, image: string) {
      this.lastTimestamp = Number(date.formatDate(new Date(), "x"));
      this.file = null;
      this.stream = {
        id: id,
        name: name,
        url: url,
        image: image,
      };
    },
    async toggleFavoriteTrack() {
      if (this.isTrack) {
        if (this.trackFavorited === null) {
          try {
            this.file!.trackInfo.favorited = await setFavoriteTrack(
              this.file!.id,
            );
          } catch (e: unknown) {
            console.error("Error setting favorite track", e);
          }
        } else {
          try {
            this.file!.trackInfo.favorited = await unsetFavoriteTrack(
              this.file!.id,
            );
          } catch (e: unknown) {
            console.error("Error unsetting favorite track", e);
          }
        }
      } else {
        console.error("only tracks can set the favorite flag");
      }
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useCurrentPlaylistItemStore, import.meta.hot));
}
