import axios from 'axios';
import { SERVER_API_BASE_PATH } from 'src/constants';
import { useSessionStore } from 'src/stores/session';

const sessionStore = useSessionStore();

const axiosInstance = axios.create({
  baseURL: SERVER_API_BASE_PATH,
});

axiosInstance.interceptors.request.use(
  (config) => {
    if (sessionStore.accessToken) {
      config.headers['Authorization'] = `Bearer ${sessionStore.accessToken}`;
      config.withCredentials = true;
    }
    return config;
  },
  (error) => {
    throw error;
  },
);

axiosInstance.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (!error) {
      throw new Error('Unknown API error');
    } else {
      if (!error.response) {
        error.response = {
          status: 0,
          statusText: 'undefined',
        };
      }
      if (error.response?.status === 401) {
        if (sessionStore.hasAccessToken) {
          sessionStore.removeAccessToken();
        }
      }
      error.isAPIError =
        error.response.headers['content-type'] == 'application/json' &&
        error.response.data.APIError === true;
      error.customAPIErrorDetails = {
        method: error.config?.method || 'N/A',
        url: error.request?.responseURL || 'N/A',
        httpCode: error.response?.status || 'N/A',
        httpStatus: error.response?.statusText || 'Unknown error',
        contentType: error.response.headers['content-type'],
        request: {
          params: {
            query: error.config.params || null,
            data: error.config.data || null,
          },
        },
        response: error.response.data || null,
      };
      throw error;
    }
  },
);

const bgDownload = async (url: string, fileName: string = 'fileName') => {
  const startTime = Date.now();
  const response = await axiosInstance.get(url, {
    responseType: 'blob',
  });
  const blob = new Blob([response.data]);
  const tmpLink = document.createElement('a');
  const urlBlob = URL.createObjectURL(blob);
  tmpLink.href = urlBlob;
  tmpLink.download = fileName;
  document.body.appendChild(tmpLink);
  tmpLink.click();
  document.body.removeChild(tmpLink);
  URL.revokeObjectURL(urlBlob);
  const endTime = Date.now();
  return {
    fileName: fileName,
    url: url,
    mimeType: blob.type,
    length: blob.size,
    msTime: endTime - startTime,
  };
};

export { axiosInstance, bgDownload };
