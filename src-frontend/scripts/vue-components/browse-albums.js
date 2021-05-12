import { default as spieldoseAPI } from '../api.js';
import { mixinPagination, mixinLiveSearches, mixinPlayer } from '../mixins.js';
import { default as inputTypeAHead } from './input-typeahead.js';
import { default as pagination } from './pagination';
import { default as imageAlbum } from './image-album.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
            <p class="title is-1 has-text-centered">{{ $t("browseAlbums.labels.sectionName") }}</p>
            <div>
                <div class="field has-addons">
                    <div class="control is-expanded has-icons-left" :class="{ 'is-loading': loading }">
                        <spieldose-input-typeahead v-if="liveSearch" :loading="loading" :placeholder="$t('browseAlbums.inputs.albumNamePlaceholder')" @on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" :placeholder="$t('browseAlbums.inputs.albumNamePlaceholder')" v-else :disabled="loading" v-model.trim="nameFilter" @keyup.enter="search();">
                        <span class="icon is-small is-left">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <p class="control">
                        <button type="button" class="button is-default" @click.prevent="advancedSearch = ! advancedSearch;">
                            <span class="icon">
                                <i v-if="advancedSearch" class="fas fa-search-minus" aria-hidden="true"></i>
                                <i v-else="advancedSearch" class="fas fa-search-plus" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browseAlbums.buttons.toggleAdvancedSearch") }}</span>
                        </button>
                    </p>
                    <p class="control" v-if="! liveSearch">
                        <a class="button is-info" @click.prevent="search();">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browseAlbums.buttons.search") }}</span>
                        </a>
                    </p>
                </div>
                <div class="field has-addons" v-if="advancedSearch">
                    <p class="control has-icons-left">
                        <input class="input" type="text" pattern="[0-9]*" :placeholder="$t('browseAlbums.inputs.yearPlaceholder')" maxlength="4" :disabled="loading" @keyup.enter="search(true);" v-model.number="filterByYear" >
                        <span class="icon is-small is-left">
                            <i class="fas fa-calendar"></i>
                        </span>
                    </p>
                    <p class="control is-expanded has-icons-left">
                        <input class="input" type="text" :placeholder="$t('browseAlbums.inputs.artistNamePlaceholder')" :disabled="loading" @keyup.enter="search(true);" v-model.trim="filterByArtist">
                        <span class="icon is-small is-left">
                            <i class="fas fa-user"></i>
                        </span>
                    </p>
                    <p class="control">
                        <a class="button is-info" @click="search(true);">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browseAlbums.buttons.search") }}</span>
                        </a>
                    </p>
                </div>
                <spieldose-pagination :loading="loading" :data="pager" @pagination-changed="onPaginationChanged"></spieldose-pagination>
                <div class="browse-album-item" v-for="album in albums" :key="album.name+album.artist+album.year" v-show="! loading">
                    <a class="play-album" :title="$t('commonLabels.playThisAlbum')" @click.prevent="playAlbumTracks(album.name, album.artist, album.year);">
                        <spieldose-image-album :src="album.image"></spieldose-image-album>
                        <i class="fas fa-play fa-4x"></i>
                        <img class="vinyl no-cover" src="images/vinyl.png" />
                    </a>
                    <div class="album-info">
                        <p class="album-name">{{ album.name }}</p>
                        <p v-if="album.artist" class="artist-name">{{ $t("commonLabels.by") }}
                            <router-link :title="$t('commonLabels.navigateToArtistPage')" :to="{ name: 'artist', params: { artist: album.artist }}">{{ album.artist }}</router-link>
                            <span v-show="album.year"> ({{ album.year }})</span>
                        </p>
                        <p v-else class="artist-name">{{ $t("commonLabels.by") }} {{ $t("browseAlbums.labels.unknownArtist") }} <span v-show="album.year"> ({{ album.year }})</span></p>
                    </div>
                </div>
                <div class="is-clearfix"></div>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-browse-albums',
    template: template(),
    mixins: [
        mixinPagination, mixinLiveSearches, mixinPlayer
    ],
    data: function () {
        return ({
            loading: false,
            nameFilter: null,
            albums: [],
            advancedSearch: false,
            filterByArtist: null,
            filterByYear: null,
            resetPager: false
        });
    },
    watch: {
        filterByArtist: function (newValue) {
            if (this.pager.actualPage > 1) {
                this.resetPager = true;
            }
        },
        filterByYear: function (newValue) {
            if (this.pager.actualPage > 1) {
                this.resetPager = true;
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
        'spieldose-image-album': imageAlbum
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
            this.loading = true;
            if (this.resetPager) {
                this.pager.actualPage = 1;
                this.resetPager = false;
            }
            spieldoseAPI.album.search(this.nameFilter, this.filterByArtist, this.filterByYear, this.pager.actualPage, this.pager.resultsPage, (response) => {
                if (response.status == 200) {
                    this.pager.actualPage = response.data.pagination.actualPage;
                    this.pager.totalPages = response.data.pagination.totalPages;
                    this.pager.totalResults = response.data.pagination.totalResults;
                    if (response.data.albums && response.data.albums.length > 0) {
                        this.albums = response.data.albums;
                    } else {
                        this.albums = [];
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