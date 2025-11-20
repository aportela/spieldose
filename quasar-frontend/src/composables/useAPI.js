import { useAxios } from "src/composables/useAxios";

const { axios } = useAxios();

const basePath = "/api2";

export function useAPI() {
  const api = {
    common: {
      initialState: () => axios.get(basePath + "/initial_state"),
    },
    auth: {
      login: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.post(basePath + "/auth/login", params);
      },
      logout: () => axios.post(basePath + "/auth/logout"),
      register: function (id, email, password) {
        const params = {
          id: id,
          email: email,
          password: password,
        };
        return axios.post(basePath + "/auth/register", params);
      },
    },
    user: {
      getProfile: () => axios.get(basePath + "/user/profile"),
      updateProfile: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.put(basePath + "/user/profile", params);
      },
    },
    file: {
      getRandom: function () {
        return axios.get(basePath + "/file/rnd");
      },
    },
    /*
    user: {
      getProfile: () => axios.get(basePath + "/user/profile"),
      updateProfile: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.put(basePath + "/user/profile", params);
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
        return axios.post(basePath + "/global_search", params);
      },
    },
    */
    browse: {
      artist: function (
        filter,
        currentPageIndex,
        resultsPage,
        sortField,
        sortOrder,
        skipCount,
      ) {
        let params = {
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
        if (skipCount) {
          params.skipCount = true;
        }
        return axios.post(basePath + "/browse/artist", params);
      },
      album: function (
        filter,
        currentPageIndex,
        resultsPage,
        sortField,
        sortOrder,
        skipCount,
      ) {
        let params = {
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
        if (skipCount) {
          params.skipCount = true;
        }
        return axios.post(basePath + "/browse/album", params);
      },
      path: function (libraryId) {
        return axios.post(basePath + "/browse/path/" + libraryId);
      },
      libraries: function () {
        return axios.get(basePath + "/browse/libraries");
      },
    },
    cloud: {
      getMusicBrainzArtistGenreCloud: function () {
        return axios.get(basePath + "/common/musicbrainz_artist_genre_cloud");
      },
      getLastFMArtistTagCloud: function () {
        return axios.get(basePath + "/common/lastfm_artist_tag_cloud");
      },
    },

    /*

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
     return axios.post(basePath + "/artist/search", params);
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
     return axios.post(basePath + "/album/search", params);
   },
   getSmallRandomCovers: function (count = 32) {
     return axios.get(basePath + "/album/small_random_covers/" + count, {});
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
     return axios.get(basePath + "/track/" + id);
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
     return axios.post(basePath + "/track/search", params);
   },
   increasePlayCount: function (id) {
     return axios.get(basePath + "/track/increase_play_count/" + id);
   },
   setFavorite: function (id) {
     return axios.get(basePath + "/track/set_favorite/" + id);
   },
   unSetFavorite: function (id) {
     return axios.get(basePath + "/track/unset_favorite/" + id);
   },
 },
 path: {
   getTree: function () {
     return axios.get(basePath + "/path/tree");
   },
 },
 metrics: {
   getTracks: function (filter, sortField, count) {
     const params = {
       filter: filter || {},
       sortField: sortField,
       count: count || 5,
     };
     return axios.post(basePath + "/metrics/tracks", params);
   },
   getArtists: function (filter, sortField, count) {
     const params = {
       filter: filter || {},
       sortField: sortField,
       count: count || 5,
     };
     return axios.post(basePath + "/metrics/artists", params);
   },
   getAlbums: function (filter, sortField, count) {
     const params = {
       filter: filter || {},
       sortField: sortField,
       count: count || 5,
     };
     return axios.post(basePath + "/metrics/albums", params);
   },
   getGenres: function (filter, sortField, count) {
     const params = {
       filter: filter || {},
       sortField: sortField,
       count: count || 5,
     };
     return axios.post(basePath + "/metrics/genres", params);
   },
   getDataRanges: function (filter) {
     const params = {
       filter: filter || {},
     };
     return axios.post(basePath + "/metrics/date_range", params);
   },
   getMetricsByUser: function (filter) {
     const params = {
       filter: filter || {},
     };
     return axios.post(basePath + "/metrics/by_user", params);
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
     return axios.post(basePath + "/playlist/search", params);
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
     return axios.post(basePath + "/playlist/add", params);
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
     return axios.post(basePath + "/playlist/update", params);
   },
   delete: function (id) {
     return axios.delete(basePath + "/playlist/" + id);
   },
   get: function (id) {
     return axios.get(basePath + "/playlist/" + id);
   },
 },
 currentPlaylist: {
   get: function () {
     return axios.get(basePath + "/current_playlist");
   },
   setTracks: function (trackIds) {
     const params = {
       trackIds: trackIds || [],
     };
     return axios.post(basePath + "/current_playlist/set_tracks", params);
   },
   appendTracks: function (trackIds) {
     const params = {
       trackIds: trackIds || [],
     };
     return axios.post(basePath + "/current_playlist/append_tracks", params);
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
     return axios.post(basePath + "/current_playlist/set_tracks", params);
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
     return axios.post(basePath + "/current_playlist/append_tracks", params);
   },
   setPlaylist: function (id) {
     const params = {
       playlistId: id || null,
     };
     return axios.post(basePath + "/current_playlist/set_tracks", params);
   },
   appendPlaylist: function (id) {
     const params = {
       playlistId: id || null,
     };
     return axios.post(basePath + "/current_playlist/append_tracks", params);
   },
   setRadioStation: function (id) {
     const params = {
       id: id || null,
     };
     return axios.post(basePath + "/current_playlist/set_radiostation", params);
   },
   setPath: function (id) {
     const params = {
       pathId: id || null,
     };
     return axios.post(basePath + "/current_playlist/set_tracks", params);
   },
   getCurrentElement: function (shuffle) {
     const params = { shuffle: shuffle ? true : false };
     return axios.get(basePath + "/current_playlist/current_element", { params });
   },
   skipToPreviousElement: function (shuffle) {
     const params = { shuffle: shuffle ? true : false };
     return axios.get(basePath + "/current_playlist/previous_element", { params });
   },
   skipToNextElement: function (shuffle) {
     const params = { shuffle: shuffle ? true : false };
     return axios.get(basePath + "/current_playlist/next_element", { params });
   },
   skipToElementAtIndex: function (index) {
     const params = {
       index: index >= 0 ? index : -1,
     };
     return axios.get(basePath + "/current_playlist/element_at_index", { params });
   },
   discover: function (count, shuffle) {
     const params = {
       count: count || 32,
       shuffle: shuffle ? true : false,
     };
     return axios.post(basePath + "/current_playlist/discover_tracks", params);
   },
   randomize: function (shuffle) {
     const params = { shuffle: shuffle ? true : false };
     return axios.get(basePath + "/current_playlist/sort/random", { params });
   },
   resortByIndexes: function (indexes, shuffle) {
     const params = {
       indexes: indexes || [],
       shuffle: shuffle ? true : false,
     };
     return axios.post(basePath + "/current_playlist/sort/indexes", params);
   },
   removeElementAtIndex: function (index, shuffle) {
     const params = {
       index: index >= 0 ? index : -1,
       shuffle: shuffle ? true : false,
     };
     return axios.post(
       basePath + "/current_playlist/remove_element_at_index",
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
     return axios.post(basePath + "/radio_station/search", params);
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
 */
  };

  return { basePath, api };
}
