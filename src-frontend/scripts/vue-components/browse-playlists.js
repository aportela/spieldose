import { default as spieldoseAPI } from '../api.js';
import { mixinPagination, mixinLiveSearches, mixinPlayer } from '../mixins.js';
import { default as inputTypeAHead } from './input-typeahead.js';
import { default as pagination } from './pagination';
import { default as deleteConfirmationModal } from './delete-confirmation-modal.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
            <p class="title is-1 has-text-centered">{{ $t("browsePlaylists.labels.sectionName") }}</p>
            <div>
                <div class="field has-addons">
                    <div class="control is-expanded has-icons-left" :class="{ 'is-loading': loading }">
                        <spieldose-input-typeahead v-if="liveSearch" :loading="loading" :placeholder="$t('browsePlaylists.inputs.playlistNamePlaceholder')" @on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" :placeholder="$t('browsePlaylists.inputs.playlistNamePlaceholder')" v-else :disabled="loading" v-model.trim="nameFilter" @keyup.enter="search();">
                        <span class="icon is-small is-left">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <p class="control" v-if="! liveSearch">
                        <button type="button" class="button is-dark" :disabled="loading" @click.prevent="search();">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browsePlaylists.buttons.search") }}</span>
                        </button>
                    </p>
                </div>
                <spieldose-pagination :loading="loading" :data="pager" @pagination-changed="onPaginationChanged"></spieldose-pagination>
                <div class="playlist-item box has-text-centered" v-for="playlist in playlists" v-show="! loading">
                    <p class="playlist-item-icon">
                        <span class="icon has-text-light">
                            <i class="fas fa-list-alt fa-5x"></i>
                        </span>
                    </p>
                    <p class="playlist-info">
                        <strong>“{{ playlist.name }}”</strong>
                    </p>
                    <p class="content is-small">{{ playlist.trackCount }} tracks</p>
                    <div class="field has-addons">
                        <p class="control">
                            <button type="button" class="button is-small is-link" @click.prevent="playPlaylistTracks(playlist.id);">
                                <span class="icon is-small"><i class="fas fa-play"></i></span>
                                <span>{{ $t("browsePlaylists.buttons.play") }}</span>
                            </button>
                        </p>
                        <p class="control">
                            <button type="button" class="button is-small is-danger" :disabled="! playlist.id" @click.prevent="onShowDeleteModal(playlist.id);">
                                <span class="icon is-small"><i class="fas fa-times"></i></span>
                                <span>{{ $t("browsePlaylists.buttons.remove") }}</span>
                            </button>
                        </p>
                    </div>
                </div>
                <div class="is-clearfix"></div>
                <delete-confirmation-modal :id="deleteItemId" v-if="showDeleteConfirmationModal" @confirm-delete="onConfirmDelete" @cancel-delete="onCancelDelete"></delete-confirmation-modal>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-browse-playlists',
    template: template(),
    mixins: [
        mixinPagination, mixinLiveSearches, mixinPlayer
    ],
    data: function () {
        return ({
            loading: false,
            nameFilter: null,
            playlists: [],
            resetPager: false,
            showDeleteConfirmationModal: false,
            deleteItemId: null
        });
    },
    watch: {
        nameFilter: function (newValue) {
            if (this.pager.actualPage > 1) {
                this.resetPager = true;
            }
        }
    },
    components: {
        'spieldose-input-typeahead': inputTypeAHead,
        'spieldose-pagination': pagination,
        'delete-confirmation-modal': deleteConfirmationModal
    },
    methods: {
        onPaginationChanged: function (currentPage) {
            this.$router.push({ name: 'playlistsPaged', params: { page: currentPage } });
        },
        onTypeahead: function (text) {
            this.nameFilter = text;
            this.search();
        },
        onShowDeleteModal: function (id) {
            if (id) {
                this.deleteItemId = id;
                this.showDeleteConfirmationModal = true;
            }
        },
        onConfirmDelete: function (id) {
            if (id) {
                this.loading = true;
                spieldoseAPI.playlist.remove(id, (response) => {
                    if (response.status == 200) {
                        if (this.$player.currentPlayList.id == id) {
                            this.$player.currentPlayList.id = null;
                            this.$player.currentPlayList.name = null;
                        }
                        this.search();
                    } else {
                        // TODO: show error
                        console.error(response);
                    }
                    this.showDeleteConfirmationModal = false;
                    this.deleteItemId = null;
                });
            }
        },
        onCancelDelete: function () {
            this.showDeleteConfirmationModal = false;
            this.deleteItemId = null;
        },
        search: function () {
            this.loading = true;
            if (this.resetPager) {
                this.pager.actualPage = 1;
                this.resetPager = false;
            }
            spieldoseAPI.playlist.search(this.nameFilter, this.pager.actualPage, this.pager.resultsPage, (response) => {
                if (response.status == 200) {
                    this.pager.actualPage = response.data.pagination.actualPage;
                    this.pager.totalPages = response.data.pagination.totalPages;
                    this.pager.totalResults = response.data.pagination.totalResults;
                    if (response.data.playlists && response.data.playlists.length > 0) {
                        this.playlists = response.data.playlists;
                    } else {
                        this.playlists = [];
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