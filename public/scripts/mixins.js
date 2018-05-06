/**
 * live searches common mixins
 */
const mixinLiveSearches = {
    computed: {
        liveSearch: function () {
            return (initialState.liveSearch);
        }
    }
};

/**
 * album entity common mixins
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

/**
 * player common mixins
 */
const mixinPlayer = {
    data: function () {
        return ({
            playerData: sharedPlayerData,
        });
    }
};

/**
 * validator common mixins
 */
const mixinValidations = {
    data: function () {
        return ({
            validator: getValidator()
        });
    }
};

/**
 * api error common mixins
 */
const mixinAPIError = {
    data: function () {
        return ({
            apiError: null
        });
    }, computed: {
        hasAPIErrors() {
            return (this.apiError != null);
        }
    }, methods: {
        clearAPIErrors() {
            this.apiError = null;
        },
        setAPIError(err) {
            this.apiError = err;
        }
    }
};

/**
 * pagination common mixins
 */
const mixinPagination = {
    data: function () {
        return ({
            pager: {
                actualPage: 1,
                previousPage: 1,
                nextPage: 1,
                totalPages: 0,
                resultsPage: initialState.defaultResultsPage
            }
        });
    }
};

/**
 * top & recent dashboard charts mixins
 */
const mixinTopRecentCharts = {
    data: function () {
        return ({
            items: []
        });
    },
    created: function () {
        this.load();
    },
    computed: {
        hasItems: function () {
            return (this.items && this.items.length > 0);
        }
    },
    methods: {
        navigateToArtistPage: function (artist) {
            if (artist) {
                this.$router.push({ name: 'artist', params: { artist: artist } });
            }
        },
        playTrack: function (track) {
            this.playerData.replace([track]);
        },
        enqueueTrack: function (track) {
            this.playerData.enqueue([track]);
        }
    }
};