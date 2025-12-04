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
