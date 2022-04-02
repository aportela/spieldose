import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPagination, mixinLiveSearches, mixinNavigation, mixinAlbums, mixinArtists, mixinPlayer } from '../mixins.js';
import { default as inputTypeAHead } from './input-typeahead.js';
import { default as pagination } from './pagination';
import { default as apiError } from './api-error.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
        <p class="title is-1 has-text-centered">{{ $t("browseAlbums.labels.sectionName") }}</p>
            <div v-if="! hasAPIErrors">
                <div class="field has-addons">
                    <div class="control is-expanded has-icons-left" v-bind:class="{ 'is-loading': loading }">
                        <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="$t('browseAlbums.inputs.albumNamePlaceholder')" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" v-bind:placeholder="$t('browseAlbums.inputs.albumNamePlaceholder')" v-else v-bind:disabled="loading" v-model.trim="nameFilter" v-on:keyup.enter="search();">
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
                            <span>{{ $t("browseAlbums.buttons.toggleAdvancedSearch") }}</span>
                        </a>
                    </p>
                    <p class="control" v-if="! liveSearch">
                        <a class="button is-info" v-on:click.prevent="search();">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browseAlbums.buttons.search") }}</span>
                        </a>
                    </p>
                </div>
                <div class="field has-addons" v-if="advancedSearch">
                    <p class="control has-icons-left">
                        <input class="input" type="text" pattern="[0-9]*" v-bind:placeholder="$t('browseAlbums.inputs.yearPlaceholder')" maxlength="4" v-bind:disabled="loading" v-on:keyup.enter="search(true);" v-model.number="filterByYear" >
                        <span class="icon is-small is-left">
                            <i class="fas fa-calendar"></i>
                        </span>
                    </p>
                    <p class="control is-expanded has-icons-left">
                        <input class="input" type="text" v-bind:placeholder="$t('browseAlbums.inputs.artistNamePlaceholder')" v-bind:disabled="loading" v-on:keyup.enter="search(true);" v-model.trim="filterByArtist">
                        <span class="icon is-small is-left">
                            <i class="fas fa-user"></i>
                        </span>
                    </p>
                    <p class="control">
                        <a class="button is-info" v-on:click="search(true);">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browseAlbums.buttons.search") }}</span>
                        </a>
                    </p>
                </div>
                <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
                <div class="browse-album-item" v-for="album in albums" v-show="! loading">
                    <a class="play-album" v-bind:title="$t('commonLabels.playThisAlbum')" v-on:click.prevent="playAlbumTracks(album.name, album.artist, album.year);">
                        <img class="album-thumbnail" v-bind:src="album.image | getAlbumImageUrl" v-on:error="album.image = null;">
                        <i class="fas fa-play fa-4x"></i>
                        <img class="vinyl no-cover" src="images/vinyl.png" />
                    </a>
                    <div class="album-info">
                        <p class="album-name">{{ album.name }}</p>
                        <p v-if="album.artist" class="artist-name">{{ $t("commonLabels.by") }} <a v-bind:title="$t('commonLabels.navigateToArtistPage')" v-on:click.prevent="navigateToArtistPage(album.artist);">{{ album.artist }}</a><span v-show="album.year"> ({{ album.year }})</span></p>
                        <p v-else class="artist-name">{{ $t("commonLabels.by") }} {{ $t("browseAlbums.labels.unknownArtist") }} <span v-show="album.year"> ({{ album.year }})</span></p>
                    </div>
                </div>
                <div class="is-clearfix"></div>
            </div>
            <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
        </div>
    `;
};

export default {
    name: 'spieldose-browse-albums',
    template: template(),
    mixins: [
        mixinAPIError, mixinPagination, mixinLiveSearches, mixinNavigation, mixinAlbums, mixinArtists, mixinPlayer
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
    components: {
        'spieldose-input-typeahead': inputTypeAHead,
        'spieldose-pagination': pagination,
        'spieldose-api-error-component': apiError
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
            spieldoseAPI.album.search(self.nameFilter, self.filterByArtist, self.filterByYear, self.pager.actualPage, self.pager.resultsPage, function (response) {
                if (response.status == 200) {
                    self.pager.actualPage = response.data.pagination.actualPage;
                    self.pager.totalPages = response.data.pagination.totalPages;
                    self.pager.totalResults = response.data.pagination.totalResults;
                    if (response.data.albums && response.data.albums.length > 0) {
                        self.albums = response.data.albums;
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
}