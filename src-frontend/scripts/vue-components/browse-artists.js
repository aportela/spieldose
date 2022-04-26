import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPagination, mixinLiveSearches, mixinNavigation, mixinArtists } from '../mixins.js';
import { default as inputTypeAHead } from './input-typeahead.js';
import { default as pagination } from './pagination';
import { default as apiError } from './api-error.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
            <p class="title is-1 has-text-centered">{{ $t("browseArtists.labels.sectionName") }}</p>
            <div v-if="! hasAPIErrors">
                <div class="field is-expanded has-addons">
                    <div class="control is-expanded has-icons-left" v-bind:class="{ 'is-loading': loading }">
                        <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="$t('browseArtists.inputs.artistNamePlaceholder')" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" placeholder="$t('browseArtists.inputs.artistNamePlaceholder')" v-else v-bind:disabled="loading" v-model.trim="nameFilter" v-on:keyup.enter="search();">
                        <span class="icon is-small is-left">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <div class="control">
                        <div class="select">
                            <select v-model="filterNotScraped">
                                <option value="0">{{ $t("browseArtists.dropdowns.filterAllArtists") }}</option>
                                <option value="1">{{ $t("browseArtists.dropdowns.filterNotScrapedArtists") }}</option>
                            </select>
                        </div>
                    </div>
                    <p class="control" v-if="! liveSearch">
                        <a class="button is-info" v-on:click.prevent="search();">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browseArtists.buttons.search") }}</span>
                        </a>
                    </p>
                </div>
                <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
                <div class="browse-artist-item is-pulled-left" v-for="artist in artists" v-show="! loading">
                    <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(artist.name);">
                        <img v-bind:src="artist.image | getArtistImageUrl" v-on:error="artist.image = null;">
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

export default {
    name: 'spieldose-browse-artists',
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
    watch: {
        filterNotScraped: function (v) {
            if (this.liveSearch) {
                this.search();
            }
        }
    },
    components: {
        'spieldose-input-typeahead': inputTypeAHead,
        'spieldose-pagination': pagination,
        'spieldose-api-error-component': apiError
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
            this.loading = true;
            this.clearAPIErrors();
            spieldoseAPI.artist.search(this.nameFilter, this.filterNotScraped == 1, this.pager.actualPage, this.pager.resultsPage).then(response => {
                this.pager.actualPage = response.data.pagination.actualPage;
                this.pager.totalPages = response.data.pagination.totalPages;
                this.pager.totalResults = response.data.pagination.totalResults;
                if (response.data.artists && response.data.artists.length > 0) {
                    this.artists = response.data.artists;
                } else {
                    this.artists = [];
                }
                this.loading = false;
            }).catch(error => {
                this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        }
    }
}