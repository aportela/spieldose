import { defineStore } from "pinia";
import { useTrackActions } from "src/composables/useTrackActions";

import { date } from "quasar";

const { setFavoriteTrack, unsetFavoriteTrack } = useTrackActions();
export const useCurrentPlaylistItemStore = defineStore("currentPlaylistItem", {
  state: () => ({
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
      state.file !== null ? `/api2/file/download/${state.file.id}` : null,
    trackFavorited: (state) =>
      state.file !== null ? state.file.trackInfo.favorited : null,
  },
  actions: {
    setTrack(
      fileId = null,
      fileName = null,
      fileSize = 0,
      mime = null,
      playTimeSeconds = 0,
      title = null,
      artistName = null,
      artistMBId = null,
      albumTitle = null,
      albumMBId = null,
      albumYear = null,
      albumArtistName = null,
      albumArtistMBId = null,
      imageSmall = null,
      imageNormal = null,
      favorited = null,
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
    // TODO
    setRadioStation(id = null, name = null, url = null, image = null) {
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
            this.file.trackInfo.favorited = await setFavoriteTrack(
              this.file.id,
            );
          } catch (e) {
            // TODO
          }
        } else {
          try {
            this.file.trackInfo.favorited = await unsetFavoriteTrack(
              this.file.id,
            );
          } catch (e) {
            // TODO
          }
        }
      } else {
        console.error("only tracks can set the favorite flag");
      }
    },
  },
});
