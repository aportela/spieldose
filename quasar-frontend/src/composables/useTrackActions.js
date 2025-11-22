import { useAPI } from "src/composables/useAPI";

const { api } = useAPI();

export function useTrackActions() {
  const setFavoriteTrack = async (id) => {
    try {
      const response = await api.track.setFavorite(id);
      return response.data?.favorited;
    } catch (error) {
      console.error(error);
      throw error;
    }
  };

  const unsetFavoriteTrack = async (id) => {
    try {
      const response = await api.track.unSetFavorite(id);
      return response.data?.favorited;
    } catch (error) {
      console.error(error);
      throw error;
    }
  };

  return { setFavoriteTrack, unsetFavoriteTrack };
}
