import {
  THUMBNAIL_API_URL,
  SMALL_THUMBNAIL_WIDTH,
  SMALL_THUMBNAIL_HEIGHT,
  SMALL_THUMBNAIL_QUALITY,
  MEDIUM_THUMBNAIL_WIDTH,
  MEDIUM_THUMBNAIL_HEIGHT,
  MEDIUM_THUMBNAIL_QUALITY,
} from "../constants";

const getURL = (imageURL, width, height, quality) => {
  const url = new URL(
    `${window.location.protocol}//${window.location.hostname}${window.location.port ? `:${window.location.port}` : ""}${THUMBNAIL_API_URL}`,
  );
  const params = new URLSearchParams();
  params.set("width", width);
  params.set("height", height);
  params.set("quality", quality);
  params.set("url", imageURL);
  url.search = params.toString();
  return url.toString();
};

const getSmallURL = (imageURL) => {
  return getURL(
    imageURL,
    SMALL_THUMBNAIL_WIDTH,
    SMALL_THUMBNAIL_HEIGHT,
    SMALL_THUMBNAIL_QUALITY,
  );
};

const getMediumURL = (imageURL) => {
  return getURL(
    imageURL,
    MEDIUM_THUMBNAIL_WIDTH,
    MEDIUM_THUMBNAIL_HEIGHT,
    MEDIUM_THUMBNAIL_QUALITY,
  );
};

export { getSmallURL, getMediumURL };
