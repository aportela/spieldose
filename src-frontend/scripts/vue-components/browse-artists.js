import { default as spieldoseAPI } from '../api.js';
import { mixinPagination, mixinLiveSearches } from '../mixins.js';
import { default as inputTypeAHead } from './input-typeahead.js';
import { default as pagination } from './pagination';
import { default as imageArtist } from './image-artist.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
            <p class="title is-1 has-text-centered">{{ $t("browseArtists.labels.sectionName") }}</p>
            <div>
                <div class="field is-expanded has-addons">
                    <div class="control is-expanded has-icons-left" :class="{ 'is-loading': loading }">
                        <spieldose-input-typeahead v-if="liveSearch" :loading="loading" :placeholder="$t('browseArtists.inputs.artistNamePlaceholder')" @on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" :placeholder="$t('browseArtists.inputs.artistNamePlaceholder')" v-else :disabled="loading" v-model.trim="nameFilter" @keyup.enter="search();">
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
                        <a class="button is-info" @click.prevent="search();">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browseArtists.buttons.search") }}</span>
                        </a>
                    </p>
                </div>
                <spieldose-pagination :loading="loading" :data="pager" @pagination-changed="onPaginationChanged"></spieldose-pagination>
                <div class="browse-artist-item is-pulled-left" v-for="artist in artists" :key="artist.name" v-show="! loading">
                    <router-link :to="{ name: 'artist', params: { artist: artist.name }}" :title="$t('commonLabels.navigateToArtistPage')">
                        <spieldose-image-artist :src="artist.image"></spieldose-image-artist>
                        <i class="fas fa-search fa-4x"></i>
                    </router-link>
                    <div class="artist-info is-clipped">
                        <p class="artist-name has-text-centered">{{ artist.name }}</p>
                    </div>
                </div>
                <div class="is-clearfix"></div>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-browse-artists',
    template: template(),
    mixins: [
        mixinPagination, mixinLiveSearches
    ],
    data: function () {
        return ({
            loading: false,
            nameFilter: null,
            artists: [],
            filterNotScraped: 0,
            resetPager: false
        });
    },
    watch: {
        filterNotScraped: function (v) {
            if (this.pager.actualPage > 1) {
                this.resetPager = true;
            }
            if (this.liveSearch) {
                this.search();
            }
        },
        nameFilter: function (newValue) {
            if (this.pager.actualPage > 1) {
                this.resetPager = true;
            }
        }
    },
    components: {
        'spieldose-input-typeahead': inputTypeAHead,
        'spieldose-pagination': pagination,
        'spieldose-image-artist': imageArtist
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
            if (this.resetPager) {
                this.pager.actualPage = 1;
            }
            spieldoseAPI.artist.search(this.nameFilter, this.filterNotScraped == 1, this.pager.actualPage, this.pager.resultsPage, (response) => {
                if (response.status == 200) {
                    this.pager.actualPage = response.data.pagination.actualPage;
                    this.pager.totalPages = response.data.pagination.totalPages;
                    this.pager.totalResults = response.data.pagination.totalResults;
                    if (response.data.artists && response.data.artists.length > 0) {
                        this.artists = response.data.artists;
                    } else {
                        this.artists = [];
                    }
                } else {
                    // TODO: show error
                    console.error(response);
                }
                this.loading = false;
            });
        }
    }
}