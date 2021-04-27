import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPagination, mixinLiveSearches, mixinPlayer, mixinAlbums } from '../mixins.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
        <p class="title is-1 has-text-centered">{{ $t("browseRadioStations.labels.sectionName") }}</p>
            <div v-if="! hasAPIErrors">
                <div class="field has-addons">
                    <div class="control is-expanded has-icons-left" v-bind:class="{ 'is-loading': loading }">
                        <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="$t('browseRadioStations.inputs.radioStationSearchNamePlaceholder')" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" v-bind:placeholder="$t('browseRadioStations.inputs.radioStationSearchNamePlaceholder')" v-else v-bind:disabled="loading" v-model.trim="nameFilter" v-on:keyup.enter="search();">
                        <span class="icon is-small is-left">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <p class="control" v-if="! liveSearch">
                        <a class="button is-info" v-on:click.prevent="search();">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browseRadioStations.buttons.search") }}</span>
                        </a>
                    </p>
                    <p class="control">
                        <a class="button is-info" v-bind:disabled="showForm" v-on:click.prevent="showAddRadioStationForm();">
                            <span class="icon">
                                <i class="fas fa-plus" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browseRadioStations.buttons.add") }}</span>
                        </a>
                    </p>
                </div>
                <div v-if="showForm">
                    <div class="field is-horizontal">
                        <div class="field-label">
                            <label class="label">{{ $t("browseRadioStations.labels.radioStationName") }}</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <input class="input" type="text" v-bind:placeholder="$t('browseRadioStations.inputs.radioStationNamePlaceholder')" v-model.trim="formRadioStationName">
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <div class="select">
                                        <select v-model.number="formRadioStationType">
                                            <option value="0">{{ $t("browseRadioStations.selects.optionDirectStream") }}</option>
                                            <option value="1">{{ $t("browseRadioStations.selects.optionM3U") }}</option>
                                            <option value="2">{{ $t("browseRadioStations.selects.optionPLS") }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field is-horizontal">
                        <div class="field-label">
                            <label class="label">{{ $t("browseRadioStations.labels.radioStationUrl") }}</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <input class="input" type="text" v-bind:placeholder="$t('browseRadioStations.inputs.radioStationPlaceholderUrl')" v-model.trim="formRadioStationUrl">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field is-horizontal">
                        <div class="field-label">
                            <label class="label">{{ $t("browseRadioStations.labels.radioStationImage") }}</label>
                        </div>
                        <div class="field-body">
                            <div class="field">
                                <div class="control">
                                    <input class="input" type="text" v-bind:placeholder="$t('browseRadioStations.inputs.radioStationPlaceholderImage')" v-model.trim="formRadioStationImage">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field is-horizontal">
                        <div class="field-label">
                        </div>
                        <div class="field-body">
                            <div class="field is-grouped">
                                <div class="control">
                                    <a class="button is-link" v-on:click.prevent="save();" v-bind:disabled="! allowSave">
                                        <span class="icon">
                                            <i class="fas fa-save" aria-hidden="true"></i>
                                        </span>
                                        <span>{{ $t("browseRadioStations.buttons.save") }}</span>
                                    </a>
                                </div>
                                <div class="control">
                                    <a class="button is-info" v-on:click.prevent="hideAddRadioStationForm();">
                                        <span class="icon">
                                            <i class="fas fa-ban" aria-hidden="true"></i>
                                        </span>
                                        <span>{{ $t("browseRadioStations.buttons.cancel") }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
                    <div class="radio-station-item browse-radio-station-item box has-text-centered" v-for="radioStation in radioStations" v-show="! loading">
                        <img class="radio-station-thumbnail" v-bind:src="radioStation.image | getAlbumImageUrl" v-on:error="radioStation.image = null;">
                        <p class="radio-station-info">
                            <strong>“{{ radioStation.name }}”</strong>
                        </p>
                        <div class="field has-addons">
                            <p class="control">
                                <a class="button is-small is-link" v-on:click.prevent="playRadioStation(radioStation.id);">
                                    <span class="icon is-small"><i class="fas fa-play"></i></span>
                                    <span>{{ $t("browseRadioStations.buttons.play") }}</span>
                                </a>
                            </p>
                            <p class="control">
                                <a class="button is-small is-info" v-on:click.prevent="showUpdateRadioStationForm(radioStation.id);">
                                    <span class="icon is-small"><i class="fas fa-edit"></i></span>
                                    <span>{{ $t("browseRadioStations.buttons.update") }}</span>
                                </a>
                            </p>
                            <p class="control">
                                <a class="button is-small is-danger" v-on:click.prevent="onShowDeleteModal(radioStation.id);">
                                    <span class="icon is-small"><i class="fas fa-times"></i></span>
                                    <span>{{ $t("browseRadioStations.buttons.remove") }}</span>
                                </a>
                            </p>
                        </div>
                    </div>
                    <div class="is-clearfix"></div>
                </div>
                <delete-confirmation-modal v-bind:id="deleteItemId" v-if="showDeleteConfirmationModal" v-on:confirm-delete="onConfirmDelete" v-on:cancel-delete="onCancelDelete"></delete-confirmation-modal>
            </div>
            <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
        </div>
    `;
};

export default {
    name: 'spieldose-browse-radio-stations',
    template: template(),
    mixins: [
        mixinAPIError, mixinPagination, mixinLiveSearches, mixinPlayer, mixinAlbums
    ],
    data: function () {
        return ({
            loading: false,
            nameFilter: null,
            radioStations: [],
            showDeleteConfirmationModal: false,
            deleteItemId: null,
            showForm: false,
            formRadioStationId: null,
            formRadioStationName: null,
            formRadioStationUrl: null,
            formRadioStationType: 0,
            formRadioStationImage: null
        });
    },
    computed: {
        allowSave: function () {
            return (this.formRadioStationName && this.formRadioStationUrl);
        }
    },
    methods: {
        onPaginationChanged: function (currentPage) {
            this.$router.push({ name: 'radioStationsPaged', params: { page: currentPage } });
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
                let self = this;
                self.loading = true;
                self.clearAPIErrors();
                spieldoseAPI.radioStation.remove(id, function (response) {
                    if (response.ok) {
                        self.search();
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.showDeleteConfirmationModal = false;
                    self.deleteItemId = null;
                });
            }
        },
        onCancelDelete: function () {
            this.showDeleteConfirmationModal = false;
            this.deleteItemId = null;
        },
        showAddRadioStationForm: function () {
            this.showForm = true;
            this.formRadioStationId = null;
            this.formRadioStationName = null;
            this.formRadioStationUrl = null;
            this.formRadioStationType = 0;
            this.formRadioStationImage = null;
        },
        hideAddRadioStationForm: function () {
            this.showForm = false;
        },
        showUpdateRadioStationForm: function (id) {
            let self = this;
            spieldoseAPI.radioStation.get(id, (response) => {
                if (response.ok) {
                    self.formRadioStationId = response.body.radioStation.id;
                    self.formRadioStationName = response.body.radioStation.name;
                    self.formRadioStationUrl = response.body.radioStation.url;
                    self.formRadioStationType = response.body.radioStation.urlType;
                    self.formRadioStationImage = response.body.radioStation.image;
                    self.showForm = true;
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
            });
        },
        save: function () {
            if (this.formRadioStationId) {
                this.update();
            } else {
                this.add();
            }
        },
        add: function () {
            let self = this;
            self.loading = true;
            self.clearAPIErrors();
            spieldoseAPI.radioStation.add(self.formRadioStationName, self.formRadioStationUrl, self.formRadioStationType, self.formRadioStationImage, function (response) {
                if (response.ok) {
                    self.showForm = false;
                    self.search();
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
            });
        },
        update: function () {
            let self = this;
            self.loading = true;
            self.clearAPIErrors();
            spieldoseAPI.radioStation.update(self.formRadioStationId, self.formRadioStationName, self.formRadioStationUrl, self.formRadioStationType, self.formRadioStationImage, function (response) {
                if (response.ok) {
                    self.showForm = false;
                    self.search();
                } else {
                    self.setAPIError(response.getApiErrorData());
                }
            });
        },
        search: function () {
            let self = this;
            self.loading = true;
            self.clearAPIErrors();
            spieldoseAPI.radioStation.search(self.nameFilter, self.pager.actualPage, self.pager.resultsPage, function (response) {
                if (response.ok) {
                    self.pager.actualPage = response.body.pagination.actualPage;
                    self.pager.totalPages = response.body.pagination.totalPages;
                    self.pager.totalResults = response.body.pagination.totalResults;
                    if (response.body.radioStations && response.body.radioStations.length > 0) {
                        self.radioStations = response.body.radioStations;
                    } else {
                        self.radioStations = [];
                    }
                    self.loading = false;
                } else {
                    self.errors = true;
                    self.apiError = response.getApiErrorData();
                    self.loading = false;
                }
            });
        }
    }
}