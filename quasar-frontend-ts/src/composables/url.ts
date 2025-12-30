import { SERVER_API_BASE_PATH } from 'src/constants';

const getDownloadFileURL = (
  fileId: string | null,
  prependBaseAPI: boolean = true,
): string | null => {
  if (fileId) {
    if (prependBaseAPI) {
      return `/${SERVER_API_BASE_PATH}/file/${fileId}/download`;
    } else {
      return `/file/${fileId}/download`;
    }
  } else {
    return null;
  }
};

const getStreamFileURL = (fileId: string | null): string | null => {
  if (fileId) {
    return `/${SERVER_API_BASE_PATH}/file/${fileId}/raw`;
  } else {
    return null;
  }
};

export { getDownloadFileURL, getStreamFileURL };
