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
  discover: {
    artist: function (
      filter: unknown,
      currentPageIndex: number,
      resultsPage: number,
      sortField: string,
      sortOrder: string,
      skipCount: boolean,
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
        skipCount: skipCount,
      };
      return axiosInstance.post("/discover/artists", params);
    },
    album: function (
      filter: unknown,
      currentPageIndex: number,
      resultsPage: number,
      sortField: string,
      sortOrder: string,
      skipCount: boolean,
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
        skipCount: skipCount,
      };
      return axiosInstance.post("/discover/albums", params);
    },
    path: function (libraryId: string) {
      return axiosInstance.post("/browse/path/" + libraryId);
    },
    libraries: function () {
      return axiosInstance.get("/browse/libraries");
    },
  },
  browse: {
    artist: function (
      filter: unknown,
      currentPageIndex: number,
      resultsPage: number,
      sortField: string,
      sortOrder: string,
      skipCount: boolean,
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
        skipCount: skipCount,
      };
      return axiosInstance.post("/browse/artists", params);
    },
    album: function (
      filter: unknown,
      currentPageIndex: number,
      resultsPage: number,
      sortField: string,
      sortOrder: string,
      skipCount: boolean,
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
        skipCount: skipCount,
      };
      return axiosInstance.post("/browse/albums", params);
    },
    path: function (libraryId: string) {
      return axiosInstance.post("/browse/path/" + libraryId);
    },
    libraries: function () {
      return axiosInstance.get("/browse/libraries");
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
  },
  playList: {
    add(id: string, name: string) {
      return axiosInstance.post(`/playlist/${id}`, { name: name });
    },
    remove(id: string) {
      return axiosInstance.delete(`/playlist/${id}`);
    },
    randomFill(id: string) {
      return axiosInstance.post(`/playlist/${id}/random_fill`);
    },
    getCurrentPlayLists: function () {
      return axiosInstance.get("/current_playlists");
    },
  },
  currentPlayList: {
    get: function () {
      return axiosInstance.get("/current_playlist");
    },
  }
};

export { api };
