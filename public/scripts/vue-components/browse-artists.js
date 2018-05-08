let browseArtists = (function () {
    "use strict";

    const template = function () {
        return `
            <div class="container is-fluid box is-marginless">
                <p class="title is-1 has-text-centered">Browse artists</i></p>
                <div v-if="! hasAPIErrors">
                    <div class="field is-expanded has-addons">
                        <div class="control is-expanded has-icons-left" v-bind:class="{ 'is-loading': loading }">
                            <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="'search artist name...'" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                            <input type="text" class="input" placeholder="search artist name..." v-else v-bind:disabled="loading" v-model.trim="nameFilter" v-on:keyup.enter="search();">
                            <span class="icon is-small is-left">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <div class="control">
                            <div class="select">
                                <select v-model="filterNotScraped" v-on:change="search();">
                                    <option value="0">All artists</option>
                                    <option value="1">Artists not scraped</option>
                                </select>
                            </div>
                        </div>
                        <p class="control" v-if="! liveSearch">
                            <a class="button is-info" v-on:click.prevent="search();">
                                <span class="icon">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                </span>
                                <span>search</span>
                            </a>
                        </p>
                    </div>
                    <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
                    <div class="browse-artist-item is-pulled-left" v-for="artist in artists" v-show="! loading">
                        <a title="click to open artist section" v-on:click.prevent="navigateToArtistPage(artist.name);">
                            <img v-bind:src="artist.image | getImageUrl" v-on:error="artist.image = null;">
                            <i class="fas fa-search fa-4x"></i>
                        </a>
                        <div class="artist-info is-clipped">
                            <p class="artist-name has-text-centered">{{ artist.name }}</p>
                        </div>
                    </div>
                    <div class="is-clearfix"></div>
                </div>
                <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
            </div>
        `;
    };

    let module = Vue.component('spieldose-browse-artists', {
        template: template(),
        mixins: [
            mixinAPIError, mixinPagination, mixinLiveSearches, mixinNavigation, mixinArtists
        ],
        data: function () {
            return ({
                loading: false,
                nameFilter: null,
                artists: [],
                filterNotScraped: 0
            });
        },
        methods: {
            onPaginationChanged: function (currentPage) {
                this.$router.push({ name: 'artistsPaged', params: { page: currentPage } });
            },
            onTypeahead: function (text) {
                this.nameFilter = text;
                this.search();
            },
            search: function () {
                let self = this;
                self.loading = true;
                self.clearAPIErrors();
                spieldoseAPI.searchArtists(self.nameFilter, self.filterNotScraped == 1, self.pager.actualPage, self.pager.resultsPage, function (response) {
                    if (response.ok) {
                        self.pager.actualPage = response.body.pagination.actualPage;
                        self.pager.totalPages = response.body.pagination.totalPages;
                        self.pager.totalResults = response.body.pagination.totalResults;
                        if (response.body.artists && response.body.artists.length > 0) {
                            self.artists = response.body.artists;
                        } else {
                            self.artists = [];
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