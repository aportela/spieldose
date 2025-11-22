import { useAPI } from "src/composables/useAPI";
import { usePlayerStore } from "src/stores/player";
import { useCurrentPlaylistItemStore } from "src/stores/currentPlaylistItem";

const currentPlaylistItemStore = useCurrentPlaylistItemStore();

const { api } = useAPI();

const randomTrack = () => {
  api.file
    .getRandom()
    .then((successResponse) => {
      currentPlaylistItemStore.setTrack(
        successResponse.data.file.id,
        successResponse.data.file.filename,
        successResponse.data.file.filesize,
        successResponse.data.file.mime,
        successResponse.data.file.trackInfo.playTimeSeconds,
        successResponse.data.file.trackInfo.title,
        successResponse.data.file.trackInfo.artist.name,
        successResponse.data.file.trackInfo.artist.mbId,
        successResponse.data.file.trackInfo.album.title,
        successResponse.data.file.trackInfo.album.mbId,
        successResponse.data.file.trackInfo.album.year,
        successResponse.data.file.trackInfo.album.artist.name,
        successResponse.data.file.trackInfo.album.artist.mbId,
        successResponse.data.file.trackInfo.imageURL.small,
        successResponse.data.file.trackInfo.imageURL.normal,
      );
    })
    .catch((errorResponse) => {
      console.error(errorResponse);
    });
};

export function usePlaylistActions() {
  const skipPrevious = () => {
    console.log("TODO: skip previous");
    randomTrack();
  };
  const skipNext = () => {
    console.log("TODO: skip next");
    randomTrack();
  };

  return { randomTrack, skipPrevious, skipNext };
}
