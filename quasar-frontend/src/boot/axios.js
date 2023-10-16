import { boot } from "quasar/wrappers";
import { useSessionStore } from "stores/session";
import axios from "axios";

const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}
axios.interceptors.request.use((config) => {
  if (session.getJWT) {
    config.headers["SPIELDOSE-JWT"] = session.getJWT;
    config.withCredentials = true;
  }
  return config;
});

axios.interceptors.response.use(
  (response) => {
    // warning: axios lowercase received header names
    const apiResponseJWT = response.headers["spieldose-jwt"] || null;
    if (apiResponseJWT) {
      if (apiResponseJWT && apiResponseJWT != session.getJWT) {
        session.signIn(apiResponseJWT);
      }
    }
    return response;
  },
  (error) => {
    if (!error) {
      return Promise.reject({
        response: {
          status: 0,
          statusText: "undefined",
        },
      });
    } else {
      if (!error.response) {
        error.response = {
          status: 0,
          statusText: "undefined",
        };
      }
      return Promise.reject(error);
    }
  }
);

const baseAPIPath = "api/2";

const api = {
  common: {
    initialState: function () {
      return new Promise((resolve, reject) => {
        axios
          .get("api/2/initial_state", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  user: {
    signIn: function (email, password, name) {
      return new Promise((resolve, reject) => {
        const params = {
          email: email,
          password: password,
          name: name,
        };
        axios
          .post(baseAPIPath + "/user/sign-in", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    signOut: function () {
      return new Promise((resolve, reject) => {
        axios
          .post(baseAPIPath + "/user/sign-out", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    signUp: function (id, email, password, name) {
      return new Promise((resolve, reject) => {
        const params = {
          id: id,
          email: email,
          password: password,
          name: name,
        };
        axios
          .post(baseAPIPath + "/user/sign-up", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  globalSearch: {
    search: function (
      filter,
      currentPageIndex,
      resultsPage,
      randomSort,
      sortField,
      sortOrder
    ) {
      return new Promise((resolve, reject) => {
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
        axios
          .post(baseAPIPath + "/global_search", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  artist: {
    search: function (
      filter,
      currentPageIndex,
      resultsPage,
      sortField,
      sortOrder
    ) {
      return new Promise((resolve, reject) => {
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
        axios
          .post(baseAPIPath + "/artist/search", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    get: function (mbId, name) {
      return new Promise((resolve, reject) => {
        axios
          .get(
            "api/2/artist?mbId=" +
              encodeURIComponent(mbId || "") +
              "&name=" +
              encodeURIComponent(name || ""),
            {}
          )
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  artistGenres: {
    get: function () {
      return new Promise((resolve, reject) => {
        axios
          .get(baseAPIPath + "/artists_genres")
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  album: {
    search: function (
      filter,
      currentPageIndex,
      resultsPage,
      sortField,
      sortOrder
    ) {
      return new Promise((resolve, reject) => {
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
        axios
          .post(baseAPIPath + "/album/search", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    getSmallRandomCovers: function (count = 32) {
      return new Promise((resolve, reject) => {
        axios
          .get(baseAPIPath + "/album/small_random_covers/" + count, {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    get: function (mbId, title, artistMbId, artistName, year) {
      return new Promise((resolve, reject) => {
        axios
          .get(
            "api/2/album?mbId=" +
              encodeURIComponent(mbId || "") +
              "&title=" +
              encodeURIComponent(title || ""),
            "&artistMbId=" + encodeURIComponent(artistMbId || ""),
            "&artistName=" + encodeURIComponent(artistName || ""),
            "&year=" + encodeURIComponent(year || ""),
            {}
          )
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  track: {
    get: function (id) {
      return new Promise((resolve, reject) => {
        axios
          .get(baseAPIPath + "/track/" + id)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    search: function (
      filter,
      currentPageIndex,
      resultsPage,
      randomSort,
      sortField,
      sortOrder
    ) {
      return new Promise((resolve, reject) => {
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
        axios
          .post(baseAPIPath + "/track/search", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    increasePlayCount: function (id) {
      return new Promise((resolve, reject) => {
        axios
          .get(baseAPIPath + "/track/increase_play_count/" + id)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    setFavorite: function (id) {
      return new Promise((resolve, reject) => {
        axios
          .get(baseAPIPath + "/track/set_favorite/" + id)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    unSetFavorite: function (id) {
      return new Promise((resolve, reject) => {
        axios
          .get(baseAPIPath + "/track/unset_favorite/" + id)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  path: {
    getTree: function () {
      return new Promise((resolve, reject) => {
        axios
          .get(baseAPIPath + "/path/tree")
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  metrics: {
    getTracks: function (filter, sortField, count) {
      const params = {
        filter: filter || {},
        sortField: sortField,
        count: count || 5,
      };
      return new Promise((resolve, reject) => {
        axios
          .post(baseAPIPath + "/metrics/tracks", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    getArtists: function (filter, sortField, count) {
      const params = {
        filter: filter || {},
        sortField: sortField,
        count: count || 5,
      };
      return new Promise((resolve, reject) => {
        axios
          .post(baseAPIPath + "/metrics/artists", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    getAlbums: function (filter, sortField, count) {
      const params = {
        filter: filter || {},
        sortField: sortField,
        count: count || 5,
      };
      return new Promise((resolve, reject) => {
        axios
          .post(baseAPIPath + "/metrics/albums", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    getGenres: function (filter, sortField, count) {
      const params = {
        filter: filter || {},
        sortField: sortField,
        count: count || 5,
      };
      return new Promise((resolve, reject) => {
        axios
          .post(baseAPIPath + "/metrics/genres", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    getDataRanges: function (filter) {
      const params = {
        filter: filter || {},
      };
      return new Promise((resolve, reject) => {
        axios
          .post(baseAPIPath + "/metrics/date_range", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    getMetricsByUser: function (filter) {
      const params = {
        filter: filter || {},
      };
      return new Promise((resolve, reject) => {
        axios
          .post(baseAPIPath + "/metrics/by_user", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  playlist: {
    search: function (
      filter,
      currentPageIndex,
      resultsPage,
      sortField,
      sortOrder
    ) {
      return new Promise((resolve, reject) => {
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
        axios
          .post(baseAPIPath + "/playlist/search", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    add: function (id, name, tracks, isPublic) {
      return new Promise((resolve, reject) => {
        const params = {
          playlist: {
            id: id,
            name: name,
            tracks: tracks,
            public: isPublic,
          },
        };
        axios
          .post(baseAPIPath + "/playlist/add", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    delete: function (id) {
      return new Promise((resolve, reject) => {
        axios
          .delete(baseAPIPath + "/playlist/" + id)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    get: function (id) {
      return new Promise((resolve, reject) => {
        axios
          .get(baseAPIPath + "/playlist/" + id)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  currentPlaylist: {
    get: function () {
      return new Promise((resolve, reject) => {
        axios
          .get(baseAPIPath + "/current_playlist")
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    setTracks: function (trackIds) {
      return new Promise((resolve, reject) => {
        const params = {
          trackIds: trackIds || [],
        };
        axios
          .post(baseAPIPath + "/current_playlist/set_tracks", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    appendTracks: function (trackIds) {
      return new Promise((resolve, reject) => {
        const params = {
          trackIds: trackIds || [],
        };
        axios
          .post(baseAPIPath + "/current_playlist/append_tracks", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    setAlbum: function (mbId, title, artistMBId, artistName, year) {
      return new Promise((resolve, reject) => {
        const params = {
          album: {
            mbId: mbId || null,
            title: title || null,
            artist: { mbId: artistMBId || null, name: artistName || null },
            year: year || null,
          },
        };
        axios
          .post(baseAPIPath + "/current_playlist/set_tracks", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    appendAlbum: function (mbId, title, artistMBId, artistName, year) {
      return new Promise((resolve, reject) => {
        const params = {
          album: {
            mbId: mbId || null,
            title: title || null,
            artist: { mbId: artistMBId || null, name: artistName || null },
            year: year || null,
          },
        };
        axios
          .post(baseAPIPath + "/current_playlist/append_tracks", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    setPlaylist: function (id) {
      return new Promise((resolve, reject) => {
        const params = {
          playlistId: id || null,
        };
        axios
          .post(baseAPIPath + "/current_playlist/set_tracks", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    appendPlaylist: function (id) {
      return new Promise((resolve, reject) => {
        const params = {
          playlistId: id || null,
        };
        axios
          .post(baseAPIPath + "/current_playlist/append_tracks", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    setRadioStation: function (id) {
      return new Promise((resolve, reject) => {
        const params = {
          id: id || null,
        };
        axios
          .post(baseAPIPath + "/current_playlist/set_radiostation", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    getCurrentElement: function (shuffle) {
      return new Promise((resolve, reject) => {
        const params = { shuffle: shuffle ? true : false };
        axios
          .get(baseAPIPath + "/current_playlist/current_element", { params })
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    skipToPreviousElement: function (shuffle) {
      return new Promise((resolve, reject) => {
        const params = { shuffle: shuffle ? true : false };
        axios
          .get(baseAPIPath + "/current_playlist/previous_element", { params })
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    skipToNextElement: function (shuffle) {
      return new Promise((resolve, reject) => {
        const params = { shuffle: shuffle ? true : false };
        axios
          .get(baseAPIPath + "/current_playlist/next_element", { params })
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    skipToElementAtIndex: function (index, shuffle) {
      return new Promise((resolve, reject) => {
        const params = {
          index: index >= 0 ? index : -1,
          shuffle: shuffle ? true : false,
        };
        axios
          .get(baseAPIPath + "/current_playlist/element_at_index", { params })
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    discover: function (count, shuffle) {
      return new Promise((resolve, reject) => {
        const params = {
          count: count || 32,
          shuffle: shuffle ? true : false,
        };
        axios
          .post(baseAPIPath + "/current_playlist/discover_tracks", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    randomize: function (shuffle) {
      return new Promise((resolve, reject) => {
        const params = { shuffle: shuffle ? true : false };
        axios
          .get(baseAPIPath + "/current_playlist/sort/random", { params })
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    resortByIndexes: function (indexes, shuffle) {
      return new Promise((resolve, reject) => {
        const params = {
          indexes: indexes || [],
          shuffle: shuffle ? true : false,
        };
        axios
          .post(baseAPIPath + "/current_playlist/sort/indexes", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    removeElementAtIndex: function (index, shuffle) {
      return new Promise((resolve, reject) => {
        const params = {
          index: index >= 0 ? index : -1,
          shuffle: shuffle ? true : false,
        };
        axios
          .post(
            baseAPIPath + "/current_playlist/remove_element_at_index",
            params
          )
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  radioStation: {
    search: function (currentPageIndex, resultsPage, filter) {
      return new Promise((resolve, reject) => {
        const params = {
          pager: {
            currentPageIndex: currentPageIndex,
            resultsPage: resultsPage,
          },
          filter: filter || {},
        };
        axios
          .post(baseAPIPath + "/radio_station/search", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  lyrics: {
    get: function (title, artist) {
      return new Promise((resolve, reject) => {
        axios
          .get(
            "api/2/lyrics?title=" +
              encodeURIComponent(title || "") +
              "&artist=" +
              encodeURIComponent(artist || ""),
            {}
          )
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
};

export default boot(({ app }) => {
  // for use inside Vue files (Options API) through this.$axios and this.$api
  app.config.globalProperties.$axios = axios;
  // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
  //       so you won't necessarily have to import axios in each vue file
  app.config.globalProperties.$api = api;
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
});

export { axios, api };
