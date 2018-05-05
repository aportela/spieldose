/**
 * vuejs mixin for live searches
 */
const mixinLiveSearches = {
    computed: {
        liveSearch: function () {
            return (initialState.liveSearch);
        }
    }
};

/**
 * vuejs mixin for albums
 */
const mixinAlbums = {
    filters: {
        albumThumbnailUrlToCacheUrl: function (value) {
            if (value.indexOf("http") == 0) {
                return ("api/thumbnail?url=" + value);
            } else {
                return ("api/thumbnail?hash=" + value);
            }
        }
    }
};