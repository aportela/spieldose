import { api } from 'src/composables/api';

const setFavoriteTrack = async (id: string) => {
  try {
    const response = await api.track.setFavorite(id);
    return response.data?.favorited;
  } catch (error) {
    console.error(error);
    throw error;
  }
};

const unsetFavoriteTrack = async (id: string) => {
  try {
    const response = await api.track.unSetFavorite(id);
    return response.data?.favorited;
  } catch (error) {
    console.error(error);
    throw error;
  }
};

export { setFavoriteTrack, unsetFavoriteTrack };
