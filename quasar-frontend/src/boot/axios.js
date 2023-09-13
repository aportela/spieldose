import { boot } from "quasar/wrappers";

import axios from "axios";
import { useSessionStore } from "stores/session";

const session = useSessionStore();
const jwt = session.getJWT;

axios.interceptors.request.use(
  (config) => {
    if (jwt) {
      config.headers["SPIELDOSE-JWT"] = jwt;
      config.withCredentials = true;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

axios.interceptors.response.use(
  (response) => {
    // warning: axios lowercase received header names
    const apiResponseJWT = response.headers["spieldose-jwt"] || null;
    if (apiResponseJWT) {
      if (apiResponseJWT && apiResponseJWT != jwt) {
        session.signIn(apiResponseJWT);
      }
    }
    return response;
  },
  (error) => {
    // helper for checking invalid fields on api response
    error.isFieldInvalid = function (fieldName) {
      return error.response.data.invalidOrMissingParams.indexOf(fieldName) > -1;
    };
    error.response.getApiErrorData = function () {
      return JSON.stringify(
        {
          url: error.request.responseURL,
          response: error.response,
        },
        null,
        "\t"
      );
    };
    return Promise.reject(error);
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
    signIn: function (email, password) {
      return new Promise((resolve, reject) => {
        const params = {
          email: email,
          password: password,
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
    signUp: function (id, email, password) {
      return new Promise((resolve, reject) => {
        const params = {
          id: id,
          email: email,
          password: password,
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
  },
  playlist: {
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
