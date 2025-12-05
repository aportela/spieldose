import { axiosInstance } from "src/composables/axios";

interface LoginParams {
  email: string;
  password: string;
}

interface RegisterParams {
  id: string;
  email: string;
  password: string;
}

const api = {
  common: {
    getServerEnvironment: () => axiosInstance.get("/server_environment"),
  },
  auth: {
    login: (email: string, password: string) => {
      const params: LoginParams = { email, password };
      return axiosInstance.post("/auth/login", params);
    },

    renewAccessToken: () => axiosInstance.post("/auth/renew_access_token"),

    logout: () => axiosInstance.post("/auth/logout"),

    register: (id: string, email: string, password: string) => {
      const params: RegisterParams = { id, email, password };
      return axiosInstance.post("/auth/register", params);
    },
  },
  user: {
    getProfile: () => axiosInstance.get("/user/profile"),
    setProfile: function (email: string, password: string) {
      const params = {
        email: email,
        password: password,
      };
      return axiosInstance.put("/user/profile", params);
    },
  },
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
      return axiosInstance.post("/browse/artist", params);
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
      return axiosInstance.post("/browse/album", params);
    },
    path: function (libraryId) {
      return axiosInstance.post("/browse/path/" + libraryId);
    },
    libraries: function () {
      return axiosInstanceget("/browse/libraries");
    },
  },
  file: {
    getRandom: function () {
      return axiosInstance.get("/file/rnd");
    },
  },
  track: {
    setFavorite: function (id: string) {
      return axiosInstance.get(`/track/${id}/set_favorite`);
    },
    unSetFavorite: function (id: string) {
      return axiosInstance.get(`/track/${id}/unset_favorite`);
    },
  }
};

export { api };
