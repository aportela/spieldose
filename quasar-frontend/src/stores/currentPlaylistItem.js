import { defineStore } from "pinia";

import { useFormatDates } from "src/composables/useFormatDates";

const { currentTimestamp } = useFormatDates();

export const useCurrentPlaylistItemStore = defineStore("currentPlaylistItem", {
  state: () => ({
    file: null,
    stream: null,
    lastTimestamp: currentTimestamp(),
  }),
  getters: {
    t: (state) => state.timestamp,
    isTrack: (state) => state.file !== null,
    isRadioStation: (state) => state.file !== null,
    track: (state) => state.file,
    radioStation: (state) => state.stream,
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
      image,
    ) {
      this.lastTimestamp = currentTimestamp();
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
          image: image,
        },
      };
    },
    // TODO
    setRadioStation(id = null, name = null, url = null, image = null) {
      this.lastTimestamp = currentTimestamp();
      this.file = null;
      this.stream = {
        id: id,
        name: name,
        url: url,
        image: image,
      };
    },
  },
});
