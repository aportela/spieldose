let browseAlbums = (function () {
    "use strict";

    const template = function () {
        return `
            <div class="container is-fluid box is-marginless">
                <p class="title is-1 has-text-centered">Browse albums</i></p>
                <div v-if="! hasAPIErrors">
                    <div class="field has-addons">
                        <div class="control is-expanded has-icons-left" v-bind:class="{ 'is-loading': loading }">
                            <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="'search album name...'" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                            <input type="text" class="input" placeholder="search album name..." v-else v-bind:disabled="loading" v-model.trim="nameFilter" v-on:keyup.enter="search();">
                            <span class="icon is-small is-left">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <p class="control">
                            <a class="button is-default" v-on:click.prevent="advancedSearch = ! advancedSearch;">
                                <span class="icon">
                                    <i v-if="advancedSearch" class="fas fa-search-minus" aria-hidden="true"></i>
                                    <i v-else="advancedSearch" class="fas fa-search-plus" aria-hidden="true"></i>
                                </span>
                                <span>toggle advanced search</span>
                            </a>
                        </p>
                        <p class="control" v-if="! liveSearch">
                            <a class="button is-info" v-on:click.prevent="search();">
                                <span class="icon">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                </span>
                                <span>search</span>
                            </a>
                        </p>
                    </div>
                    <div class="field has-addons" v-if="advancedSearch">
                        <p class="control has-icons-left">
                            <input class="input" type="text" pattern="[0-9]*" placeholder="year (4 digits)" maxlength="4" v-bind:disabled="loading" v-on:keyup.enter="search(true);" v-model.number="filterByYear" >
                            <span class="icon is-small is-left">
                                <i class="fas fa-calendar"></i>
                            </span>
                        </p>
                        <p class="control is-expanded has-icons-left">
                            <input class="input" type="text" placeholder="search album artist name..." v-bind:disabled="loading" v-on:keyup.enter="search(true);" v-model.trim="filterByArtist">
                            <span class="icon is-small is-left">
                                <i class="fas fa-user"></i>
                            </span>
                        </p>
                        <p class="control">
                            <a class="button is-info" v-on:click="search(true);">
                                <span class="icon">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                </span>
                                <span>search</span>
                            </a>
                        </p>
                    </div>
                    <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
                    <div class="browse-album-item" v-for="album in albums" v-show="! loading">
                        <a class="play-album" title="click to play album" v-on:click.prevent="playAlbumTracks(album.name, album.artist, album.year);">
                            <img class="album-thumbnail" v-bind:src="album.image | getAlbumImageUrl" v-on:error="album.image = null;">
                            <i class="fas fa-play fa-4x"></i>
                            <img class="vinyl no-cover" src="images/vinyl.png" />
                        </a>
                        <div class="album-info">
                            <p class="album-name">{{ album.name }}</p>
                            <p v-if="album.artist" class="artist-name"><a title="click to open artist section" v-on:click.prevent="navigateToArtistPage(album.artist);">by {{ album.artist }}</a><span v-show="album.year"> ({{ album.year }})</span></p>
                            <p v-else class="artist-name">unknown artist</p>
                        </div>
                    </div>
                    <div class="is-clearfix"></div>
                </div>
                <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
            </div>
        `;
    };

    let module = Vue.component('spieldose-browse-albums', {
        template: template(),
        mixins: [
            mixinAPIError, mixinPagination, mixinLiveSearches, mixinNavigation, mixinAlbums, mixinArtists
        ],
        data: function () {
            return ({
                loading: false,
                nameFilter: null,
                albums: [],
                advancedSearch: false,
                filterByArtist: null,
                filterByYear: null
            });
        },
        methods: {
            onPaginationChanged: function (currentPage) {
                this.$router.push({ name: 'albumsPaged', params: { page: currentPage } });
            },
            onTypeahead: function (text) {
                this.nameFilter = text;
                this.search();
            },
            search: function (resetPager) {
                let self = this;
                self.loading = true;
                self.clearAPIErrors();
                if (resetPager) {
                    self.pager.actualPage = 1;
                }
                spieldoseAPI.searchAlbums(self.nameFilter, self.filterByArtist, self.filterByYear, self.pager.actualPage, self.pager.resultsPage, function (response) {
                    if (response.ok) {
                        self.pager.actualPage = response.body.pagination.actualPage;
                        self.pager.totalPages = response.body.pagination.totalPages;
                        self.pager.totalResults = response.body.pagination.totalResults;
                        if (response.body.albums && response.body.albums.length > 0) {
                            self.albums = response.body.albums;
                        } else {
                            self.albums = [];
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            }
        }
    });

    return (module);
})();