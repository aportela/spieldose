let browsePaths = (function () {
    "use strict";

    const template = function () {
        return `
            <div class="container is-fluid box is-marginless">
                <p class="title is-1 has-text-centered">Browse paths</i></p>
                <div v-if="! hasAPIErrors">
                    <div class="field has-addons">
                        <div class="control is-expanded has-icons-left" v-bind:class="{ 'is-loading': loading }">
                            <spieldose-input-typeahead v-if="liveSearch" v-bind:loading="loading" v-bind:placeholder="'search path name...'" v-on:on-value-change="onTypeahead"></spieldose-input-typeahead>
                            <input class="input" type="text" placeholder="search path name..." v-bind:disabled="loading" v-else v-model.trim="nameFilter" v-on:keyup.enter="search();">
                            <span class="icon is-small is-left">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <p class="control" v-if="! liveSearch">
                            <a class="button is-info" v-on:click.prevent="search();">
                                <span class="icon">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                </span>
                                <span>search</span>
                            </a>
                        </p>
                    </div>
                    <spieldose-pagination v-bind:loading="loading" v-bind:data="pager" v-on:pagination-changed="onPaginationChanged"></spieldose-pagination>
                    <table class="table is-bordered is-striped is-narrow is-fullwidth is-unselectable" v-show="! loading">
                        <thead>
                            <tr>
                                <th>Path</th>
                                <th>Tracks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in paths" v-bind:key="item.path">
                                <td>{{ item.path }}</td>
                                <td class="has-text-right">{{ item.totalTracks }}</td>
                                <td class="has-text-centered">
                                    <div v-if="item.totalTracks > 0">
                                        <i class="cursor-pointer fa fa-play" title="play this path" v-on:click.prevent="play(item.path, item.totalTracks);"></i>
                                        <i class="cursor-pointer fa fa-plus-square" title="enqueue this path" v-on:click.prevent="enqueue(item.path, item.totalTracks);"></i>
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

    /* browse path section component */
    let module = Vue.component('spieldose-browse-paths', {
        template: template(),
        mixins: [
            mixinAPIError, mixinFocus, mixinPagination, mixinLiveSearches, mixinPlayer
        ],
        data: function () {
            return ({
                loading: false,
                nameFilter: null,
                timeout: null,
                paths: []
            });
        }, watch: {
            '$route'(to, from) {
                if (to.name == "paths" || to.name == "pathsPaged") {
                    this.pager.actualPage = parseInt(to.params.page);
                    this.search();
                }
            }
        }, methods: {
            onPaginationChanged: function (currentPage) {
                this.$router.push({ name: 'pathsPaged', params: { page: currentPage } });
            },
            onTypeahead: function (text) {
                this.nameFilter = text;
                this.search();
            },
            search: function () {
                var self = this;
                self.loading = true;
                self.clearAPIErrors();
                spieldoseAPI.searchPaths(self.nameFilter, self.pager.actualPage, self.pager.resultsPage, function (response) {
                    if (response.ok) {
                        self.pager.actualPage = response.body.pagination.actualPage;
                        self.pager.totalPages = response.body.pagination.totalPages;
                        self.pager.totalResults = response.body.pagination.totalResults;
                        if (response.body.paths && response.body.paths.length > 0) {
                            self.paths = response.body.paths;
                        } else {
                            self.paths = [];
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            play: function (path, trackCount) {
                var self = this;
                self.clearAPIErrors();
                spieldoseAPI.getPathTracks(path, parseInt(trackCount), function (response) {
                    if (response.ok) {
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.playerData.replace(response.body.tracks);
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            },
            enqueue: function (path, trackCount) {
                var self = this;
                self.clearAPIErrors();
                spieldoseAPI.getPathTracks(path, parseInt(trackCount), function (response) {
                    if (response.ok) {
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.playerData.enqueue(response.body.tracks);
                        }
                    } else {
                        self.setAPIError(response.getApiErrorData());
                    }
                    self.loading = false;
                });
            }
        }
    });

    return (module);
})();