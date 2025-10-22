import { useAxios } from "src/composables/useAxios";

const { axios } = useAxios();

const baseAPIPath = "/api2";

export function useAPI() {
  const api = {
    common: {
      initialState: () => axios.get(baseAPIPath + "/initial_state"),
    },
    auth: {
      login: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.post(baseAPIPath + "/auth/login", params);
      },
      logout: () => axios.post(baseAPIPath + "/auth/logout"),
      register: function (id, email, password) {
        const params = {
          id: id,
          email: email,
          password: password,
        };
        return axios.post(baseAPIPath + "/auth/register", params);
      },
    },
    user: {
      getProfile: () => axios.get(baseAPIPath + "/user/profile"),
      updateProfile: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.put(baseAPIPath + "/user/profile", params);
      },
    },
    globalSearch: {
      search: function (
        filter,
        currentPageIndex,
        resultsPage,
        randomSort,
        sortField,
        sortOrder,
      ) {
        const params = {
          pager: {
            currentPageIndex: currentPageIndex,
            resultsPage: resultsPage,
          },
          sort: {
            random: randomSort,
            field: sortField,
            order: sortOrder,
          },
          filter: filter || {},
        };
        return axios.post(baseAPIPath + "/global_search", params);
      },
    },
    artist: {
      search: function (
        filter,
        currentPageIndex,
        resultsPage,
        sortField,
        sortOrder,
      ) {
        const params = {
          filter: filter || {},
          pager: {
            currentPageIndex: currentPageIndex,
            resultsPage: resultsPage,
          },
          sort: {
            field: sortField,
            order: sortOrder,
          },
        };
        return axios.post(baseAPIPath + "/artist/search", params);
      },
      get: function (mbId, name) {
        return axios.get(
          "api2/artist?mbId=" +
          encodeURIComponent(mbId || "") +
          "&name=" +
          encodeURIComponent(name || ""),
          {},
        );
      },
      getOverview: function (mbId, name) {
        return axios.get(
          "api2/artist_overview?mbId=" +
          encodeURIComponent(mbId || "") +
          "&name=" +
          encodeURIComponent(name || ""),
          {},
        );
      },
    },
    artistGenres: {
      get: function () {
        return axios.get(baseAPIPath + "/artists_genres");
      },
    },
    album: {
      search: function (
        filter,
        currentPageIndex,
        resultsPage,
        sortField,
        sortOrder,
      ) {
        const params = {
          filter: filter || {},
          pager: {
            currentPageIndex: currentPageIndex,
            resultsPage: resultsPage,
          },
          sort: {
            field: sortField,
            order: sortOrder,
          },
        };
        return axios.post(baseAPIPath + "/album/search", params);
      },
      getSmallRandomCovers: function (count = 32) {
        return axios.get(baseAPIPath + "/album/small_random_covers/" + count, {});
      },
      get: function (mbId, title, artistMbId, artistName, year) {
        const params = {
          mbId: mbId || null,
          title: title || null,
          artistMBId: artistMbId || null,
          artistName: artistName || null,
          year: year || null,
        };
        return axios.get("api2/album", { params });
      },
    },
    track: {
      get: function (id) {
        return axios.get(baseAPIPath + "/track/" + id);
      },
      search: function (
        filter,
        currentPageIndex,
        resultsPage,
        randomSort,
        sortField,
        sortOrder,
      ) {
        const params = {
          pager: {
            currentPageIndex: currentPageIndex,
            resultsPage: resultsPage,
          },
          sort: {
            random: randomSort,
            field: sortField,
            order: sortOrder,
          },
          filter: filter || {},
        };
        return axios.post(baseAPIPath + "/track/search", params);
      },
      increasePlayCount: function (id) {
        return axios.get(baseAPIPath + "/track/increase_play_count/" + id);
      },
      setFavorite: function (id) {
        return axios.get(baseAPIPath + "/track/set_favorite/" + id);
      },
      unSetFavorite: function (id) {
        return axios.get(baseAPIPath + "/track/unset_favorite/" + id);
      },
    },
    path: {
      getTree: function () {
        return axios.get(baseAPIPath + "/path/tree");
      },
    },
    metrics: {
      getTracks: function (filter, sortField, count) {
        const params = {
          filter: filter || {},
          sortField: sortField,
          count: count || 5,
        };
        return axios.post(baseAPIPath + "/metrics/tracks", params);
      },
      getArtists: function (filter, sortField, count) {
        const params = {
          filter: filter || {},
          sortField: sortField,
          count: count || 5,
        };
        return axios.post(baseAPIPath + "/metrics/artists", params);
      },
      getAlbums: function (filter, sortField, count) {
        const params = {
          filter: filter || {},
          sortField: sortField,
          count: count || 5,
        };
        return axios.post(baseAPIPath + "/metrics/albums", params);
      },
      getGenres: function (filter, sortField, count) {
        const params = {
          filter: filter || {},
          sortField: sortField,
          count: count || 5,
        };
        return axios.post(baseAPIPath + "/metrics/genres", params);
      },
      getDataRanges: function (filter) {
        const params = {
          filter: filter || {},
        };
        return axios.post(baseAPIPath + "/metrics/date_range", params);
      },
      getMetricsByUser: function (filter) {
        const params = {
          filter: filter || {},
        };
        return axios.post(baseAPIPath + "/metrics/by_user", params);
      },
    },
    playlist: {
      search: function (
        filter,
        currentPageIndex,
        resultsPage,
        sortField,
        sortOrder,
      ) {
        const params = {
          filter: filter || {},
          pager: {
            currentPageIndex: currentPageIndex,
            resultsPage: resultsPage,
          },
          sort: {
            field: sortField,
            order: sortOrder,
          },
        };
        return axios.post(baseAPIPath + "/playlist/search", params);
      },
      add: function (id, name, tracks, isPublic) {
        const params = {
          playlist: {
            id: id,
            name: name,
            tracks: tracks || [],
            public: isPublic || false,
          },
        };
        return axios.post(baseAPIPath + "/playlist/add", params);
      },
      update: function (id, name, tracks, isPublic) {
        const params = {
          playlist: {
            id: id,
            name: name,
            tracks: tracks || [],
            public: isPublic || false,
          },
        };
        return axios.post(baseAPIPath + "/playlist/update", params);
      },
      delete: function (id) {
        return axios.delete(baseAPIPath + "/playlist/" + id);
      },
      get: function (id) {
        return axios.get(baseAPIPath + "/playlist/" + id);
      },
    },
    currentPlaylist: {
      get: function () {
        return axios.get(baseAPIPath + "/current_playlist");
      },
      setTracks: function (trackIds) {
        const params = {
          trackIds: trackIds || [],
        };
        return axios.post(baseAPIPath + "/current_playlist/set_tracks", params);
      },
      appendTracks: function (trackIds) {
        const params = {
          trackIds: trackIds || [],
        };
        return axios.post(baseAPIPath + "/current_playlist/append_tracks", params);
      },
      setAlbum: function (mbId, title, artistMBId, artistName, year) {
        const params = {
          album: {
            mbId: mbId || null,
            title: title || null,
            artist: { mbId: artistMBId || null, name: artistName || null },
            year: year || null,
          },
        };
        return axios.post(baseAPIPath + "/current_playlist/set_tracks", params);
      },
      appendAlbum: function (mbId, title, artistMBId, artistName, year) {
        const params = {
          album: {
            mbId: mbId || null,
            title: title || null,
            artist: { mbId: artistMBId || null, name: artistName || null },
            year: year || null,
          },
        };
        return axios.post(baseAPIPath + "/current_playlist/append_tracks", params);
      },
      setPlaylist: function (id) {
        const params = {
          playlistId: id || null,
        };
        return axios.post(baseAPIPath + "/current_playlist/set_tracks", params);
      },
      appendPlaylist: function (id) {
        const params = {
          playlistId: id || null,
        };
        return axios.post(baseAPIPath + "/current_playlist/append_tracks", params);
      },
      setRadioStation: function (id) {
        const params = {
          id: id || null,
        };
        return axios.post(baseAPIPath + "/current_playlist/set_radiostation", params);
      },
      setPath: function (id) {
        const params = {
          pathId: id || null,
        };
        return axios.post(baseAPIPath + "/current_playlist/set_tracks", params);
      },
      getCurrentElement: function (shuffle) {
        const params = { shuffle: shuffle ? true : false };
        return axios.get(baseAPIPath + "/current_playlist/current_element", { params });
      },
      skipToPreviousElement: function (shuffle) {
        const params = { shuffle: shuffle ? true : false };
        return axios.get(baseAPIPath + "/current_playlist/previous_element", { params });
      },
      skipToNextElement: function (shuffle) {
        const params = { shuffle: shuffle ? true : false };
        return axios.get(baseAPIPath + "/current_playlist/next_element", { params });
      },
      skipToElementAtIndex: function (index) {
        const params = {
          index: index >= 0 ? index : -1,
        };
        return axios.get(baseAPIPath + "/current_playlist/element_at_index", { params });
      },
      discover: function (count, shuffle) {
        const params = {
          count: count || 32,
          shuffle: shuffle ? true : false,
        };
        return axios.post(baseAPIPath + "/current_playlist/discover_tracks", params);
      },
      randomize: function (shuffle) {
        const params = { shuffle: shuffle ? true : false };
        return axios.get(baseAPIPath + "/current_playlist/sort/random", { params });
      },
      resortByIndexes: function (indexes, shuffle) {
        const params = {
          indexes: indexes || [],
          shuffle: shuffle ? true : false,
        };
        return axios.post(baseAPIPath + "/current_playlist/sort/indexes", params);
      },
      removeElementAtIndex: function (index, shuffle) {
        const params = {
          index: index >= 0 ? index : -1,
          shuffle: shuffle ? true : false,
        };
        return axios.post(
          baseAPIPath + "/current_playlist/remove_element_at_index",
          params,
        );
      },
    },
    radioStation: {
      search: function (currentPageIndex, resultsPage, filter) {
        const params = {
          pager: {
            currentPageIndex: currentPageIndex,
            resultsPage: resultsPage,
          },
          filter: filter || {},
        };
        return axios.post(baseAPIPath + "/radio_station/search", params);
      },
    },
    lyrics: {
      get: function (title, artist) {
        return axios.get(
          "api2/lyrics?title=" +
          encodeURIComponent(title || "") +
          "&artist=" +
          encodeURIComponent(artist || ""),
          {},
        );
      },
    },
  };

  return { baseAPIPath, api };
}
