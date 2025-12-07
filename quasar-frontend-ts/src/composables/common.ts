import { SERVER_API_BASE_PATH } from "src/constants";

const buildDownloadTrackURL = (trackFileId: string) => {
  return (`/${SERVER_API_BASE_PATH}/file/download/${trackFileId}`);
}

export { buildDownloadTrackURL };
