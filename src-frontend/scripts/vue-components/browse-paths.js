import { default as spieldoseAPI } from '../api.js';
import { mixinAPIError, mixinPagination, mixinLiveSearches } from '../mixins.js';
import { default as inputTypeAHead } from './input-typeahead.js';
import { default as pagination } from './pagination';
import { default as apiError } from './api-error.js';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
        <p class="title is-1 has-text-centered">{{ $t("browsePaths.labels.sectionName") }}</p>
            <div v-if="! hasAPIErrors">
                <div class="field has-addons">
                    <div class="control is-expanded has-icons-left" v-bind:class="{ 'is-loading': loading }">
                    <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="$t('browsePaths.inputs.pathNamePlaceholder')" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" v-bind:placeholder="$t('browsePaths.inputs.pathNamePlaceholder')" v-else v-bind:disabled="loading" v-model.trim="nameFilter" v-on:keyup.enter="search();">
                        <span class="icon is-small is-left">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <p class="control" v-if="! liveSearch">
                        <a class="button is-info" v-on:click.prevent="search();">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browsePaths.buttons.search") }}</span>
                        </a>
                    </p>
                </div>
                <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
                <table class="table is-bordered is-striped is-narrow is-fullwidth is-unselectable" v-show="! loading">
                    <thead>
                        <tr>
                            <th>{{ $t("browsePaths.labels.pathNameTableHeader") }}</th>
                            <th>{{ $t("browsePaths.labels.trackCountTableHeader") }}</th>
                            <th>{{ $t("browsePaths.labels.actionsTableHeader") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in paths" v-bind:key="item.path">
                            <td>{{ item.path }}</td>
                            <td class="has-text-right">{{ item.totalTracks }}</td>
                            <td class="has-text-centered">
                                <div v-if="item.totalTracks > 0">
                                    <i class="cursor-pointer fa-fw fa fa-play" v-bind:title="$t('browsePaths.labels.playThisPath')" v-on:click.prevent="playPathTracks(item.path);"></i>
                                    <i class="cursor-pointer fa-fw fa fa-plus-square" v-bind:title="$t('browsePaths.labels.enqueueThisPath')" v-on:click.prevent="enqueuePathTracks(item.path);"></i>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
        </div>
    `;
};

export default {
    name: 'spieldose-browse-paths',
    template: template(),
    mixins: [
        mixinAPIError, mixinPagination, mixinLiveSearches
    ],
    data: function () {
        return ({
            loading: false,
            nameFilter: null,
            paths: []
        });
    },
    components: {
        'spieldose-input-typeahead': inputTypeAHead,
        'spieldose-pagination': pagination,
        'spieldose-api-error-component': apiError
    },
    methods: {
        onPaginationChanged: function (currentPage) {
            this.$router.push({ name: 'pathsPaged', params: { page: currentPage } });
        },
        onTypeahead: function (text) {
            this.nameFilter = text;
            this.search();
        },
        search: function () {
            this.loading = true;
            this.clearAPIErrors();
            spieldoseAPI.searchPaths(this.nameFilter, this.pager.actualPage, this.pager.resultsPage).then(response => {
                this.pager.actualPage = response.data.pagination.actualPage;
                this.pager.totalPages = response.data.pagination.totalPages;
                this.pager.totalResults = response.data.pagination.totalResults;
                if (response.data.paths && response.data.paths.length > 0) {
                    this.paths = response.data.paths;
                } else {
                    this.paths = [];
                }
                this.loading = false;
            }).catch(error => {
                this.setAPIError(error.getApiErrorData());
                this.loading = false;
            });
        }
    }
}