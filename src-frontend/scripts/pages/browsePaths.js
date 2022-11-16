import { default as pagination } from '../vue-components/pagination.js';

const template = function () {
    return `
        <div class="field has-addons">
            <div class="control has-icons-left is-expanded is-small" :class="{ 'is-loading': loading }">
                <input class="input is-small" type="text" placeholder="Search" v-model.trim="searchQuery"
                    v-on:keyup.enter="onSearch" :disabled="loading">
                <span class="icon is-small is-left">
                    <i class="fas fa-search"></i>
                </span>
                <span class="icon is-small is-right">
                    <i class="fas fa-loading"></i>
                </span>
            </div>
            <div class="control">
                <div class="select is-small">
                    <select :disabled="loading">
                        <option value="">sort by name</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <div class="select is-small">
                    <select v-model="sort.order" :disabled="loading">
                        <option value="ASC">ASCending order</option>
                        <option value="DESC">DESCending order</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <div class="select is-small">
                    <select v-model.number="pager.resultsPage" :disabled="loading">
                        <option value="32">32 results/page</option>
                        <option value="64">64 results/page</option>
                        <option value="128">128 results/page</option>
                        <option value="0">all results</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <button class="button is-small is-pink" @click.prevent="onSearch" :disabled="loading">Search</button>
            </div>
        </div>
        <spieldose-pagination :disabled="loading" :data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth is-size-7">
            <thead>
                <tr>
                    <th class="has-text-centered">Operations</th>
                    <th>Path</th>
                    <th class="has-text-right">Total files</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="directory in directories">
                    <td class="has-text-centered">
                        <div class="field has-addons is-center">
                            <p class="control is-small">
                                <button class="button is-small" @click.prevent="onPlayEnqueuePath(directory.id, false)">
                                    <span class="icon is-small">
                                        <i class="cursor-pointer fa-fw fa fa-play"></i>
                                    </span>
                                    <span>Play</span>
                                </button>
                            </p>
                            <p class="control is-small">
                            <button class="button is-small" @click.prevent="onPlayEnqueuePath(directory.id, true)">
                                    <span class="icon is-small">
                                        <i class="cursor-pointer fa-fw fa fa-plus-square mr-1"></i>
                                    </span>
                                    <span>Enqueue</span>
                                </button>
                            </p>
                        </div>
                    </td>
                    <td>{{ directory.path }}</td>
                    <td class="has-text-right">{{ directory.totalFiles }}</td>
                </tr>
            </tbody>
        </tbody>

    `;
};

export default {
    name: 'spieldose-page-browse-artists',
    template: template(),
    data: function () {
        return ({
            loading: false,
            searchQuery: null,
            pager: {
                currentPage: 1,
                resultsPage: 32,
                totalPages: 0,
                totalResults: 0
            },
            sort: {
                field: 'name',
                order: 'ASC'
            },
            directories: []
        });
    },
    created: function () {
        this.onSearch();
    },
    components: {
        'spieldose-pagination': pagination
    },
    methods: {
        onSearch: function () {
            this.loading = true;
            this.tracks = [];
            this.currentTrackIndex = -1;
            this.$api.path.search(this.searchQuery, this.pager.currentPage, this.pager.resultsPage, this.sort.field, this.sort.order).then(success => {
                this.loading = false;
                this.directories = success.data.results.items;
                this.pager = success.data.results.pager;
            }).catch(error => {
                this.loading = false;
            });
        },
        onPaginationChanged: function (event) {
            this.onSearch();
        },
        onPlayEnqueuePath: function (path, enqueue) {
            this.$api.track.search(this.searchQuery, null, null, null, path).then(success => {
                if (!enqueue) {
                    this.$player.replaceCurrentPlaylist(success.data.tracks);
                } else {
                    this.$player.enqueueToCurrentPlaylist(success.data.tracks);
                }
                this.loading = false;
            }).catch(error => {
                console.log(error);
                switch (error.response.status) {
                    default:
                        //this.setAPIError(error.getApiErrorData());
                        break;
                }
                this.loading = false;
            });
        }
    }
}