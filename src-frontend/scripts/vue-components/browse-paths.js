import { default as spieldoseAPI } from '../api.js';
import { mixinPagination, mixinLiveSearches, mixinPlayer } from '../mixins.js';
import { default as inputTypeAHead } from './input-typeahead.js';
import { default as pagination } from './pagination';

const template = function () {
    return `
        <div class="container is-fluid box is-marginless">
            <p class="title is-1 has-text-centered">{{ $t("browsePaths.labels.sectionName") }}</p>
            <div>
                <div class="field has-addons">
                    <div class="control is-expanded has-icons-left" :class="{ 'is-loading': loading }">
                    <spieldose-input-typeahead v-if="liveSearch" :loading="loading" :placeholder="$t('browsePaths.inputs.pathNamePlaceholder')" @on-value-change="onTypeahead"></spieldose-input-typeahead>
                        <input type="text" class="input" :placeholder="$t('browsePaths.inputs.pathNamePlaceholder')" v-else :disabled="loading" v-model.trim="nameFilter" @keyup.enter="search();">
                        <span class="icon is-small is-left">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <p class="control" v-if="! liveSearch">
                        <button type="button" class="button is-dark" :disabled="loading" @click.prevent="search();">
                            <span class="icon">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t("browsePaths.buttons.search") }}</span>
                        </button>
                    </p>
                </div>
                <spieldose-pagination :loading="loading" :data="pager" @pagination-changed="onPaginationChanged"></spieldose-pagination>
                <table class="table is-bordered is-striped is-narrow is-fullwidth is-unselectable" v-show="! loading">
                    <thead>
                        <tr>
                            <th>{{ $t("browsePaths.labels.pathNameTableHeader") }}</th>
                            <th>{{ $t("browsePaths.labels.trackCountTableHeader") }}</th>
                            <th>{{ $t("browsePaths.labels.actionsTableHeader") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in paths" :key="item.path">
                            <td>{{ item.path }}</td>
                            <td class="has-text-right">{{ item.totalTracks }}</td>
                            <td class="has-text-centered">
                                <div v-if="item.totalTracks > 0">
                                    <span class="icon"><i class="cursor-pointer fa fa-play" :title="$t('browsePaths.labels.playThisPath')" @click.prevent="playPathTracks(item.path);"></i></span>
                                    <span class="icon"><i class="cursor-pointer fa fa-plus-square" :title="$t('browsePaths.labels.enqueueThisPath')" @click.prevent="enqueuePathTracks(item.path);"></i></span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
};

export default {
    name: 'spieldose-browse-paths',
    template: template(),
    mixins: [
        mixinPagination, mixinLiveSearches, mixinPlayer
    ],
    data: function () {
        return ({
            loading: false,
            paths: [],
            nameFilter: null,
            resetPager: false
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
        'spieldose-pagination': pagination
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
            if (this.resetPager) {
                this.pager.actualPage = 1;
                this.resetPager = false;
            }
            spieldoseAPI.searchPaths(this.nameFilter, this.pager.actualPage, this.pager.resultsPage, (response) => {
                if (response.status == 200) {
                    this.pager.actualPage = response.data.pagination.actualPage;
                    this.pager.totalPages = response.data.pagination.totalPages;
                    this.pager.totalResults = response.data.pagination.totalResults;
                    if (response.data.paths && response.data.paths.length > 0) {
                        this.paths = response.data.paths;
                    } else {
                        this.paths = [];
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