import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPagination, mixinLiveSearches, mixinPlayer } from '../mixins.js';
import { default as inputTypeAHead } from './input-typeahead.js';
import { default as pagination } from './pagination';
import { default as apiError } from './api-error.js';
import { default as deleteConfirmationModal } from './delete-confirmation-modal.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
        <p class="title is-1 has-text-centered">{{ $t("browsePlaylists.labels.sectionName") }}</p>
            <div v-if="! hasAPIErrors">
                <div class="field has-addons">
                    <div class="control is-expanded has-icons-left" v-bind:class="{ 'is-loading': loading }">
                        <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="$t('browsePlaylists.inputs.playlistNamePlaceholder')" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" v-bind:placeholder="$t('browsePlaylists.inputs.playlistNamePlaceholder')" v-else v-bind:disabled="loading" v-model.trim="nameFilter" v-on:keyup.enter="search();">
                        <span class="icon is-small is-left">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <p class="control" v-if="! liveSearch">
                        <a class="button is-info" v-on:click.prevent="search();">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browsePlaylists.buttons.search") }}</span>
                        </a>
                    </p>
                </div>
                <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
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
                            <a class="button is-small is-link" v-on:click.prevent="playPlaylistTracks(playlist.id);">
                                <span class="icon is-small"><i class="fas fa-play"></i></span>
                                <span>{{ $t("browsePlaylists.buttons.play") }}</span>
                            </a>
                        </p>
                        <p class="control">
                            <a class="button is-small is-danger" v-bind:disabled="! playlist.id" v-on:click.prevent="onShowDeleteModal(playlist.id);">
                                <span class="icon is-small"><i class="fas fa-times"></i></span>
                                <span>{{ $t("browsePlaylists.buttons.remove") }}</span>
                            </a>
                        </p>
                    </div>
                </div>
                <div class="is-clearfix"></div>
                <delete-confirmation-modal v-bind:id="deleteItemId" v-if="showDeleteConfirmationModal" v-on:confirm-delete="onConfirmDelete" v-on:cancel-delete="onCancelDelete"></delete-confirmation-modal>
            </div>
            <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
        </div>
    `;
};

export default {
    name: 'spieldose-browse-playlists',
    template: template(),
    mixins: [
        mixinAPIError, mixinPagination, mixinLiveSearches, mixinPlayer
    ],
    data: function () {
        return ({
            loading: false,
            nameFilter: null,
            playlists: [],
            showDeleteConfirmationModal: false,
            deleteItemId: null
        });
    },
    components: {
        'spieldose-input-typeahead': inputTypeAHead,
        'spieldose-pagination': pagination,
        'spieldose-api-error-component': apiError,
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
                this.clearAPIErrors();
                spieldoseAPI.playlist.remove(id, (response) => {
                    if (response.ok) {
                        if (this.playerData.currentPlaylist.id == id) {
                            this.playerData.currentPlaylist.unset();
                        }
                        this.search();
                    } else {
                        this.setAPIError(response.getApiErrorData());
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
            this.clearAPIErrors();
            spieldoseAPI.playlist.search(this.nameFilter, this.pager.actualPage, this.pager.resultsPage, (response) => {
                if (response.ok) {
                    this.pager.actualPage = response.body.pagination.actualPage;
                    this.pager.totalPages = response.body.pagination.totalPages;
                    this.pager.totalResults = response.body.pagination.totalResults;
                    if (response.body.playlists && response.body.playlists.length > 0) {
                        this.playlists = response.body.playlists;
                    } else {
                        this.playlists = [];
                    }
                    this.loading = false;
                } else {
                    this.errors = true;
                    this.apiError = response.getApiErrorData();
                    this.loading = false;
                }
            });
        }
    }
}