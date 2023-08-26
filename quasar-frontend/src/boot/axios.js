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
  artist: {
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
          .post(baseAPIPath + "/artist/search", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    get: function (name) {
      return new Promise((resolve, reject) => {
        axios
          .get("api/2/artist/" + encodeURIComponent(name), {})
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
    search: function (currentPageIndex, resultsPage, randomSort, filter) {
      return new Promise((resolve, reject) => {
        const params = {
          pager: {
            currentPageIndex: currentPageIndex,
            resultsPage: resultsPage,
          },
          sort: {
            random: randomSort,
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
