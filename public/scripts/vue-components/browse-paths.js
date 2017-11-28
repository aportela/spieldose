var browsePaths = (function () {
    "use strict";

    var template = function () {
        return `
    <div class="container is-fluid box">
        <p class="title is-1 has-text-centered">Browse paths</i></p>
        <div v-if="! errors">
            <div class="field">
                <div class="control has-icons-left" v-bind:class="loading ? 'is-loading': ''">
                    <input class="input" :disabled="loading" v-focus v-model.trim="nameFilter" type="text" placeholder="search path name..." v-on:keyup.esc="abortInstantSearch();" v-on:keyup="instantSearch();">
                    <span class="icon is-small is-left">
                        <i class="fa fa-search"></i>
                    </span>
                </div>
            </div>
            <table id="playlist-now-playing" class="table is-bordered is-striped is-narrow is-fullwidth" v-show="! loading">
                <thead>
                        <tr class="is-unselectable">
                            <th>Path</th>
                            <th>Tracks</th>
                            <th>Actions</th>
                        </tr>
                </thead>
                <tbody>
                    <tr v-for="item in paths">
                        <td>{{ item.path }}</td>
                        <td>{{ item.totalTracks }}</td>
                        <td>
                            <i v-on:click="play(item.path);" class="cursor-pointer fa fa-play" title="play this path"></i>
                            <i v-on:click="enqueue(item.path);" class="cursor-pointer fa fa-plus-square" title="enqueue this path"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <spieldose-api-error-component v-else v-bind:apiError="apiError"></spieldose-api-error-component>
    </div>
    `;
    };

    var module = Vue.component('spieldose-browse-paths', {
        template: template(),
        data: function () {
            return ({
                loading: false,
                errors: false,
                apiError: null,
                nameFilter: null,
                timeout: null,
                paths: [],
                pager: getPager(),
                playerData: sharedPlayerData
            });
        }, directives: {
            focus: {
                update: function (el) {
                    el.focus();
                }
            }
        }, created: function () {
            this.search();
        }, directives: {
            focus: {
                inserted: function(el) {
                    el.focus();
                }
            }
        }, methods: {
            abortInstantSearch: function () {
                this.nameFilter = null;
            },
            instantSearch: function () {
                var self = this;
                if (self.timeout) {
                    clearTimeout(self.timeout);
                }
                self.timeout = setTimeout(function () {
                    self.pager.actualPage = 1;
                    self.search();
                }, 256);
            },
            search: function () {
                var self = this;
                self.loading = true;
                self.errors = false;
                spieldoseAPI.searchPaths(self.nameFilter, function (response) {
                    if (response.ok) {
                        self.loading = false;
                        self.paths = response.body.paths;
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                        self.loading = false;
                    }
                });
            },
            play: function (path) {
                var self = this;
                spieldoseAPI.getPathTracks(path, function (response) {
                    if (response.ok) {
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.playerData.replace(response.body.tracks);
                        }
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                    }
                });
            },
            enqueue: function (path) {
                var self = this;
                spieldoseAPI.getPathTracks(path, function (response) {
                    if (response.ok) {
                        if (response.body.tracks && response.body.tracks.length > 0) {
                            self.playerData.enqueue(response.body.tracks);
                        }
                    } else {
                        self.errors = true;
                        self.apiError = response.getApiErrorData();
                    }
                });
            }
        }
    });

    return (module);
})();