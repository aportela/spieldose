import { api } from 'src/composables/api';
import { useCurrentPlaylistItemStore } from 'src/stores/currentPlaylistItem';

const currentPlaylistItemStore = useCurrentPlaylistItemStore();

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
        successResponse.data.file.trackInfo.favorited,
      );
    })
    .catch((errorResponse) => {
      console.error(errorResponse);
    });
};

const skipToPreviousItem = () => {
  console.log('TODO: skip previous');
  randomTrack();
};
const skipToNextItem = () => {
  console.log('TODO: skip next');
  randomTrack();
};

export { randomTrack, skipToPreviousItem, skipToNextItem };
